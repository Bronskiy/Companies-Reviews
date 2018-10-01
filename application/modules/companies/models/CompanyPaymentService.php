<?php

/**
 * Company payment service
 * Class Companies_Model_CompanyPaymentService
 */
class Companies_Model_CompanyPaymentService extends Main_Service_Models {
    /**
     * Paginator for company payments
     * @param int    $companyId
     * @param string $order
     * @return Zend_Paginator 
     */
    public function getPaginator($companyId = null, array $fields = null, $createdAt = null) {
        $pageNumber = (int)Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        $query      = $this->getTable()->getQueryToFetchAll($companyId, $fields, $createdAt);
        $perPage    = self::getItemsPerPageDefault();
        
        return parent::getPaginator($query, $pageNumber, $perPage);
    }

    /**
     * Get all payment months
     */
    public function getAllMonths() {
        $paymentDate = $this->getTable()->getFirstPaymentDate();

        if (!$paymentDate) {
            $paymentDate = date("Y-m-01");
        }

        $start = new DateTime($paymentDate);
        $start->setTime(0, 0, 0);
        $current = new DateTime(date("Y-m-01"));
        $current->setTime(0, 0, 0);
        $months = array();

        while (true) {
            $months[] = array(
                "value" => $current->format("Y-m"),
                "text" => $current->format("m/Y")
            );

            $current->sub(new DateInterval("P1M"));

            if ($current < $start) {
                break;
            }
        }

        return $months;
    }

    /**
     * Get total payment amount
     * @param $month
     * @return mixed
     */
    public function getTotal($month = null) {
        $total = $this->getTable()->getTotal($month);

        if (!$total) {
            $total = 0;
        }

        return $total;
    }
}