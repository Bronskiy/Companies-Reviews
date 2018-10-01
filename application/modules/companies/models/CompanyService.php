<?php

require_once("Image/Image.php");

/**
 * Company service
 */
class Companies_Model_CompanyService extends Main_Service_Models {
    /**
     * Current company
     * @var Companies_Model_Company 
     */
    protected $_company = false;
    
    /**
     * Creating folders for company
     * @param Companies_Model_Company $company 
     */
    public function createCompanyFolders(Companies_Model_Company $company)
    {
        $dirGenerator = new Main_Service_Dir_Generator_Company($company);
        $dirGenerator->generateFoldersByRule();
    }

    /**
     * Attempt send mail to the company owner
     * 
     * @param Companies_Model_Company $company 
     */
    public function contact(Companies_Model_Company $company)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        
        $form = $this->getForm('contact');
        
        if ($form->isValid($post))
        {
            try
            {
                $owner = $company->Users->get(0);
            
                $userService = new Users_Model_UserService();
                $userService->sendContactMail($owner, $form->getValues());

                Main_Service_Models::addProcessingInfo(
                    'Message sent',
                    Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                );
                
                return true;
            }
            catch (Exception $e)
            {
                self::getLogger()->log($e);
                self::addProcessingInfo('Error sending message, please contact the administrator or try again.');

                return false;
            }            
        }
        else
        {
            $form->populate($post);

            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME))
                self::addProcessingInfo('Error sending message, please contact the administrator or try again.');
            else
                self::addProcessingInfo('Please fix the errors below.');
        }

        return false;
    }
    
    /**
     * Updating company data
     * @param Companies_Model_Company $company 
     */
    public function updateCompany(Companies_Model_Company $company) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('company', 'companies');
        
        if ($form->isValid($post)) {
            $filter = new Main_Service_Filter_StringToUri();
           
            if ($form->getValue('local_business')) {
                $uri = '/' . $filter->filter($form->getValue('name')) .
                       '/' . $filter->filter($form->getValue('city')) .
                       '/' . $filter->filter($form->getValue('state'));
            } else {
                $uri = '/' . $filter->filter($form->getValue('name'));
            }

            $validator = new Main_Validate_NoOtherSameRecords(
                'companies', 'uri', array('id' => $company->id)
            );
            
            if (!$validator->isValid($uri)) {
                $form->name->addError(
                    $form->getValue('name') . ' in ' .
                    $form->getValue('city') . ', ' . $form->getValue('state') .
                    ' already exists'
                );

                return false;
            }

            $validator = new Main_Validate_NoOtherSameRecords("companies", "code_num", array("id" => $company->id));

            if (!$validator->isValid($form->getValue('code_num'))) {
                $form->code_num->addError("Company with this code already exists");
                return false;
            }
            
            try {
                $website = $form->getValue('website');
                $facebook = $form->getValue('facebook_link');
                $twitter = $form->getValue('twitter_link');
                $linkedin = $form->getValue('linkedin_link');
                $google = $form->getValue('google_link');
                $yelp = $form->getValue('yelp_link');

                if ($website && substr($website, 0, 7) != 'http://' && substr($website, 0, 8) != 'https://') {
                    $website = 'http://' . $website;
                }

                if ($facebook && substr($facebook, 0, 7) != 'http://' && substr($facebook, 0, 8) != 'https://') {
                    $facebook = 'http://' . $facebook;
                }
                
                if ($twitter && substr($twitter, 0, 7) != 'http://' && substr($twitter, 0, 8) != 'https://') {
                    $twitter = 'http://' . $twitter;
                }
                
                if ($linkedin && substr($linkedin, 0, 7) != 'http://' && substr($linkedin, 0, 8) != 'https://') {
                    $linkedin = 'http://' . $linkedin;
                }
                
                if ($google && substr($google, 0, 7) != 'http://' && substr($google, 0, 8) != 'https://') {
                    $google = 'http://' . $google;
                }

                if ($yelp && substr($yelp, 0, 7) != 'http://' && substr($yelp, 0, 8) != 'https://') {
                    $yelp = 'http://' . $yelp;
                }
                
                $company->fromArray(array(
                    'name' => $form->getValue('name'),
                    'local_business' => $form->getValue('local_business'),
                    'show_address' => $form->getValue('show_address'),
                    'address' => $form->getValue('address'),
                    'city' => $form->getValue('city'),
                    'state' => $form->getValue('state'),
                    'zip' => $form->getValue('zip'),
                    'phone' => $form->getValue('phone'),
                    'website' => $website,
                    'mail' => $form->getValue('mail'),
                    'business_since' => $form->getValue('business_since'),
                    'owner' => $form->getValue('owner'),
                    'about_us' => $form->getValue('about_us'),
                    'facebook_link' => $facebook,
                    'twitter_link' => $twitter,
                    'linkedin_link' => $linkedin,
                    'google_link' => $google,
                    'yelp_link' => $yelp,
                    'offered_services' => $form->getValue('offered_services'),
                    'review_email_text' => $form->getValue('review_email_text'),
                    'latitude' => $form->getValue('latitude'),
                    'longitude' => $form->getValue('longitude'),
                    'uri' => $uri
                ));

                $company->category_id = $form->getValue('category_id');
                $company->code_num = $form->getValue('code_num');

                if (in_array(self::getAuthUser()->Role->name, array(Users_Model_Role::ADMIN_ROLE, Users_Model_Role::SUBADMIN_ROLE))) {
                    $company->code_letter = $form->getValue("code_letter");
                }

                $company->save();

                if ($form->logo->isUploaded()) {
                    $origName = pathinfo($form->logo->getFileName());
                    $newName = 'logo.' . $origName['extension'];

                    // get dirs info for cur review
                    $dirGenerator = new Main_Service_Dir_Generator_Company($company);
                    $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
                    $uploadedFileNewPath = $this->getView()->getPath($dirsInfo, 'images', $newName);

                    $form->logo->addFilter('Rename', array(
                        'target'    => $uploadedFileNewPath,
                        'overwrite' => true
                    ));
                    $form->logo->receive();

                    $company->logo = $newName;
                    $company->save();
                }
                
                if ($form->video->isUploaded()) {
                    $this->_uploadVideoFile($company, $form->video);
                }

                Main_Service_Models::addProcessingInfo(
                    'Profile saved',
                    Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                );

                return true;
            } catch (Exception $e) {
                self::getLogger()->log($e);
                self::addProcessingInfo('Error saving profile, please contact the administrator or try again.');
            }
        } else {
            $form->populate($post);

            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME)) {
                self::addProcessingInfo('Error saving profile, please contact the administrator or try again.');
            } else {
                self::addProcessingInfo('Please fix the errors below.');
            }
        }

        return false;
    }

    /**
     * Delete profile videos
     * @param $company
     */
    private function _deleteProfileVideos($company) {
        $dirGenerator = new Main_Service_Dir_Generator_Company($company);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();

        $table = Companies_Model_CompanyVideoTable::getInstance();
        $video = $table->getAboutUs($company->id);

        if (!$video->exists()) {
            return;
        }

        foreach ($video->Streams as $stream) {
            $ext = self::getExtensionByMimeType($stream->type);
            $file = $this->getView()->getPath($dirsInfo, 'videos', $video->name . "." . $ext);
            @unlink($file);

            $stream->delete();
        }

        $video->delete();
    }
    
    /**
     * Upload video
     * @param Companies_Model_Company $company
     * @param Zend_Form_Element $uploadedVideo
     */
    private function _uploadVideoFile(Companies_Model_Company $company, Zend_Form_Element $uploadedVideo) {
        $this->_deleteProfileVideos($company);
        
        $origName = pathinfo($uploadedVideo->getFileName());
        $newName = hash('sha256', time() . rand() . $origName['basename']);
        $ext = $origName['extension'];

        $dirGenerator = new Main_Service_Dir_Generator_Company($company);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();

        $uploadedFileNewPath = $this->getView()->getPath($dirsInfo, 'videos', $newName . "." . $ext);
        $uploadedVideo->addFilter('Rename', array(
            'target' => $uploadedFileNewPath,
            'overwrite' => true
        ));
        $uploadedVideo->receive();

        $companyVideo = new Companies_Model_CompanyVideo();
        $companyVideo->fromArray(array(
            "company_id" => $company->id,
            "name" => $newName,
            "status" => Companies_Model_CompanyVideo::STATUS_NOT_PROCESSED,
        ));
        $companyVideo->save();

        $stream = new Companies_Model_VideoStream();
        $stream->fromArray(array(
            "video_id" => $companyVideo->id,
            "type" => $uploadedVideo->getMimeType(),
            "status" => Companies_Model_VideoStream::STATUS_NOT_PROCESSED,
            "is_source" => true,
        ));
        $stream->save();
    }
    
    /**
     * Delete company video
     * @param $id
     */
    public function deleteVideo($id) {
        try {
            $company = $this->getCompanyById($id);
            $this->_deleteProfileVideos($company);

            self::addProcessingInfo('Video deleted.', self::PROCESSING_INFO_SUCCESS_TYPE);
        } catch (Exception $e) {
            self::getLogger()->log($e->getMessage());
            self::addProcessingInfo('Error deleting video, please contact the administrator or try again.');
        }
    }
    
    /**
     * Uploading company images
     * 
     * @param Companies_Model_Company $company 
     * @param string $name
     */
    public function addImage(Companies_Model_Company $company)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('company-image', 'companies');

        if ($form->isValid($post))
        {
            $curConnection = Doctrine_Manager::connection();

            try
            {
                $curConnection->beginTransaction();

                if ($form->image->isUploaded())
                {
                    $origName = pathinfo($form->image->getFileName());
                    $newName = hash('sha256', $form->image->getFileName() . time() . rand());

                    $image = new Companies_Model_Image();
                    $image->company_id = $company->id;
                    $image->name = $newName;
                    $image->extension = $origName['extension'];
                    $image->save();

                    // get dirs info for cur review
                    $dirGenerator = new Main_Service_Dir_Generator_Company($company);
                    $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
                    $uploadedFileNewPath = $this->getView()->getPath($dirsInfo, 'gallery', $newName . "." . $origName['extension']);

                    $form->image->addFilter('Rename', array(
                        'target'    => $uploadedFileNewPath,
                        'overwrite' => true
                    ));

                    $form->image->receive();

                    $this->resizeImageThumbnail(
                        $uploadedFileNewPath,
                        $this->getView()->getPath($dirsInfo, 'gallery', $newName . "-small." . $origName['extension'])
                    );

                    $curConnection->commit();
                }

                Main_Service_Models::addProcessingInfo('Image added', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

                return true;
            }
            catch(Exception $e)
            {
                $curConnection->rollback();
                self::getLogger()->log($e);
                self::addProcessingInfo('Error adding image, please contact the administrator or try again.');
            }
        }
        else
        {
            self::addProcessingInfo('Please fix the errors below.');
        }

        return false;
    }

    /**
     * Resize image thumbnail
     * @param $image
     */
    public function resizeImageThumbnail($image, $newImage) {
        $img = new Image($image);

        if ($img->width > $img->height)
            $img->resize(0, 150, false);
        else
            $img->resize(150, 0, false);

        $cropStartX = $img->width / 2 - 50;
        $cropStartY = $img->height / 2 - 50;

        $img->crop($cropStartX, $cropStartY, $cropStartX + 100, $cropStartY + 100);
        $img->save($newImage);
    }
    
    /**
     * Saving one image in Db and File system
     * 
     * @param Main_Service_Download_Company_Image $upload
     * @param Companies_Model_Company $company
     * @param array $dirsInfo
     * @param string $inner_name
     * @param string $path
     * @return boolean 
     */
    protected function _saveImage(Main_Service_Download_Company_Image $upload, 
                                    Companies_Model_Company $company, 
                                    $dirsInfo, $inner_name, $path)
    {
        $filePathInfo = pathinfo($path);
        
        $image = new Companies_Model_Image();
        $image->company_id = $company->id;
        $image->name = 'tmp';
        $image->save();

        $newName = hash('sha256', time() . rand() . $image->id) .'.'. $filePathInfo['extension'];
        
        $uploadedFileNewPath = $this->getView()->getPath($dirsInfo, 'gallery', $newName);
        $upload->addFilter('Rename', 
                           array('target' => $uploadedFileNewPath, 'overwrite' => true),
                           $inner_name);
        
        if (!$upload->receive($inner_name)) {
            self::addProcessingInfo('Error saving image, please contact the administrator or try again.');
            return false;
        }
        
        $image->name = $newName;
        $image->save();
        
        return true;
    }
    
    /**
     * Deleting company
     * @param Companies_Model_Company $company 
     */
    public function deleteCompany(Companies_Model_Company $company) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || !$this->isValidCsrfToken($post)) {
            self::addProcessingInfo('Error deleting company, please contact the administrator or try again.');
            return false;
        }
        
        try {
            $users = $company->Users;
            $company->status = Companies_Model_Company::STATUS_DELETED;
            $company->uri = null;
            $company->code_num = null;
            $company->save();
            
            foreach ($users as $user) {
                $user->status = Users_Model_User::STATUS_DELETED;
                $user->save();
            }

            Main_Service_Models::addProcessingInfo(
                'Company deleted.',
                Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
            );

            return true;
        } catch (Exception $e) {
            self::getLogger()->log($e->getMessage());
        }

        self::addProcessingInfo('Error deleting company, please contact the administrator or try again.');

        return false;
    }
    
    /**
     * Deleting user
     * 
     * @param Users_Model_User $user
     * @return boolean 
     */
    public function deleteUser(Users_Model_User $user)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || ! $this->isValidCsrfToken($post)) {
            self::addProcessingInfo('Error deleting user, please contact the administrator or try again.');
            return false;
        }

        $userService = new Users_Model_UserService();
        $userService->deleteUser($user);

        Main_Service_Models::addProcessingInfo('User deleted', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

        return true;
    }
    
    /**
     * Deleting company image
     *  
     * @param int $imageId
     * @param int $companyId 
     */
    public function deleteCompanyImage($imageId, Companies_Model_Company $company)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || ! $this->isValidCsrfToken($post)) {
            self::addProcessingInfo('Error deleting image, please contact the administrator or try again.');
            return false;
        }

        try {
            $image = $this->getTable('image')->findOneByIdAndCompanyId($imageId, $company->id);

            if ($image !== false) {
                $imgName = $image->name;
                $extension = $image->extension;

                if ($image->delete()) {
                    $dirGenerator = new Main_Service_Dir_Generator_Company($company);
                    $dirsInfo = $dirGenerator->getFoldersPathsFromRule();

                    @unlink($this->getView()->getPath($dirsInfo, 'gallery', $imgName . "." . $extension));
                    @unlink($this->getView()->getPath($dirsInfo, 'gallery', $imgName . "-small." . $extension));

                    Main_Service_Models::addProcessingInfo('Image deleted', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

                    return true;
                }
            }     
        } catch(Exception $e) {
            self::getLogger()->log($e);
        }

        self::addProcessingInfo('Error deleting image, please contact the administrator or try again.');

        return false;
    }

    /**
     * Delete company logo
     * @param int $companyId
     */
    public function deleteLogo(Companies_Model_Company $company)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || !$this->isValidCsrfToken($post)) {
            self::addProcessingInfo('Error deleting logo, please contact the administrator or try again.');
            return false;
        }

        try {
            $image = $company->logo;

            if ($image) {
                $company->logo = null;
                $company->save();

                // delete file from file system
                $dirGenerator = new Main_Service_Dir_Generator_Company($company);
                $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
                @unlink($this->getView()->getPath($dirsInfo, 'images', $image));

                Main_Service_Models::addProcessingInfo('Logo deleted', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

                return true;
            }
        } catch(Exception $e) {
            self::getLogger()->log($e);
        }

        self::addProcessingInfo('Error deleting logo, please contact the administrator or try again.');

        return false;
    }

    /**
     * Get current company
     * @param int $id 
     */
    public function getCompanyById($id) {
        if(!$this->_company) {
            $this->_company = $this->getTable()->findOneById((int)$id);
        }

        return $this->_company;
    }
    
    /**
     * Get company by uri
     * @param type $uri
     * @return Companies_Model_Company | false 
     */
    public function getCompanyByUri($uri = null) {
        if (!$uri) {
            $uriParts = array();

            foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $k => $v) {
                if (in_array($k, array("name", "city", "state"))) {
                    $uriParts[] = $v;
                }
            }

            $uri = "/" . implode("/", $uriParts);
        }

        if (!$this->_company) {
            $this->_company = $this->getTable()->findFirstByUri($uri);
        }

        return $this->_company;
    }

    /**
     * Get company
     * @param int id
     */
    public function getCompany($id)
    {
        return $this->getTable()->getAvailableCompany($id);
    }

    /**
     * Get company list
     * @param string $code
     */
    public function getCompanyList($code=null)
    {
        $companies = null;

        if ($code != null)
            $companies = $this->getTable()->getAvailableCompaniesByCode($code);
        else
            $companies = $this->getTable()->getAvailableCompanies();

        return $companies;
    }

    /**
     * Notify or send a coupon to reviewer
     * @param Companies_Model_Review $review 
     */
    public function notifyReviewer(Companies_Model_Review $review) {
        $coupon = $review->Company->Coupons->get(0);
        $couponReviewTable = Companies_Model_CouponReviewTable::getInstance();

        $links = array();
        $serviceLinks = array(
            "Google+" => $review->Company->google_link,
            "Facebook" => $review->Company->facebook_link,
            'Linkedin' => $review->Company->linkedin_link,
            "Twitter" => $review->Company->twitter_link
        );

        foreach ($serviceLinks as $title => $link) {
            if ($link[1]) {
                $links[] = array($title, $link);
            }
        }

        $templateVars = array(
            'userName' => !empty($review->client_name) ? $review->client_name : 'client',
            'links' => $links,
        );

        try {
            if ($coupon->exists()) {
                if ($couponReviewTable->wasSent($coupon->id, $review->id)) {
                    return;
                }

                $dirGenerator = new Main_Service_Dir_Generator_Coupon($coupon);
                $dirsInfo = $dirGenerator->getFoldersPathsFromRule(false);
                $imgSrc = $this->getView()->getPath($dirsInfo, 'coupon_images', $coupon->image);

                $templateVars['imgSrc'] = 'http://' . $this->getConfig()->domain . $imgSrc;

                $this->getView()->assign($templateVars);
                $mailBody = $this->getView()->render('reviewer-coupon.phtml');

                $mailConfig = array(
                    'toMail' => $review->mail,
                    'body' => $mailBody,
                    'fromText' => 'Revudio',
                    'subject' => 'Discount Coupon'
                );

                $mail = new Main_Mail_Smtp($mailConfig);
                $mail->send();

                $couponReviewTable->setSent($coupon->id, $review->id);
            } else {
                $this->getView()->assign($templateVars);
                $mailBody = $this->getView()->render('thank-you.phtml');

                $mailConfig = array(
                    'toMail' => $review->mail,
                    'body' => $mailBody,
                    'fromText' => 'Revudio',
                    'subject' => 'Thank You'
                );

                $mail = new Main_Mail_Smtp($mailConfig);
                $mail->send();
            }
        } catch (Exception $e) {
            self::getLogger()->log($e->getMessage());
        }
    }
    
    /**
     * Companies paginator
     * @return Zend_Paginator 
     */
    public function getCompaniesPaginator(Doctrine_Query $query = null, $pageNumber = null, $status = null) {
        if ($pageNumber === null) {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $pageNumber = abs((int)$request->getParam('page', 1));
        }

        $query = $query !== null ? $query : $this->getTable()->getQueryToFetchAll($status);
        
        $itemsPerPage = @self::getConfig()->pagination->companies->itemsPerPage;
        $itemsPerPage = (int)$itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();

        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }
    
    /**
     * Searching companies in specific category
     * 
     * @param int | string $categoryUri
     * @param int $page 
     * @return Zend_Paginator | false
     */
    public function getCompaniesPaginatorByCategory($categoryUri, $page, $state, $city) {
        if ($categoryUri == Companies_Model_Category::UNCATEGORIZED) {
            $query = $this->getTable()->getUncategorizedCompaniesQuery($state, $city);
            return $this->getCompaniesPaginator($query, $page);
        } else {
            $category = $this->getTable('category')->getByUri($categoryUri);

            if (!$category) {
                return false;
            }

            $query = $this->getTable()->getCompaniesByCategoryQuery($category->id, $state, $city);
            return $this->getCompaniesPaginator($query, $page);
        }

        return false;
    }
    
    /**
     * Paginator by city name and category id
     * 
     * @param string $city
     * @param int | null $catId 
     */
    public function getCompaniesPaginatorByCityCategory($page, $city, $state, $catId = null)
    {
        $query = $this->getTable()->getCompaniesByCityCategoryQuery($city, $state, $catId);
        return $this->getCompaniesPaginator($query, $page);
    }

    /**
     * Get latest articles by city category
     * @param string $city
     * @param string $state
     * @param int | null $catId
     * @param int $count
     * @return Doctrine_Collection
     */
    public function getLatestArticlesByCityCategory($city, $state, $catId = null, $count = 3) {
        return $this->getTable("company-article")->getLatestByCityCategory($city, $state, $catId, $count);
    }

    /**
     * Get latest articles by category
     * @param int | null $catId
     * @param int $count
     * @return Doctrine_Collection
     */
    public function getLatestArticlesByCategory($catId = null, $count = 3) {
        return $this->getTable("company-article")->getLatestByCategory($catId, $count);
    }

    /**
     * Paginator by category id
     * @param $page
     * @param $catId
     * @param int | null $catId
     */
    public function getNationalCompaniesPaginatorByCategory($page, $catId = null) {
        $query = $this->getTable()->getNationalCompaniesByCategoryQuery($catId);
        return $this->getCompaniesPaginator($query, $page);
    }

    /**
     * Paginator by category id (all companies)
     * @param $page
     * @param $catId
     */
    public function getCompanyPaginatorByCategory($page, $catId) {
        $query = $this->getTable()->getCompaniesByCategoryQuery($catId, null, null, false);
        return $this->getCompaniesPaginator($query, $page);
    }

    /**
     * Paginator by letter (all companies)
     * @param $page
     * @param $letter
     */
    public function getCompanyPaginatorByLetter($page, $letter) {
        $query = $this->getTable()->getCompaniesByLetterQuery($letter);
        return $this->getCompaniesPaginator($query, $page);
    }
    
    /**
     * Paginator for search query result
     * @param string $searchString
     * @param array $statuses
     */
    public function getSearchPaginator($searchString, $statuses=null) {
        $pageNumber = (int)Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        $query = $this->getTable()->getQueryToFetchSearchAll($searchString, $statuses);
        $itemsPerPage = @self::getConfig()->pagination->companies->itemsSearchPerPage;
        $itemsPerPage = (int)$itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();

        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }

    /**
     * Search
     * 
     * @param Main_Session_Search_Abstract $data
     */
    public function search(Main_Session_Search_Abstract $container)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('search');

        if ($form->isValid($post))
        {
            try
            {
                $query = $form->getValue('search');
                $container->saveSearchData($query);

                return true;
            }
            catch(Exception $e)
            {
                self::getLogger()->log($e);
                self::addProcessingInfo('Error searching company');

                return false;
            }
        }
        else
        {
            $form->populate($post);
        }

        return false;
    }

    /**
     * Company account activation
     * @param $company
     * @param $next
     */
    private function _activate($company, $next=null) {
        try {
            $company->status = Companies_Model_Company::STATUS_ACTIVE;

            if ($next != null) {
                $company->payment_date = $next->format('Y-m-d H:i:s');
            }

            $company->save();
        } catch (Exception $e) {
            self::getLogger()->log($e);
        }
    }

    /**
     * Apply discount
     * @param $company
     */
    public function applyDiscount($company) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('discount-code');
        
        if ($form->isValid($post)) {
            try {
                $code = $form->getValue('code');
                $discount = $this->getTable('discount')->findOneByCodeAndPlanIdAndStatus(
                    $code,
                    $company->plan_id,
                    Companies_Model_Discount::STATUS_ACTIVE
                );
                
                if (!$discount) {
                    self::addProcessingInfo('Invalid discount code, please try again.');
                    return;
                }

                $company->discount_id = $discount->id;
                $company->save();

                $plan = $company->Plan;
                $monthly = $plan->monthly_fee - $discount->monthly_discount;
                $firstMonth = $plan->setup_fee + $plan->monthly_fee - $discount->first_month_discount;

                // if it's a free account
                if ($firstMonth <= 0 && $monthly <= 0) {
                    $this->_activate($company, new DateTime("2099-12-31 23:59:59"));

                    self::addProcessingInfo(
                        'Free account activated. Congratulations on signing up your business with Revudio!',
                        Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                    );
                } else {
                    self::addProcessingInfo(
                        'Discount code applied.',
                        Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                    );
                }
            } catch (Exception $e) {
                self::getLogger()->log($e);
                self::addProcessingInfo('Error processing discount code, please contact the administrator or try again.');
            }
        } else {
            $form->populate($post);
            self::addProcessingInfo('Please fix the errors below.');
        }
    }

    private function _deleteCardBraintree($token) {
        $card = Braintree_CreditCard::find($token);

        if (!$card) {
            return;
        }

        if ($card->billingAddress && $card->billingAddress->id) {
            Braintree_Address::delete($card->billingAddress->id);
        }

        Braintree_CreditCard::delete($token);
    }

    /**
     * Delete company cards
     * @param $company
     * @param $newToken
     */
    private function _deleteCards($company, $newToken=null) {
        $cards = $this->getTable('company-card')->findByCompanyId($company->id);

        foreach ($cards as $card) {
            if ($card->token == $newToken) {
                continue;
            }

            try {
                $this->_deleteCardBraintree($card->token);
            } catch (Exception $e) {
                self::getLogger()->log($e);
            }

            $card->delete();
        }
    }

    /**
     * Add credit card
     * @param $company
     */
    public function addCard($company) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('company-card');
        $errorMessage = "";

        if (!$form->isValid($post)) {
            if (!$form->getValue("agree", null)) {
                $form->getElement("agree")->setErrors(array("Please check this checkbox to continue."));
            }

            $form->populate($post);

            self::addProcessingInfo('Please fix the errors below.');
            return false;
        }

        if (!$form->getValue("agree", null)) {
            $form->getElement("agree")->setErrors(array("Please check this checkbox to continue."));
            $form->populate($post);
            self::addProcessingInfo('Please fix the errors below.');

            return false;
        }

        try {
            self::configureBrainTree();
            $customer = null;

            // check if customer already exists on Braintree
            try {
                $customer = Braintree_Customer::find($company->id);
            } catch (Braintree_Exception_NotFound $e) {
                $result = Braintree_Customer::create(array(
                    'id' => $company->id,
                    'company' => $company->name,
                ));

                if (!$result->success) {
                    throw new Exception('Error creating Braintree customer: ' . $result->message);
                }
            }

            $name = $form->getValue("name");
            $name = explode(" ", $name);

            $firstName = $name[0];

            if (count($name) > 1) {
                $lastName = $name[1];
            }

            // create a new card
            $result = Braintree_CreditCard::create(array(
                'customerId' => $company->id,
                'number' => $form->getValue('number'),
                'expirationMonth' => $form->getValue('month'),
                'expirationYear' => $form->getValue('year'),
                'cvv' => $form->getValue('cvv'),
                'billingAddress' => array(
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'streetAddress' => $form->getValue('address'),
                    'postalCode' => $form->getValue('zip'),
                    'locality' => $form->getValue('city'),
                    'region' => $form->getValue('state'),
                    'countryCodeAlpha2' => 'US',
                ),
                'options' => array(
                    'verifyCard' => true,
                    'makeDefault' => true
                )
            ));

            if (!$result->success) {
                $errorMessage = "Invalid card.";
                throw new Exception('Error adding card to Braintree: ' . $result->message);
            }

            $newCardToken = $result->creditCard->token;

            $card = new Companies_Model_CompanyCard();
            $card->company_id = $company->id;
            $card->number = $result->creditCard->maskedNumber;
            $card->exp_date = $result->creditCard->expirationDate;
            $card->type = $result->creditCard->cardType;
            $card->token = $newCardToken;
            $card->name = $form->getValue("name");
            $card->address = $form->getValue("address");
            $card->state = $form->getValue("state");
            $card->city = $form->getValue("city");
            $card->zip = $form->getValue("zip");
            $card->save();

            try {
                $company->subscription_id = null;

                $config = self::getConfig()->braintree;
                $result = null;
                $trial = false;
                $free = false;

                if ($company->status == Companies_Model_Company::STATUS_NOT_ACTIVATED) {
                    $plan = $company->Plan;
                    $discount = null;

                    if ($company->discount_id) {
                        $discount = $company->Discount;
                    }

                    $monthly = $plan->monthly_fee;
                    $firstMonth = $plan->setup_fee + $plan->monthly_fee;

                    if ($discount) {
                        $firstMonth -= $discount->first_month_discount;
                        $monthly -= $discount->monthly_discount;
                    }

                    if ($firstMonth > 0 || $monthly > 0) {
                        $addons = array();
                        $discounts = array();
                        $basePrice = $plan->monthly_fee;

                        // monthly discount
                        if ($monthly > 0 && $monthly < $basePrice && $discount) {
                            $discounts[] = array(
                                "amount" => $discount->monthly_discount,
                                "inheritedFromId" => "discount1",
                            );

                            $basePrice = $monthly;
                        }

                        // first month addon/discount, depending on the situation
                        if ($firstMonth > 0) {
                            if ($firstMonth > $basePrice) {
                                $addons[] = array(
                                    "amount" => $firstMonth - $basePrice,
                                    "numberOfBillingCycles" => 1,
                                    "inheritedFromId" => "addon1",
                                );
                            } else if ($firstMonth < $basePrice) {
                                $discounts[] = array(
                                    "amount" => $basePrice - $firstMonth,
                                    "numberOfBillingCycles" => 1,
                                    "inheritedFromId" => "discount2",
                                );
                            }
                        }

                        $options = array(
                            "paymentMethodToken" => $card->token,
                            "planId" => $config->defaultPlanId,
                            "price" => $plan->monthly_fee,
                        );

                        if (count($addons) > 0) {
                            $options["addOns"] = array(
                                "add" => $addons
                            );
                        }

                        if (count($discounts) > 0) {
                            $options["discounts"] = array(
                                "add" => $discounts
                            );
                        }

                        if ($firstMonth <= 0) {
                            // 1 month trial
                            $additionalOptions = array(
                                "trialPeriod" => true,
                                "trialDuration" => 1,
                                "trialDurationUnit" => "month",
                            );

                            $trial = true;
                        } else if ($monthly <= 0) {
                            // free with setup
                            $additionalOptions = array(
                                "numberOfBillingCycles" => 1,
                                "options" => array(
                                    "startImmediately" => true,
                                ),
                            );

                            $free = true;
                        } else {
                            // normal situation
                            $additionalOptions = array(
                                "options" => array(
                                    "startImmediately" => true,
                                ),
                            );
                        }

                        $options = array_merge($options, $additionalOptions);
                        $result = Braintree_Subscription::create($options);
                    }
                } else {
                    $basePrice = $company->Plan->monthly_fee;

                    if ($company->discount_id) {
                        $discount = $company->Discount->monthly_discount;
                    } else {
                        $discount = 0;
                    }

                    $options = array(
                        "paymentMethodToken" => $card->token,
                        "planId" => $config->defaultPlanId,
                        "price" => $basePrice,
                    );

                    $now = new DateTime();

                    if ($company->payment_date) {
                        $nextPayment = new DateTime($company->payment_date);
                    } else {
                        $nextPayment = new DateTime();
                    }

                    if ($nextPayment > $now) {
                        $options["firstBillingDate"] = $nextPayment;
                    } else {
                        $options["options"] = array(
                            "startImmediately" => true,
                        );

                        $company->payment_date = $now->format('Y-m-d H:i:s');
                        $company->save();
                    }

                    if ($basePrice - $discount > 0) {
                        if ($discount > 0) {
                            $options["discounts"] = array(
                                "add" => array(
                                    array(
                                        "amount" => $discount,
                                        "inheritedFromId" => "discount1",
                                    ),
                                ),
                            );
                        }

                        $result = Braintree_Subscription::create($options);
                    }
                }

                if ($result) {
                    if ($result->success) {
                        $company->subscription_id = $result->subscription->id;
                        $company->save();

                        // delete old cards
                        try {
                            $this->_deleteCards($company, $newCardToken);
                        } catch (Exception $e) {
                            self::getLogger()->log($e);
                        }
                    } else {
                        $errorMessage = "Card declined.";
                        throw new Exception('Error saving subscription: ' . $result->message);
                    }
                }

                if ($company->status == Companies_Model_Company::STATUS_NOT_ACTIVATED) {
                    $paymentDate = null;

                    if ($trial) {
                        $now = new DateTime();
                        $now->add(new DateInterval("P1M"));
                        $paymentDate = $now;

                        Main_Service_Models::addProcessingInfo(
                            "Trial account activated. Congratulations on signing up your business with Revudio! Please continue adding additional information to your profile below.",
                            Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                        );
                    } elseif ($free) {
                        $paymentDate = new DateTime("2099-12-31 23:59:59");

                        Main_Service_Models::addProcessingInfo(
                            "Free account activated. Congratulations on signing up your business with Revudio! Please continue adding additional information to your profile below.",
                            Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                        );
                    } else {
                        Main_Service_Models::addProcessingInfo(
                            "Account activated. Congratulations on signing up your business with Revudio! You will receive an email with a confirmation of payment. Please continue adding additional information to your profile below.",
                            Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                        );
                    }

                    $this->_activate($company, $paymentDate);
                } elseif ($company->status == Companies_Model_Company::STATUS_SUSPENDED) {
                    $this->_activate($company);

                    Main_Service_Models::addProcessingInfo(
                        'Company profile activated.',
                        Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                    );
                } else {
                    Main_Service_Models::addProcessingInfo(
                        'Card changed.',
                        Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                    );
                }

                return true;
            } catch (Exception $e) {
                self::getLogger()->log($e);

                // delete added card
                $cards = $this->getTable('company-card')->findByCompanyIdAndToken($company->id, $newCardToken);

                foreach ($cards as $card) {
                    $card->delete();
                }

                // delete from braintree
                try {
                    $this->_deleteCardBraintree($newCardToken);
                } catch (Exception $e) {
                    self::getLogger()->log($e);
                }
            }
        } catch (Exception $e) {
            self::getLogger()->log($e);
        }

        if (!$errorMessage) {
            $errorMessage = "Error adding credit card, please contact the administrator or try again.";
        }

        self::addProcessingInfo($errorMessage);
        
        return false;
    }

    /**
     * Canceling subscription
     * 
     * @param Companies_Model_Company $company 
     */
    public function cancelSubscription(Companies_Model_Company $company) {
        if ($company->subscription_id) {
            try {
                self::configureBrainTree();

                $result = Braintree_Subscription::cancel($company->subscription_id);

                if (!$result->success) {
                    throw new Exception("Error cancelling subscription: " . $result->message);
                }
            } catch(Exception $e) {
                self::getLogger()->log($e->getMessage());
                return false;
            }
        }

        $company->subscription_id = null;
        $company->status = Companies_Model_Company::STATUS_CANCELLED;
        $company->save();

        return true;
    }
    
    /**
     * Processing braintree payment status notification
     * @return boolean 
     */
    public function processBraintreeWebHook() {
        $curConnection = Doctrine_Manager::connection();
        
        try {
            $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
            
            $btSignature = $post['bt_signature'];
            $btPayload = $post['bt_payload'];
            
            $webhookNotification = Braintree_WebhookNotification::parse(
                $btSignature,
                $btPayload
            );
            
            $subscriptionId = $webhookNotification->subscription->id;
            $company = $this->getTable()->findOneBySubscriptionId($subscriptionId);

            if (!$company) {
                return false;
            }

            $admins = Users_Model_UserTable::getInstance()->getAdmins();
            
            if ($webhookNotification->kind == Braintree_WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY) {
                $curConnection->beginTransaction();
                $subscription = Braintree_Subscription::find($subscriptionId);

                if ($company->payment_date) {
                    $next = new DateTime($company->payment_date);
                } else {
                    $next = new DateTime();
                }

                $next->add(new DateInterval("P1M"));
                $this->_activate($company, $next);

                $companyPayment = new Companies_Model_CompanyPayment();
                $companyPayment->company_id = $company->id;
                $companyPayment->amount = $subscription->transactions[0]->amount;
                $companyPayment->plan = $company->Plan->name;
                $companyPayment->discount = 0;

                if ($company->discount_id) {
                    $discount = $company->Discount;
                    $amount = $companyPayment->amount;

                    if ($amount + $discount->first_month_discount == $company->Plan->setup_fee + $company->Plan->monthly_fee) {
                        $discount = $discount->first_month_discount;
                    } else {
                        $discount = $discount->monthly_discount;
                    }

                    $companyPayment->discount = $discount;
                }

                $companyPayment->save();
                $curConnection->commit();
                
                try {
                    self::getMailer()->notifySubscriptionCharged($company, $subscription->transactions[0]->amount);

                    foreach ($admins as $admin) {
                        self::getMailer()->notifyAdminSubscriptionCharged($admin, $company, $subscription->transactions[0]->amount);
                    }
                } catch (Exception $e) {
                    self::getLogger()->log($e->getMessage());
                }
            } elseif ($webhookNotification->kind == Braintree_WebhookNotification::SUBSCRIPTION_CHARGED_UNSUCCESSFULLY) {
                try {
                    self::getMailer()->notifySubscriptionNotCharged($company);

                    foreach ($admins as $admin) {
                        self::getMailer()->notifyAdminSubscriptionNotCharged($admin, $company);
                    }
                } catch (Exception $e) {
                    self::getLogger()->log($e->getMessage());
                }
            }

            return true;
        } catch (Exception $e) {
            $curConnection->rollback();
            self::getLogger()->log($e->getMessage());

            return false;
        }
    }

    /**
     * Convert company to JSON - private function
     * @param Companies_Model_Company $company
     * @return array
     */
    private function _companyToArray(Companies_Model_Company $company) {
        $employeeService = new Companies_Model_EmployeeService();
        $employees = $employeeService->getAll($company->id);
        $employeeList = array();

        foreach ($employees as $employee) {
            $employeeList[] = $employeeService->toArray($employee);
        }

        return array(
            "id" => $company->id,
            "code" => $company->code_num,
            "name" => $company->name,
            "rating" => sprintf("%.2f", $company->rating ? $company->rating : 0),
            "reviews" => $company->reviews->count(),
            "employees" => $employeeList,
        );
    }

    /**
     * Convert company to JSON
     * @param Companies_Model_Company $company
     * @return string
     */
    public function companyToJson(Companies_Model_Company $company) {
        return json_encode($this->_companyToArray($company));
    }

    /**
     * Convert company list to JSON
     * @param $companies
     * @return string
     */
    public function companiesToJson($companies) {
        $companyList = array();

        foreach ($companies as $company) {
            $companyList[] = $this->_companyToArray($company);
        }

        return json_encode(array('companies' => $companyList));
    }

    /**
     * Import companies
     */
    public function import() {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm("company-import", "companies");

        if ($form->isValid($post)) {
            $curConnection = Doctrine_Manager::connection();

            try {
                $createCategories = $form->getValue("create_categories");
                $noEqualNames = $form->getValue("no_equal_names");
                $continueOnError = $form->getValue("continue_on_error");

                if (!$form->csv->receive()) {
                    throw new Exception("Failed to receive the file.");
                }

                $path = $form->csv->getFileName();
                $data = @fopen($path, "rt");

                if (!$data) {
                    throw new Exception("Unable to read the file.");
                }

                $companyCount = 0;
                $importedCount = 0;

                $curConnection->beginTransaction();
                $states = $this->getStatesArray();
                $reversedStates = $this->getReversedStatesArray();
                $uriFilter = new Main_Service_Filter_StringToUri();
                $companyTable = $this->getTable("company");
                $categoryTable = $this->getTable("category");

                while ($line = fgetcsv($data)) {
                    if (count($line) < 10) {
                        continue;
                    }

                    if (count($line) != 10) {
                        if ($continueOnError) {
                            continue;
                        }

                        throw new Exception(sprintf("invalid record #%d: not enough columns", $companyCount + 1));
                    }

                    list($name, $category, $address, $city, $state, $zip, $phone, $email, $website, $desc) = $line;

                    if (!$name || !$city || !$state) {
                        if ($continueOnError) {
                            continue;
                        }

                        throw new Exception(sprintf("invalid record #%d: no name, city or state", $companyCount + 1));
                    }

                    $state = strtoupper($state);

                    if (!isset($states[$state])) {
                        $state = ucfirst(strtolower($state));

                        if (isset($reversedStates[$state])) {
                            $state = $reversedStates[$state];
                        } else {
                            if ($continueOnError) {
                                continue;
                            }

                            throw new Exception(sprintf("invalid record #%d: invalid state", $companyCount + 1));
                        }
                    }

                    $companyCount++;

                    if ($noEqualNames) {
                        $company = $companyTable->getCompanyWithName($name);

                        if ($company) {
                            continue;
                        }
                    }

                    // check if company with the same URL already exists in DB
                    $uri = "/" . $uriFilter->filter($name) .
                        "/" . $uriFilter->filter($city) .
                        "/" . $uriFilter->filter($state);

                    if ($companyTable->existsByUri($uri)) {
                        continue;
                    }

                    $categoryId = null;

                    if ($category) {
                        $categoryUri = $uriFilter->filter($category);
                        $existingCategory = $categoryTable->getByUri($categoryUri);

                        if (!$existingCategory && $createCategories) {
                            $existingCategory = new Companies_Model_Category();
                            $existingCategory->name = $category;
                            $existingCategory->uri = $categoryUri;
                            $existingCategory->save();
                        }

                        $categoryId = $existingCategory->id;

                        // memory cleanup
                        $existingCategory->free(true);
                    }

                    // generate code
                    $code = null;
                    $attempt = 0;

                    while ($attempt < 1000) {
                        $code = sprintf("%05d", rand(1, 99999));

                        if (!$companyTable->codeExists($code)) {
                            break;
                        } else {
                            $code = null;
                        }

                        $attempt++;
                    }

                    if (!$code) {
                        if ($continueOnError) {
                            continue;
                        }

                        throw new Exception("Unable to generate a company code.");
                    }

                    if ($website && substr($website, 0, 7) != "http://" && substr($website, 0, 8) != "https://") {
                        $website = "http://" . $website;
                    }

                    try {
                        $company = new Companies_Model_Company();

                        $company->fromArray(array(
                            "code_num" => $code,
                            "category_id" => $categoryId,
                            "name" => $name,
                            "local_business" => true,
                            "show_address" => true,
                            "address" => $address,
                            "city" => $city,
                            "state" => $state,
                            "zip" => $zip,
                            "phone" => $phone,
                            "website" => $website,
                            "mail" => $email,
                            "about_us" => $desc,
                            "uri" => $uri,
                            "status" => Companies_Model_Company::STATUS_UNOWNED,
                        ));

                        $company->save();

                        // memory cleanup
                        $company->free(true);
                    } catch (Exception $e) {
                        if ($continueOnError) {
                            continue;
                        }

                        throw $e;
                    }

                    $importedCount++;
                }

                $curConnection->commit();

                Main_Service_Models::addProcessingInfo(
                    "Companies imported: " . $importedCount . " of " . $companyCount,
                    Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                );
            } catch (Exception $e) {
                $curConnection->rollback();

                self::getLogger()->log($e);
                self::addProcessingInfo(sprintf("Error importing companies (%s), please contact the administrator or try again.", $e->getMessage()));
            }
        } else {
            $form->populate($post);

            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME)) {
                self::addProcessingInfo("Error importing companies, please contact the administrator or try again.");
            } else {
                self::addProcessingInfo("Please fix the errors below.");
            }
        }
    }

    /**
     * Paginator by city name and category id
     * @param string $city
     * @param int | null $catId
     */
    public function getCompanyAndCategoryPaginator($page) {
        $query = $this->getTable("category")->getCategoriesWithCompaniesQuery();
        return $this->getPaginator($query, $page, 9);
    }

    /**
     * Verify listing of the company
     * @param Companies_Model_Company $company
     */
    public function verify(Companies_Model_Company $company) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm("verify-listing");

        if ($form->isValid($post)) {
            $curConnection = Doctrine_Manager::connection();
            $curConnection->beginTransaction();

            try {
                $next = new DateTime();
                $next = $next->add(new DateInterval("P1M"));

                $company->status = Companies_Model_Company::STATUS_TAKEN;
                $company->payment_date = $next->format("Y-m-d H:i:s");
                $company->plan_id = $this->getTable("plan")->getCheapestId();
                $company->save();

                $userService = new Users_Model_UserService();
                $user = $userService->create(array(
                    "name" => $form->getValue("name"),
                    "mail" => $form->getValue("email"),
                    "phone" => $form->getValue("phone"),
                    "password" => $form->getValue("password"),
                    "company_id" => $company->id,
                    "status" => Users_Model_User::STATUS_UNCONFIRMED,
                ));

                $userService->sendRegMail($user);
                $this->_notifyAdminOnVerifyListing($user);

                $curConnection->commit();
            } catch (Exception $e) {
                $curConnection->rollback();

                self::getLogger()->log($e);
                self::addProcessingInfo("Error creating user account, please contact the administrator or try again.");

                throw $e;
            }
        } else {
            $form->populate($post);

            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME)) {
                self::addProcessingInfo("Error creating user account, please contact the administrator or try again.");
            } else {
                self::addProcessingInfo("Please fix the errors below.");
            }

            throw new Exception();
        }
    }

    /**
     * Get available statuses
     * @param bool $admin
     * @return array
     */
    public static function getAvailableStatuses() {
        return array(
            Companies_Model_Company::STATUS_ACTIVE,
            Companies_Model_Company::STATUS_EXPIRED,
            Companies_Model_Company::STATUS_SUSPENDED,
            Companies_Model_Company::STATUS_UNOWNED,
            Companies_Model_Company::STATUS_TAKEN,
        );
    }

    /**
     * Get text by status
     * @param $status
     * @return string
     */
    public static function getTextByStatus($status) {
        $statuses = array(
            Companies_Model_Company::STATUS_ACTIVE => "Active",
            Companies_Model_Company::STATUS_EXPIRED => "Expired",
            Companies_Model_Company::STATUS_SUSPENDED => "Suspended",
            Companies_Model_Company::STATUS_NOT_ACTIVATED => "Not Activated",
            Companies_Model_Company::STATUS_UNOWNED => "Unowned",
            Companies_Model_Company::STATUS_TAKEN => "Verify Listing",
            Companies_Model_Company::STATUS_DELETED => "Deleted",
        );

        return $statuses[$status];
    }

    /**
     * Approve company listing verification
     * @param Companies_Model_Company $company
     */
    public function approve(Companies_Model_Company $company) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || !$this->isValidCsrfToken($post)) {
            self::addProcessingInfo("Error approving company, please contact the administrator or try again (post).");
            return;
        }

        try {
            $company->status = Companies_Model_Company::STATUS_ACTIVE;
            $company->save();

            Main_Service_Models::addProcessingInfo(
                "Company approved.",
                Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
            );
        } catch (Exception $e) {
            self::getLogger()->log($e->getMessage());
            self::addProcessingInfo("Error approving company, please contact the administrator or try again.");
        }
    }

    /**
     * Disapprove company with listing verification
     * @param Companies_Model_Company $company
     */
    public function disapprove(Companies_Model_Company $company) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || !$this->isValidCsrfToken($post)) {
            self::addProcessingInfo("Error disapproving company, please contact the administrator or try again.");
            return;
        }

        try {
            $company->status = Companies_Model_Company::STATUS_UNOWNED;
            $company->save();

            $users = $company->Users;

            foreach ($users as $user) {
                $user->status = Users_Model_User::STATUS_DELETED;
                $user->save();
            }

            Main_Service_Models::addProcessingInfo(
                "Company disapproved.",
                Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
            );
        } catch (Exception $e) {
            self::getLogger()->log($e->getMessage());
            self::addProcessingInfo("Error disapproving company, please contact the administrator or try again.");
        }
    }

    /**
     * Notify admin on company listing verification attempt
     * @param Users_Model_User $user
     */
    private function _notifyAdminOnVerifyListing(Users_Model_User $user) {
        $admins = Users_Model_UserTable::getInstance()->getAdmins();

        foreach ($admins as $admin) {
            $templateVars = array(
                "userName" => empty($admin->name) ? $admin->mail : $admin->name,
                "url" => $this->getView()->serverUrl() . $this->getView()->url("admin_user_edit", array("id" => $user->id)),
                "name" => $user->name,
                "email" => $user->mail,
                "companyUrl" => $this->getView()->serverUrl() . $this->getView()->url("admin_company_edit", array("id" => $user->company_id)),
                "companyName" => $user->Company->name,
            );

            $this->getView()->assign($templateVars);
            $mailBody = $this->getView()->render("admin/verify-listing.phtml");

            $mailConfig = array(
                "toMail" => $admin->mail,
                "body" => $mailBody,
                "fromText" => "Revudio",
                "subject" => "Listing Verification Attempt"
            );

            $mail = new Main_Mail_Smtp($mailConfig);
            $mail->send();
        }
    }

    /**
     * Convert free company to paid one
     * @param Companies_Model_Company $company
     */
    public function convertToPaid(Companies_Model_Company $company) {
        $company->subscription_id = null;
        $company->discount_id = null;
        $company->payment_date = null;
        $company->status = Companies_Model_Company::STATUS_NOT_ACTIVATED;
        $company->save();
    }

    /**
     * Get company page count
     * @param $categoryId
     */
    public function getCompanyPageCountByCategory($categoryId) {
        $companies = $this->getTable()->getCompanyCountByCategory($categoryId);
        $itemsPerPage = @self::getConfig()->pagination->companies->itemsPerPage;
        $itemsPerPage = (int) $itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();
        $pages = (int)($companies / $itemsPerPage);

        if ($companies % $itemsPerPage) {
            $pages++;
        }

        return $pages;
    }

    /**
     * Get company page count
     * @param $letter
     */
    public function getCompanyPageCountByLetter($letter) {
        $count = $this->getTable()->getCompanyCountByLetter($letter);
        $itemsPerPage = @self::getConfig()->pagination->companies->itemsSearchPerPage;
        $itemsPerPage = (int) $itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();
        $pages = (int)($count / $itemsPerPage);

        if ($count % $itemsPerPage) {
            $pages++;
        }

        return $pages;
    }

    /**
     * Get company page count
     * @param $categoryId
     * @param $state
     * @param $city
     */
    public function getCompanyPageCountByCategoryStateCity($categoryId, $state, $city) {
        $companies = $this->getTable()->getCategoryCompanyCount($categoryId, $state, $city);
        $itemsPerPage = @self::getConfig()->pagination->companies->itemsPerPage;
        $itemsPerPage = (int) $itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();
        $pages = (int)($companies / $itemsPerPage);

        if ($companies % $itemsPerPage) {
            $pages++;
        }

        return $pages;
    }

    /**
     * Get national company page count
     * @param $categoryId
     */
    public function getNationalCompanyPageCountByCategory($categoryId) {
        $companies = $this->getTable()->getNationalCompanyCount($categoryId);
        $itemsPerPage = @self::getConfig()->pagination->companies->itemsPerPage;
        $itemsPerPage = (int) $itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();
        $pages = (int)($companies / $itemsPerPage);

        if ($companies % $itemsPerPage) {
            $pages++;
        }

        return $pages;
    }
}
