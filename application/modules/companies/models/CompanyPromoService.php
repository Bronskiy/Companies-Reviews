<?php

/**
 * Company promo service
 */
class Companies_Model_CompanyPromoService extends Main_Service_Models
{
    /**
     * Save company promo
     * @param Companies_Model_CompanyPromo $promo
     * @return bool
     */
    public function processCompanyPromo(Companies_Model_CompanyPromo $promo)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('company-promo');

        try {
            if ($form->isValid($post)) {
                $promo->fromArray($form->getValues());
                $promo->save();
                self::addProcessingInfo('Promo saved', self::PROCESSING_INFO_SUCCESS_TYPE);

                return true;
            } else {
                $form->populate($post);
                self::addProcessingInfo('Please fix the errors below.');
            }
        } catch(Exception $e) {
            self::getLogger()->log($e);
            self::addProcessingInfo('Error saving promo, please contact the administrator or try again.');
        }

        return false;
    }

    /**
     * Create promo
     * @param Companies_Model_Company $company
     * @return bool|int
     */
    public function createPromo(Companies_Model_Company $company)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('company-promo');

        try {
            if ($form->isValid($post)) {
                $promo = new Companies_Model_CompanyPromo();
                $promo->fromArray($form->getValues());
                $promo->company_id = $company->id;
                $promo->save();

                self::addProcessingInfo('Promo created', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

                return $promo->id;
            } else {
                $form->populate($post);
                self::addProcessingInfo('Please fix the errors below.');
            }
        } catch(Exception $e) {
            self::getLogger()->log($e);
            self::addProcessingInfo('Error creating promo, please contact the administrator or try again.');
        }

        return false;
    }
    
    /**
     * Paginator for company promos
     * 
     * @param int $companyId
     * @return Zend_Paginator 
     */
    public function getCompanyPromosPaginator($companyId)
    {
        $pageNumber = (int)Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        $query = $this->getTable()->getQueryToFetchCompanyPromos($companyId);
        $itemsPerPage = self::getItemsPerPageDefault();
        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }
}