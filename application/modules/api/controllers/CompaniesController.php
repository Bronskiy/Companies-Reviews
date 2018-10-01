<?php

/**
 * Companies controller
 */
class Api_CompaniesController extends Main_Controller_Rest {
    /**
     * Get a list of companies
     */
    public function indexAction() {
        try {
            $code = $this->getRequest()->getParam('code', null);

            $service = new Companies_Model_CompanyService();
            $companies = $service->getCompanyList($code);

            if ($code != null && $companies->count() == 0) {
                $this->_setErrorCode(404);
                return;
            }

            $this->getResponse()->setBody($service->companiesToJson($companies));
            $this->getResponse()->setHttpResponseCode(200);
        } catch (Exception $e) {
            $this->_getLogger()->log($e);
            $this->_setErrorCode(500);
        }
    }

    /**
     * Get company
     */
    public function getAction() {
        try {
            $id = $this->getRequest()->getParam('id');
            $service = new Companies_Model_CompanyService();
            $company = $service->getCompany($id);

            if (!$company) {
                $this->_setErrorCode(404);
                return;
            }

            $this->getResponse()->setBody($service->companyToJson($company));
            $this->getResponse()->setHttpResponseCode(200);
        } catch (Exception $e) {
            $this->_getLogger()->log($e);
            $this->_setErrorCode(500);
        }
    }
}
