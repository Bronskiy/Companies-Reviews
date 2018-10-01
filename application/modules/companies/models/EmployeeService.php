<?php

/**
 * Employee service
 */
class Companies_Model_EmployeeService extends Main_Service_Models {
    /**
     * Paginator for employees
     * @param int $companyId
     * @return Zend_Paginator 
     */
    public function getPaginator($companyId = null, $pageNumber = 1) {
        $query = $this->getTable()->getQueryToFetchAll($companyId);
        $perPage = self::getItemsPerPageDefault();
        
        return parent::getPaginator($query, $pageNumber, $perPage);
    }

    /**
     * Add employee
     * @param Companies_Model_Company $company
     * @param Companies_Model_Employee $employee
     * @return boolean
     */
    public function save(Companies_Model_Company $company, Companies_Model_Employee $employee = null) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm("employee");
        $newRecord = false;

        if (!$employee) {
            $newRecord = true;
        }

        if ($form->isValid($post)) {
            $curConnection = Doctrine_Manager::connection();

            try {
                $curConnection->beginTransaction();

                if (!$employee) {
                    $employee = new Companies_Model_Employee();
                }

                $facebook = $form->getValue("facebook_link");
                $twitter = $form->getValue("twitter_link");
                $linkedin = $form->getValue("linkedin_link");
                $google = $form->getValue("google_link");

                if ($facebook && substr($facebook, 0, 7) != "http://" && substr($facebook, 0, 8) != "https://") {
                    $facebook = "http://" . $facebook;
                }

                if ($twitter && substr($twitter, 0, 7) != "http://" && substr($twitter, 0, 8) != "https://") {
                    $twitter = "http://" . $twitter;
                }

                if ($linkedin && substr($linkedin, 0, 7) != "http://" && substr($linkedin, 0, 8) != "https://") {
                    $linkedin = "http://" . $linkedin;
                }

                if ($google && substr($google, 0, 7) != "http://" && substr($google, 0, 8) != "https://") {
                    $google = "http://" . $google;
                }

                $employee->fromArray(array(                        
                    "company_id" => $company->id,
                    "facebook_link" => $facebook,
                    "linkedin_link" => $linkedin,
                    "twitter_link" => $twitter,
                    "google_link" => $google,
                    "sorting_position" => $form->getValue("sorting_position"),
                    "public_profile" => $form->getValue("public_profile"),
                    "name" => $form->getValue("name"),
                    "position" => $form->getValue("position"),
                    "about" => $form->getValue("about"),
                    "year_started" => $form->getValue("year_started"),
                ));

                $employee->save();

                if ($form->photo->isUploaded()) {
                    $this->_uploadPhoto($employee, $form->photo);
                }

                $curConnection->commit();


                Main_Service_Models::addProcessingInfo(
                    $newRecord ? "Employee added" : "Employee saved",
                     Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                );

                return $employee->id;
            } catch (Exception $e) {
                $curConnection->rollback();
                self::getLogger()->log($e);
                self::addProcessingInfo("Error saving employee, please contact the administrator or try again.");
            }
        } else {
            $form->populate($post);
            self::addProcessingInfo("Please fix the errors below.");
        }

        return false;
    }

    /**
     * Upload photo
     * @param Companies_Model_Employee $employee
     * @param Zend_Form_Element $photo
     */
    private function _uploadPhoto(Companies_Model_Employee $employee, Zend_Form_Element $photo) {
        // create directories for the company (if not created yet)
        $companyService = new Companies_Model_CompanyService();
        $companyService->createCompanyFolders($employee->Company);

        // get dirs info for cur employee
        $dirGenerator = new Main_Service_Dir_Generator_Company($employee->Company);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
        $uploadedFileNewPath = $this->getView()->getPath($dirsInfo, "employees", $employee->id . ".jpg");

        $photo->addFilter("Rename", array(
            "target" => $uploadedFileNewPath,
            "overwrite" => true
        ));
        
        $photo->receive();

        $employee->photo = $employee->id . ".jpg";
        $employee->save();

        $this->_resizePhoto($employee);
    }

    /**
     * Resize photo
     * @param $employee
     */
    private function _resizePhoto($employee) {
        $dirGenerator = new Main_Service_Dir_Generator_Company($employee->Company);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
        $filePath = $this->getView()->getPath($dirsInfo, "employees", $employee->photo);

        $img = new Image($filePath);

        if ($img->width > $img->height) {
            $img->resize(0, 100, false);
        } else {
            $img->resize(100, 0, false);
        }

        $cropStartX = $img->width / 2 - 50;
        $cropStartY = $img->height / 2 - 50;

        $img->crop($cropStartX, $cropStartY, $cropStartX + 100, $cropStartY + 100);
        $img->save($filePath);
    }

    /**
     * Delete photo
     * @param Companies_Model_Employee $employee
     */
    public function deletePhoto(Companies_Model_Employee $employee) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || ! $this->isValidCsrfToken($post)) {
            self::addProcessingInfo('Error deleting photo, please contact the administrator or try again.');
            return false;
        }

        try {
            if ($employee->photo) {
                $dirGenerator = new Main_Service_Dir_Generator_Company($employee->Company);
                $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
                $filePath = $this->getView()->getPath($dirsInfo, "employees", $employee->photo);
                @unlink($filePath);
            }

            $employee->photo = null;
            $employee->save();

            self::addProcessingInfo('Photo deleted', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

            return true;
        } catch (Exception $e) {
            self::getLogger()->log($e);
        }

        self::addProcessingInfo('Error deleting photo, please contact the administrator or try again.');

        return false;
    }

    /**
     * Delete employee
     * @param Companies_Model_Employee $employee
     */
    public function delete(Companies_Model_Employee $employee) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || ! $this->isValidCsrfToken($post)) {
            self::addProcessingInfo('Error deleting employee, please contact the administrator or try again.');
            return false;
        }

        try {
            if ($employee->photo) {
                $dirGenerator = new Main_Service_Dir_Generator_Company($employee->Company);
                $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
                $filePath = $this->getView()->getPath($dirsInfo, "employees", $employee->photo);

                @unlink($filePath);
            }

            $employee->delete();

            self::addProcessingInfo('Employee deleted', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

            return true;
        } catch (Exception $e) {
            self::getLogger()->log($e);
        }

        self::addProcessingInfo('Error deleting photo, please contact the administrator or try again.');

        return false;
    }

    /**
     * Get all company employees
     * @param null $companyId
     * @return mixed
     */
    public function getAll($companyId = null) {
        return $this->getTable()->getQueryToFetchAll($companyId)->execute();
    }

    /**
     * Convert employee to array
     * @param Companies_Model_Employee $employee
     */
    public function toArray($employee) {
        if ($employee->photo) {
            $dirGenerator = new Main_Service_Dir_Generator_Company($employee->Company);
            $dirsInfo = $dirGenerator->getFoldersPathsFromRule(false);
            $filePath = $this->getView()->getPath($dirsInfo, "employees", $employee->photo);
            $photo = $filePath;
        } else {
            $photo = "/images/employee.jpg";
        }

        $domain = $this->getConfig()->domain;
        $photo = "http://" . $domain . $photo;

        return array(
            "id" => $employee->id,
            "name" => $employee->name,
            "position" => $employee->position,
            "sorting_position" => $employee->sorting_position,
            "photo" => $photo,
        );
    }
}