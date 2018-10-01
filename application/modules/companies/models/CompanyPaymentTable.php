<?php

/**
 * Companies_Model_CompanyPaymentTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Companies_Model_CompanyPaymentTable extends Doctrine_Table
{
    private $_alias = 'cp';
    
    /**
     * Returns an instance of this class.
     * @return object Companies_Model_CompanyPaymentTable
     */
    public static function getInstance() {
        return Doctrine_Core::getTable('Companies_Model_CompanyPayment');
    }

    /**
     * Get query to fetch all payments
     * @param null $companyId
     * @param array $fields
     * @return Doctrine_Query
     */
    public function getQueryToFetchAll($companyId = null, array $fields = null, $createdAt = null) {
        $query = $this->createQuery($this->_alias);
        
        if ($companyId) {
            $query->where('cp.company_id = ?', (int)$companyId);
        }

        if ($createdAt) {
            $query->where("DATE_FORMAT(cp.created_at, '%Y-%m') = ?", $createdAt);
        }
        
        $this->_orderBy($query, $fields);
        
        return $query;
    }
    
    /**
     * Order by
     * @param type $query
     * @param array $fields 
     */
    private function _orderBy(Doctrine_Query_Abstract &$query, array &$fields = null) {
        $allowedFields = array('id', 'created_at', 'amount');
        $allowedOrder  = array('DESC', 'ASC');
        $orderField    = 'id';
        $orderWay      = 'DESC';
        
        if(!empty($fields['order-field']) && is_string($fields['order-field'])) {
            $tmpOrderField = @strtolower($fields['order-field']);
            $orderField = in_array($tmpOrderField, $allowedFields) ? $tmpOrderField 
                                                                   : $orderField;
        }
        
        if(!empty($fields['order-way']) && is_string($fields['order-way'])) {
            $tmpOrderWay =  @strtoupper($fields['order-way']);
            $orderWay = in_array($tmpOrderWay, $allowedOrder) ? $tmpOrderWay 
                                                              : $orderWay;
        }

        $orderBy = $this->_alias . '.' . $orderField . ' ' . $orderWay;

        $query->orderBy($orderBy);
    }

    /**
     * Get first payment date
     */
    public function getFirstPaymentDate() {
        $query = $this->createQuery($this->_alias)
            ->select("DATE_FORMAT(cp.created_at, '%Y-%m') AS payment_date")
            ->orderBy("id ASC")
            ->limit(1);

        $res = $query->execute(array(), Doctrine_Core::HYDRATE_SCALAR);

        if (count($res) > 0) {
            $res = $res[0]["cp_payment_date"];
        }

        return $res;
    }

    /**
     * Get total payments amount
     * @return Doctrine_Collection
     */
    public function getTotal($month = null) {
        $query = $this->createQuery($this->_alias)
            ->select("SUM(cp.amount) AS total_amount");

        if ($month) {
            $query->where("DATE_FORMAT(cp.created_at, '%Y-%m') = ?", $month);
        }

        $res = $query->execute(array(), Doctrine_Core::HYDRATE_SCALAR);

        if (count($res) > 0) {
            $res = $res[0]["cp_total_amount"];
        }

        return $res;
    }
}