<?php

/**
 * Reviews controller
 */
class Api_ReviewsController extends Main_Controller_Rest {
    /**
     * Create review
     */
    public function postAction() {
        try {
            $service = new Companies_Model_CompanyService();
            $company = $service->getCompany($this->_request->getParam('company_id', null));

            if (!$company) {
                $this->_setErrorCode(404);
                return;
            }

            $service = new Companies_Model_ReviewService();
            $review = $service->createReview($company);

            if ($review == null) {
                $this->_setErrorCode(400);
                return;
            }

            $this->getResponse()->setBody($service->reviewToJson($review));
            $this->getResponse()->setHttpResponseCode(200);
        } catch (Exception $e) {
            $this->_getLogger()->log($e);
            $this->_setErrorCode(500);
        }
    }

    /**
     * Change review
     */
    public function putAction() {
        try {
            $service = new Companies_Model_ReviewService();
            $review = $service->getUnconfirmedReview($this->_request->getParam('id', null));

            if (!$review) {
                $this->_setErrorCode(404);
                return;
            }

            $review = $service->updateReview($review);

            if ($review == null) {
                $this->_setErrorCode(400);
                return;
            }

            $this->getResponse()->setBody($service->reviewToJson($review));
            $this->getResponse()->setHttpResponseCode(200);
        } catch (Exception $e) {
            $this->_getLogger()->log($e);
            $this->_setErrorCode(500);
        }
    }
}
