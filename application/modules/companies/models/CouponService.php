<?php

require_once("Image/Image.php");

/**
 * Coupon service
 */
class Companies_Model_CouponService extends Main_Service_Models {
    /**
     * Creating / updating company coupon
     * @param Companies_Model_Company $company
     * @return boolean 
     */
    public function process(Companies_Model_Company $company) {
        $form = self::getStaticForm('company-coupon', 'companies');
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $curConnection = Doctrine_Manager::connection();

        try {
            if ($form->isValid($post)) {
                if (empty($_FILES) || $_FILES[$form->coupon->getName()]['error'] == UPLOAD_ERR_NO_FILE) {
                    return true;
                }

                $curConnection->beginTransaction();
                $coupon = $company->Coupons->get(0);

                if (!$coupon->exists()) {
                    $coupon = $this->_createCoupon($company);
                }

                if (!$this->_uploadCouponImage($coupon, $form)) {
                    $curConnection->rollback();
                    return false;
                }

                $curConnection->commit();
                self::addProcessingInfo("Coupon saved.", Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);
                
                return true;
            } else {
                if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME)) {
                    self::addProcessingInfo('Error saving coupon, please contact the administrator or try again.');
                } else {
                    self::addProcessingInfo('Please fix the errors below.');
                }
            }
        } catch(Exception $e) {
            $curConnection->rollback();
            self::getLogger()->log($e);
        }

        return false;
    }
    
    /**
     * Creating coupon
     * @param Companies_Model_Company $company
     * @return \Companies_Model_Coupon 
     */
    private function _createCoupon(Companies_Model_Company $company) {
        $coupon = new Companies_Model_Coupon();
        $coupon->company_id = $company->id;
        $coupon->image = 'tmp';
        $coupon->save();

        $this->_createCouponFolders($coupon);

        return $coupon;
    }
    
    /**
     * Creating folders for coupon
     * @param Companies_Model_Coupon $coupon 
     */
    private function _createCouponFolders(Companies_Model_Coupon $coupon) {
        $dirGenerator = new Main_Service_Dir_Generator_Coupon($coupon);
        $dirGenerator->generateFoldersByRule();
    }
    
    /**
     * Uploading coupon image
     * @param Companies_Model_Coupon $coupon
     * @param Companies_Form_CompanyCoupon $form
     */
    private function _uploadCouponImage(Companies_Model_Coupon $coupon, Companies_Form_CompanyCoupon $form) {
        $upload = $form->coupon;

        if (empty($_FILES) || $_FILES[$upload->getName()]['error'] == UPLOAD_ERR_NO_FILE) {
            return false;
        }
                
        // get dirs info for cur coupon
        $dirGenerator = new Main_Service_Dir_Generator_Coupon($coupon);
        $dirsInfo = $dirGenerator->getFoldersPathsFromRule();

        // get uploaded file path info array
        $pathinfo = pathinfo($upload->getFileName());
        $name = 'coupon.' . $pathinfo['extension'];

        // get new file full name (absolute name)
        $uploadedFileNewPath = $this->getView()->getPath($dirsInfo, 'coupon_images', $name);
        $upload->addFilter('Rename', array('target' => $uploadedFileNewPath, 'overwrite' => true));

        if (!$upload->receive()) {
            self::addProcessingInfo($upload->getMessages());
            return false;
        }

        $coupon->image = $name;
        $coupon->save();

        $this->_resize($uploadedFileNewPath);
        
        return true;
    }

    /**
     * Resize image
     * @param $image
     */
    private function _resize($image) {
        $img = new Image($image);

        if ($img->width > 620) {
            $img->resize(620, 0, false);
        }

        if ($img->height > 200) {
            $img->resize(0, 200, false);
        }

        $img->save($image);
    }
    
    /**
     * Deleting coupon
     * @param Companies_Model_Coupon $coupon 
     */
    public function deleteCoupon(Companies_Model_Coupon $coupon) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || ! $this->isValidCsrfToken($post)) {
            self::addProcessingInfo('Error deleting coupon, please contact the administrator or try again.');
            return false;
        }

        try {
            $coupon->delete();
            self::addProcessingInfo('Coupon deleted', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

            return true;
        } catch (Exception $e) {
            self::getLogger()->log($e);
        }

        self::addProcessingInfo('Error deleting coupon, please contact the administrator or try again.');

        return false;
    }

}