<?php
require_once 'common.php';

class Reconcile extends Task
{
    const DAYS_AFTER_CHANGE = 30;

    protected $_daysAfterChange;
    
    /**
     * locked file name 
     */
    const LOCK_FILE_NAME = 'locked_reconcile_file.tmp';
    
    public function __construct() 
    {
        parent::__construct();
        
        $config = Main_Service_Models::getConfig();

        $days = @$config->reconcile->daysAfterChange;

        $this->_daysAfterChange = $days ? (int)$days : self::DAYS_AFTER_CHANGE;
    }
    
    public function exec() 
    {
        parent::exec();
        echo $this->_publishExpiredReviews();
    }
    
    /**
     * Publishing reviews with status = reconciliation and expired date
     *  
     */
    protected function _publishExpiredReviews()
    {
        $data = array('status' => 'published', 'reconcile_hash' => NULL);
        
        $where = array();
        
        $where[] = "status = 'reconciliation'";
        $where[] = sprintf("DATEDIFF(NOW(), created_at) >= %d", $this->_daysAfterChange);
        
        $this->_db->update('reviews', $data, $where);
    }
    
    /**
     * Return full locking file name path
     * 
     * @return string 
     */
    protected function _getLockedFileName()
    {
        return realpath(APPLICATION_PATH . '/../tmp') . '/' . self::LOCK_FILE_NAME;
    }
}

$reconcile = new Reconcile();
$reconcile->exec();