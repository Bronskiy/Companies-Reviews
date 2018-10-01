<?php

require_once("Image/Image.php");

/**
 * Review service
 */
class Companies_Model_ReviewService extends Main_Service_Models {
    /**
     * Leave review
     * @param Companies_Model_Company $company
     * @return boolean 
     */
    public function leaveReview(Companies_Model_Company $company) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = new Companies_Form_LeaveReview($company->id);

        if ($form->isValid($post)) {
            $curConnection = Doctrine_Manager::connection();

            try {
                $curConnection->beginTransaction();

                $starFilter = new Main_Service_Filter_StarToFloat();
                
                $review = new Companies_Model_Review();
                $review->fromArray(array(
                    'company_id' => $company->id,
                    'client_name' => $form->getValue('name'),
                    'client_from' => $form->getValue('from'),
                    'mail' => $form->getValue('mail'),
                    'review' => $form->getValue('review'),
                    'rating' => $starFilter->filter($form->getValue('rating')),
                    'confirm_hash' => self::generateUniqueHash(),
                    'status' => Companies_Model_Review::STATUS_UNCONFIRMED
                ));

                if ($form->getValue("employee_id")) {
                    $review->company_employee_id = $form->getValue("employee_id");
                }

                $review->save();

                $this->createReviewFolders($review);

                try {
                    if ($form->avatar->isUploaded()) {
                        $this->_uploadReviewAvatar($review, $form->avatar);
                    }

                    if (!$form->video->isUploaded()) {
                        $this->_sendReviewerUploadVideoLink($review);
                    } else {
                        $this->_uploadVideoFile($review, $form->video);
                    }

                    $this->_sendReviewerConfirmLink($review);
                    $curConnection->commit();

                    Main_Service_Models::addProcessingInfo(
                        'Review added',
                         Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                    );

                    return $review;
                } catch (Exception $e) {
                    $this->deleteReviewFolder($review, 'video');
                    $this->deleteReviewFolder($review, $review->id);
                    throw $e;
                }
            } catch (Exception $e) {
                $curConnection->rollback();
                self::getLogger()->log($e);
                self::addProcessingInfo('Error adding review, please contact the administrator or try again.');
            }            
        } else {
            $form->populate($post);
            self::addProcessingInfo('Please fix the errors below.');
        }

        return false;
    }
    
    /**
     * Review Changing
     * @param Companies_Model_Review $review 
     */
    public function changeReview(Companies_Model_Review $review) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('change-review');

        if ($form->isValid($post)) {
            $curConnection = Doctrine_Manager::connection(); 
            
            try {
                $curConnection->beginTransaction();

                $starFilter = new Main_Service_Filter_StarToFloat();
                
                $review->status = Companies_Model_Review::STATUS_NOT_PROCESSED;
                $review->reconcile_hash = null;
                $review->review = $form->getValue('review');
                $review->rating = $starFilter->filter($form->getValue('rating'));
                $review->save();

                // delete old videos, if any
                $dirGenerator = new Main_Service_Dir_Generator_Review($review);
                $dirsInfo = $dirGenerator->getFoldersPathsFromRule();

                foreach ($review->Videos as $video) {
                    $thumbnail = $this->getView()->getPath($dirsInfo, 'video', $video->name . ".jpg");

                    if (file_exists($thumbnail)) {
                        @unlink($thumbnail);
                    }

                    foreach ($video->Streams as $stream) {
                        $ext = self::getExtensionByMimeType($stream->type);

                        $filePath = $this->getView()->getPath($dirsInfo, 'video', $video->name . "." . $ext);

                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }

                        $stream->delete();
                    }

                    $video->delete();
                }
                
                if ($form->video->isUploaded()) {
                    $this->_uploadVideoFile($review, $form->video);
                }

                $curConnection->commit();

                Main_Service_Models::addProcessingInfo(
                    'Review changed',
                    Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                );
                
                return true;
            } catch (Exception $e) {
                $curConnection->rollback();
                self::getLogger()->log($e);
                self::addProcessingInfo('Error changing review, please contact the administrator or try again.');
            }
        } else {
            $form->populate($post);
            self::addProcessingInfo('Please fix the errors below.');
        }

        return false;
    }

    /**
     * Add video to the existing review
     * @param Companies_Model_Review $review
     * @return boolean
     */
    public function uploadVideo(Companies_Model_Review $review) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('add-video');

        if ($form->isValid($post)) {
            $curConnection = Doctrine_Manager::connection();

            try {
                $curConnection->beginTransaction();

                if ($form->video->isUploaded()) {
                    $this->_uploadVideoFile($review, $form->video);
                }

                $review->status = Companies_Model_Review::STATUS_NOT_PROCESSED;
                $review->video_attach_hash = null;
                $review->video_attach_date = null;
                $review->save();

                $curConnection->commit();

                Main_Service_Models::addProcessingInfo(
                    'Video added.',
                    Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                );

                return true;
            } catch (Exception $e) {
                $curConnection->rollback();
                self::getLogger()->log($e);
                self::addProcessingInfo('Error adding video review, please contact the administrator or try again.');
            }
        } else {
            $form->populate($post);
            self::addProcessingInfo('Please fix the errors below.');
        }

        return false;
    }
    
    /**
     * Upload avatar
     * @param Companies_Model_Review $review
     * @param Zend_Form_Element $avatar
     */
    private function _uploadReviewAvatar(Companies_Model_Review $review, Zend_Form_Element $avatar)
    {
        // get dirs info for cur review
        $dirGenerator = new Main_Service_Dir_Generator_Review($review);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
        $uploadedFileNewPath = $this->getView()->getPath($dirsInfo, $review->id, "avatar.jpg");

        $avatar->addFilter('Rename', $uploadedFileNewPath);
        $avatar->receive();

        $review->client_avatar = "avatar.jpg";
        $review->save();

        $this->_resizeAvatar($review);
    }
    
    /**
     * Upload video
     * @param Companies_Model_Review $review
     * @param Zend_Form_Element $uploadedVideo
     */
    private function _uploadVideoFile(Companies_Model_Review $review, Zend_Form_Element $uploadedVideo) {
        $origName = pathinfo($uploadedVideo->getFileName());
        $newName = hash('sha256', time() . rand() . $origName['basename']);
        $ext = $origName['extension'];

        $mimeType = $uploadedVideo->getMimeType();
        $dirGenerator = new Main_Service_Dir_Generator_Review($review);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
        $uploadedFileNewPath = $this->getView()->getPath($dirsInfo, 'video', $newName . "." . $ext);

        $uploadedVideo->addFilter('Rename', $uploadedFileNewPath);
        $uploadedVideo->receive();

        $companyVideo = new Companies_Model_CompanyVideo();
        $companyVideo->fromArray(array(
            "company_id" => $review->company_id,
            "review_id" => $review->id,
            "name" => $newName,
            "type" => $mimeType,
            "status" => Companies_Model_CompanyVideo::STATUS_NOT_PROCESSED,
            "is_source" => true,
        ));
        $companyVideo->save();

        $stream = new Companies_Model_VideoStream();
        $stream->fromArray(array(
            "video_id" => $companyVideo->id,
            "type" => $mimeType,
            "status" => Companies_Model_VideoStream::STATUS_NOT_PROCESSED,
            "is_source" => true,
        ));
        $stream->save();
    }

    /**
     * Confirm review
     * @param Companies_Model_Review $review
     */
    public function confirm(Companies_Model_Review $review) {
        $review->confirm_hash = null;
        $review->status = Companies_Model_Review::STATUS_NOT_PROCESSED;
        $review->save();
    }

    /**
     * Publish review
     * @param Companies_Model_Review $review
     */
    public function publish(Companies_Model_Review $review) {
        $review->confirm_hash = null;
        $review->status = Companies_Model_Review::STATUS_PUBLISHED;
        $review->save();
    }
    
    /**
     * Confirm review
     * @param Companies_Model_Review $review 
     */
    public function confirmReview(Companies_Model_Review $review) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('confirm-review');

        if ($form->isValid($post)) {
            if ($review->Company->code_num != $form->getValue('code_num')) {
                $form->code_num->addError('Invalid code');
                return false;
            }

            $this->confirm($review);
            Main_Service_Models::addProcessingInfo('Review confirmed', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

            return true;
        } else {
            $form->populate($post);
            self::addProcessingInfo('Please fix the errors below.');
        }

        return false;
    }

    /**
     * Send reviewer a video upload link
     * @param Companies_Model_Review $review
     */
    protected function _sendReviewerUploadVideoLink(Companies_Model_Review $review) {
        if (!$review->mail) {
            return;
        }

        $date = date('Y-m-d', mktime(0, 0, 0, date("m")  , date("d") + 7, date("Y")));

        $review->video_attach_hash = self::generateUniqueHash();
        $review->video_attach_date = $date;
        $review->save();

        $templateVars = array(
            "userName"     => !empty($review->client_name) ? $review->client_name : "client",
            "addVideoLink" =>
                $this->getView()->serverUrl() .
                $this->getView()->urlGenerator()->companyReviewAddVideoUrl($review->Company, $review),
            "company" => $review->Company,
            "domain" => $this->getConfig()->domain,
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('review-video-attach.phtml');

        $mailConfig = array(
            'toMail'   => $review->mail,
            'body'     => $mailBody,
            'fromText' => 'Revudio',
            'subject'  => 'Attach video to review'
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }
    
    /**
     * Send confirmation link for review confirmation
     * 
     * @param Companies_Model_Review $review 
     */
    public function _sendReviewerConfirmLink(Companies_Model_Review $review)
    {
        $templateVars = array(
            'userName' => !empty($review->client_name) ? $review->client_name : 'client',
            'approveLink' =>
                $this->getView()->serverUrl() .
                $this->getView()->urlGenerator()->companyReviewConfirmUrl($review->Company, $review),
            'codeNum' => $review->Company->code_num
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('confirm-review.phtml');

        $mailConfig = array(
            'toMail'   => $review->mail,
            'body'     => $mailBody,
            'fromText' => 'Revudio',
            'subject'  => 'Review confirmation');

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }
 
    /**
     * Delete review folder
     * 
     * WARNING folder should be empty
     * 
     * @param Companies_Model_Review $review
     * @param type $folderName
     * @return boolean 
     */
    public function deleteReviewFolder(Companies_Model_Review $review, $folderName)
    {
        // get dirs info for cur review
        $dirGenerator = new Main_Service_Dir_Generator_Review($review);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
        
        if(is_string($folderName)) {
            $folder = $this->getView()->getPath($dirsInfo, $folderName);
            if(is_dir($folder)) {
                return @rmdir($folder);
            }
        }
        return false;
    }
    
    /**
     * Creating folders for review
     * 
     * @param Companies_Model_Review $company 
     */
    public function createReviewFolders(Companies_Model_Review $review)
    {
        $dirGenerator = new Main_Service_Dir_Generator_Review($review);
        $dirGenerator->generateFoldersByRule();
    }
    
   
    /**
     * Deleting review
     *  
     */
    public function deleteReview($reviewId, $companyId)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || ! $this->isValidCsrfToken($post)) {
            self::addProcessingInfo('Error deleting review, please contact the administrator or try again.');
            return false;
        }

        $review = $this->getTable()->findOneByIdAndCompanyId($reviewId, $companyId);

        if ($review !== false && $this->isReviewRemovable($review)) {
            if ($review->delete()) {
                $this->deleteReviewFolder($review, 'video');
                $this->deleteReviewFolder($review, $review->id);

                Main_Service_Models::addProcessingInfo(
                    'Review deleted',
                    Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                );

                return true;
            }
        }

        self::addProcessingInfo('Error deleting review, please contact the administrator or try again.');

        return false;
    }
    
    /**
     * Business owner request for change review
     * @param Companies_Model_Review $review 
     */
    public function requestChangeReview(Companies_Model_Review $review) {
        try {
            $review->reconcile_hash = self::generateUniqueHash();
            $this->_sendReconciliationRequest($review);
            $review->save();

            self::addProcessingInfo(
                'Reconciliation request sent.',
                self::PROCESSING_INFO_SUCCESS_TYPE
            );
        } catch (Exception $e) {
            self::getLogger()->log($e->getMessage());
            self::addProcessingInfo('Error sending reconciliation request, please contact the administrator or try again.');
        }
    }
    
    /**
     * Send a reconciliation request
     * @param Companies_Model_Review $review 
     */
    protected function _sendReconciliationRequest(Companies_Model_Review $review) {
         $templateVars = array(
            'userName' => !empty($review->client_name) ? $review->client_name : 'client',
            'changeLink' => 
                $this->getView()->serverUrl() .
                $this->getView()->urlGenerator()->companyReviewChangeUrl($review->Company, $review),
            'company' => $review->Company,
            'companyLink' => 
                $this->getView()->serverUrl() .
                $this->getView()->urlGenerator()->companyUrl($review->Company)
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('change-review-mail.phtml');

        $mailConfig = array(
            'toMail' => $review->mail,
            'body' => $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Please Change Your Review'
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }
    
    /**
     * Downloading reviewers file
     * 
     * @param string $fileType 
     */
    public function downloadReviewers(Companies_Model_Company $company, $fileType)
    {
        $paginator = $this->getCompanyReviewersPaginator($company);

        if (!$paginator->count()) {
            self::addProcessingInfo('No reviewers to download.');
            return false;
        }

        try {
            $factory = new Main_Service_File_Factory();
            $aReviewers = $this->_getReviewersDataFromPaginator($paginator);
            unset($paginator);
            // get dirs info for cur review
            $dirGenerator = new Main_Service_Dir_Generator_Company($company);

            $dirsInfo    = $dirGenerator->getFoldersPathsFromRule();
            $reviewsPath = $this->getView()->getPath($dirsInfo, 'reviews');
            $fileName    = 'reviewers';

            $typeWriter  = $factory->getWriter($fileType, $reviewsPath, $fileName, $aReviewers); 

            if (!$typeWriter->write()) {
                self::addProcessingInfo('Error downloading reviewers, please contact the administrator or try again.');
                return false;
            }

            $streamWriter = new Main_Service_File_Writer_Stream($typeWriter->getFullFileName());
            $streamWriter->write();

            exit;
        } catch(Exception $e) {
            self::getLogger()->log($e);
        }

        self::addProcessingInfo('Error downloading reviewers, please contact the administrator or try again.');

        return false;
    }
    
    /**
     *
     * @param type $paginator 
     */
    private function _getReviewersDataFromPaginator(Zend_Paginator $paginator)
    {
        $data = array();
        
        foreach($paginator as $reviewer) {
            $data[] = array($reviewer->client_name, $reviewer->mail, $reviewer->client_from);
        }
        return $data;
    }
    
    /**
     * Check if user can delete review 
     */
    public function isReviewRemovable(Companies_Model_Review $review)
    {
        $rules = array(Companies_Model_Review::STATUS_NOT_PROCESSED,
                       Companies_Model_Review::STATUS_UNCONFIRMED,
                       Companies_Model_Review::STATUS_PUBLISHED,
                       Companies_Model_Review::STATUS_RECONCILIATION);
        
        return in_array($review->status, $rules) ? true : false;
    }

    /**
     * Get company reviews
     * @param Companies_Model_Company $company
     * @param bool $status
     * @param array $range
     * @param null $perPage
     * @return Zend_Paginator
     */
    public function getCompanyReviewsPaginator(Companies_Model_Company $company, $status = false, array $range = null, $perPage = null) {
        $pageNumber = (int)Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        $query = $this->getTable()->getQueryCompanyReviewsAll($company->id, $status, $range);

        if ($perPage != null) {
            $itemsPerPage = $perPage;
        } else {
            $itemsPerPage = @self::getConfig()->pagination->companies->reviews->itemsPerPage;
        }

        $itemsPerPage = (int)$itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();

        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }

    /**
     * Get paginator for employee reviews
     * @param Companies_Model_Employee $employee
     * @param null $perPage
     * @return Zend_Paginator
     */
    public function getEmployeeReviewsPaginator(Companies_Model_Employee $employee, $perPage = null, $status = null) {
        $pageNumber = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        $query = $this->getTable()->getQueryEmployeeReviewsAll($employee->id, $status);

        if ($perPage != null) {
            $itemsPerPage = $perPage;
        } else {
            $itemsPerPage = @self::getConfig()->pagination->companies->reviews->itemsPerPage;
        }

        $itemsPerPage = (int) $itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();

        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }
    
    /**
     * Company reviewers pahinator
     * 
     * @param Companies_Model_Company $company
     * @param array $statuses
     * @return Zend_Paginator 
     */
    public function getCompanyReviewersPaginator(Companies_Model_Company $company, $statuses = null) {
        $pageNumber = (int)Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        $query = $this->getTable()->getQueryCompanyReviewersAll($company->id, $statuses);

        $itemsPerPage = @self::getConfig()->pagination->companies->reviewers->itemsPerPage;
        $itemsPerPage = (int)$itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();

        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }
    
    /**
     * All reviews paginator
     * 
     * @param string $status
     * @return Zend_Paginator 
     */
    public function getReviewsPaginator($status = false)
    {
        $pageNumber = (int)Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        $query = $this->getTable()->getQueryReviewsAll($status);
        $itemsPerPage = @self::getConfig()->pagination->companies->reviews->itemsPerPage;
        $itemsPerPage = (int)$itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();
        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }

    /**
     * Review creation (API)
     * @param Companies_Model_Company $company
     * @return Companies_Model_Review
     */
    public function createReview($company) {
        $post = Zend_Controller_Front::getInstance()->getRequest();
        $form = new Api_Form_CreateReview($company->id);
        $review = null;

        $curConnection = Doctrine_Manager::connection();
        $curConnection->beginTransaction();

        try {
            if (!$form->isValid($post->getPost())) {
                throw new Exception();
            }

            $starFilter = new Main_Service_Filter_StarToFloat();

            $employee = $post->getParam("employee_id", null);

            if (!$employee) {
                $employee = null;
            }

            $review = new Companies_Model_Review();
            $review->fromArray(array(
                "company_id" => $company->id,
                "client_name" => $post->getParam("name", null),
                "client_from" => $post->getParam("from", null),
                "mail" => $post->getParam("email", null),
                "review" => $post->getParam("review", null),
                "rating" => $starFilter->filter($post->getParam("rating", null)),
                "company_employee_id" => $employee,
                "status" => Companies_Model_Review::STATUS_UNCONFIRMED,                
            ));
            $review->save();

            $this->createReviewFolders($review);

            try {
                $curConnection->commit();
            } catch (Exception $e) {
                $this->deleteReviewFolder($review, 'video');
                $this->deleteReviewFolder($review, $review->id);
                throw $e;
            }
        } catch (Exception $e) {
            $curConnection->rollback();
            self::getLogger()->log($e);
            $review = null;
        }

        return $review;
    }

    /**
     * Upload avatar
     * @param $review
     * @param $chunk
     * @param $offset
     * @return boolean
     */
    private function _uploadAvatar($review, $chunk, $offset)
    {
        $status = false;
        $offset = (int) $offset;

        if ($offset == 0) {
            $review->client_avatar = "avatar.jpg";
            $review->save();
        }

        $dirGenerator = new Main_Service_Dir_Generator_Review($review);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
        $filePath = $this->getView()->getPath($dirsInfo, $review->id, "avatar.jpg");

        $size = 0;

        if (file_exists($filePath)) {
            $size = filesize($filePath);
        }

        if ($offset + strlen($chunk) < 10 * 1024 * 1024) {
            if ($size >= $offset) {
                $fp = fopen($filePath, "ab");

                if (flock($fp, LOCK_EX)) {
                    ftruncate($fp, $offset);
                    fwrite($fp, $chunk);
                    flock($fp, LOCK_UN);

                    $status = true;
                }

                fclose($fp);
            }
        }

        return $status;
    }

    /**
     * Upload video
     * @param $review
     * @param $format
     * @param $chunk
     * @param $offset
     * @return boolean
     */
    private function _uploadVideo($review, $format, $chunk, $offset) {
        $status = false;
        $offset = (int) $offset;
        $companyVideo = null;
        $stream = null;

        if ($offset == 0) {
            $fileName = hash('sha256', time() . rand());

            $companyVideo = new Companies_Model_CompanyVideo();
            $companyVideo->fromArray(array(
                "company_id" => $review->company_id,
                "review_id" => $review->id,
                "name" => $fileName,
                "status" => Companies_Model_CompanyVideo::STATUS_NOT_PROCESSED,
            ));
            $companyVideo->save();

            $stream = new Companies_Model_VideoStream();
            $stream->fromArray(array(
                "video_id" => $companyVideo->id,
                "type" => ($format == "3gp" ? "video/3gpp" : "video/quicktime"),
                "status" => Companies_Model_VideoStream::STATUS_NOT_PROCESSED,
                "is_source" => true,
            ));
            $stream->save();
        } else {
            $companyVideo = Companies_Model_CompanyVideoTable::getInstance()->findOneByReviewId($review->id);

            if ($companyVideo === false) {
                return false;
            }

            $fileName = $companyVideo->name;
        }

        // get dirs info for cur review
        $dirGenerator = new Main_Service_Dir_Generator_Review($review);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
        $filePath = $this->getView()->getPath($dirsInfo, 'video', $fileName . "." . $format);

        $size = 0;

        if (file_exists($filePath)) {
            $size = filesize($filePath);
        }

        if ($offset + strlen($chunk) < 1024 * 1024 * 1024) {
            if ($size >= $offset) {
                $fp = fopen($filePath, "ab");

                if (flock($fp, LOCK_EX)) {
                    ftruncate($fp, $offset);
                    fwrite($fp, $chunk);
                    flock($fp, LOCK_UN);

                    $status = true;
                }

                fclose($fp);
            }
        }

        return $status;
    }

    /**
     * Resize avatar
     * @param $review
     */
    private function _resizeAvatar($review) {
        $dirGenerator = new Main_Service_Dir_Generator_Review($review);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
        $filePath = $this->getView()->getPath($dirsInfo, $review->id, "avatar.jpg");

        $img = new Image($filePath);

        if ($img->width > $img->height)
            $img->resize(0, 150, false);
        else
            $img->resize(150, 0, false);

        $cropStartX = $img->width / 2 - 50;
        $cropStartY = $img->height / 2 - 50;

        $img->crop($cropStartX, $cropStartY, $cropStartX + 100, $cropStartY + 100);
        $img->save($filePath);
    }

    /**
     * Resize video thumbnail
     * @param $image
     */
    public function resizeVideoThumbnail($imageName) {
        $img = new Image($imageName);
        $res = false;

        if ($img->width > $img->height) {
            $res = $img->resize(0, 150, false);
        } else {
            $res = $img->resize(150, 0, false);
        }

        if ($res) {
            $cropStartX = $img->width / 2 - 50;
            $cropStartY = $img->height / 2 - 50;

            $newImageName = substr($imageName, 0, strrpos($imageName, ".jpg")) . "-small.jpg";

            $img->crop($cropStartX, $cropStartY, $cropStartX + 100, $cropStartY + 100);
            $img->save($newImageName);
        }
    }

    /**
     * Update review
     * @param Companies_Model_Review $review
     * @return Companies_Model_Review
     */
    public function updateReview(Companies_Model_Review $review) {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $type = $request->getParam('type', null);

        switch ($type) {
            case "status":
                $status = $request->getParam('status', null);

                if ($status != null) {
                    if ($status == Companies_Model_Review::API_STATUS_PROCESSED) {
                        $review->status = Companies_Model_Review::STATUS_NOT_PROCESSED;
                        $review->save();

                        if ($review->client_avatar) {
                            $this->_resizeAvatar($review);
                        }
                    } else {
                        $review = null;
                    }
                } else {
                    $review = null;
                }

                break;

            case "avatar":
                $offset = $request->getParam("offset", null);
                $chunk = file_get_contents("php://input");

                if ($offset == null || strlen($chunk) == 0 || !$this->_uploadAvatar($review, $chunk, $offset)) {
                    $review = null;
                }

                break;

            case "video":
                $offset = $request->getParam("offset", null);
                $format = $request->getParam("format", null);
                $chunk = file_get_contents("php://input");

                if ($offset == null || strlen($chunk) == 0 || !in_array($format, array("mov", "3gp")) || !$this->_uploadVideo($review, $format, $chunk, $offset)) {
                    $review = null;
                }

                break;

            default:
                $review = null;
        }


        return $review;
    }

    /**
     * Convert review to JSON - private function
     * @param Companies_Model_Review $review
     * @return array
     */
    private function _reviewToArray(Companies_Model_Review $review)
    {
        $starFilter = new Main_Service_Filter_FloatToStar();

        return array(
            "id" => $review->id,
            "company_id" => $review->company_id,
            "employee_id" => $review->company_employee_id,
            "email" => $review->mail,
            "name" => $review->client_name,
            "from" => $review->client_from,
            "rating" => $starFilter->filter($review->rating),
            "review" => $review->review
        );
    }

    /**
     * Convert review to JSON
     * @param Companies_Model_Review $review
     * @return string
     */
    public function reviewToJson(Companies_Model_Review $review)
    {
        return json_encode($this->_reviewToArray($review));
    }

    /**
     * Get unconfirmed review by id
     * @param int $id
     */
    public function getUnconfirmedReview($id)
    {
        return $this->getTable()->getUnconfirmedReview($id);
    }

    /**
     * Get review width
     * @param $video
     * @return string
     */
    public static function getVideoWidth($video) {
        $width = '800';

        if (!$video) {
            return '0';
        }

        if ($video->width && $video->height) {
            if ($video->width > $video->height) {
                $width = (string) $video->width;

                if ($video->width > 800) {
                    $width = '800';
                }
            } else {
                $width = '300';
            }
        }

        return $width;
    }

    /**
     * Get video ratio attribute
     * @param $video
     */
    public static function getVideoRatioAttribute($video) {
        $ratio = '';

        if ($video->width && $video->height) {
            $ratio =  ' data-ratio="' . sprintf("%.4f", $video->height / $video->width) . '"';
        }

        return $ratio;
    }

    /**
     * Get text status
     * @param $review
     */
    public static function getTextStatus($review) {
        return self::getTextByStatus($review->status);
    }

    /**
     * Get text by status
     * @param $status
     */
    public static function getTextByStatus($status) {
        $statuses = array(
            "not_processed"  => "Not Processed",
            "processing"     => "Processing",
            "error"          => "Error",
            "published"      => "Published",
            "reconciliation" => "Reconciliation",
            "unconfirmed"    => "Unconfirmed"
        );

        return $statuses[$status];
    }

    /**
     * Paginator for search query result
     * @param $searchString
     * @param Companies_Model_Company $company
     * @param array $statuses
     * @return Zend_Paginator
     */
    public function getSearchPaginator($searchString, Companies_Model_Company $company = null, $statuses = null) {
        $pageNumber = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        $query = $this->getTable()->getQueryToFetchSearchAll($searchString, $company, $statuses);
        $itemsPerPage = @self::getConfig()->pagination->reviews->itemsSearchPerPage;
        $itemsPerPage = (int)$itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();

        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }

    /**
     * Get available statuses
     * @param bool $admin
     */
    public static function getAvailableStatuses($admin=false) {
        return $admin ? array(
            Companies_Model_Review::STATUS_PUBLISHED,
            Companies_Model_Review::STATUS_RECONCILIATION,
            Companies_Model_Review::STATUS_ERROR,
            Companies_Model_Review::STATUS_PROCESSING,
            Companies_Model_Review::STATUS_NOT_PROCESSED,
            Companies_Model_Review::STATUS_UNCONFIRMED,
        ) : array(
            Companies_Model_Review::STATUS_PUBLISHED,
            Companies_Model_Review::STATUS_RECONCILIATION,
        );
    }

    /**
     * Update review's comment
     * @param Companies_Model_Review $review
     */
    public function updateComment(Companies_Model_Review $review) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm("review-comment");

        if (!$form->isValid($post)) {
            $form->populate($post);
            self::addProcessingInfo('Please fix the errors below.');
            throw new Exception();
        }

        $review->owner_comment = $form->getValue("comment");
        $review->save();

        Main_Service_Models::addProcessingInfo("Comment saved", Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);
    }

    /**
     * Get review page count for company
     * @param $companyId
     */
    public function getReviewPageCountForCompany($companyId) {
        $reviews = $this->getTable()->getCompanyReviewCount($companyId);
        $pages = (int)($reviews / 50);

        if ($reviews % 50) {
            $pages++;
        }

        return $pages;
    }

    /**
     * Get review page count for employee
     * @param $employeeId
     */
    public function getReviewPageCountForEmployee($employeeId) {
        $reviews = $this->getTable()->getCompanyReviewCount($employeeId);
        $pages = (int)($reviews / 50);

        if ($reviews % 50) {
            $pages++;
        }

        return $pages;
    }
}