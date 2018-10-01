<?php

/**
 * Discount service
 */
class Companies_Model_DiscountService extends Main_Service_Models {
    /**
     * Save discount
     * @return boolean 
     */
    public function save($discount) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('discount');
        $newValue = true;

        if ($discount) {
            $newValue = false;
            $form->removeElement("first_month_discount");
            $form->removeElement("monthly_discount");
        }
        
        if ($form->isValid($post)) {
            try {
                $values = $form->getValues();

                if (!$discount) {
                    $discount = new Companies_Model_Discount();
                }

                $discount->code = empty($values['code']) ? $this->generateCode() : mb_strtoupper($values['code'], 'UTF-8');
                $discount->plan_id = $values["plan_id"];

                if ($newValue) {
                    $discount->first_month_discount = $values["first_month_discount"];
                    $discount->monthly_discount = $values["monthly_discount"];
                }

                $discount->save();
                
                self::addProcessingInfo(
                    'Discount saved',
                    self::PROCESSING_INFO_SUCCESS_TYPE
                );

                return true;
            } catch (Exception $e) {
                self::getLogger()->log($e);
                self::addProcessingInfo('Error saving discount, please contact the administrator or try again.');
            }
        } else {
            $form->populate($post);
            self::addProcessingInfo('Please fix the errors below.');
        }

        return false;
    }

    /**
     * Delete discount
     * @param $discount
     */
    public function delete($discount) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || !$this->isValidCsrfToken($post)) {
            self::addProcessingInfo('Error deleting discount, please contact the administrator or try again.');
            return;
        }
        
        try {
            $discount->status = Companies_Model_Discount::STATUS_DELETED;
            $discount->save();

            self::addProcessingInfo('Discount deleted', self::PROCESSING_INFO_SUCCESS_TYPE);
        } catch (Exception $e) {
            self::addProcessingInfo('Error deleting discount, please contact the administrator or try again.');
            self::getLogger()->log($e->getMessage());
        }
    }
    
    /**
     * Generate trial code
     */
    public function generateCode()
    {
        static $codeSymbols = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
            'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
            'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        );
        
        shuffle($codeSymbols);

        return implode('', array_slice($codeSymbols, 0, 16));
    }
    
    /**
     * Pagination data
     * @return Zend_Paginator 
     */
    public function getDiscountsPaginator() {
        $pageNumber = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        $query = $this->getTable()->getQueryActiveDiscounts();
        $itemsPerPage = @self::getConfig()->pagination->itemsPerPage;
        $itemsPerPage = (int) $itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();

        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }
}