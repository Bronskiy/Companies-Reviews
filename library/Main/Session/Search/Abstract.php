<?php
abstract class Main_Session_Search_Abstract extends Zend_Session_Namespace
{
    const SESS_NAMESPACE = 'SearchResult';
    
    /**
     * Creating  Zend_Session_Namespace object and locking it
     * 
     * No one can change it data, only through public methods
     * 
     */
    public function __construct() {
        parent::__construct(self::SESS_NAMESPACE);
        $this->lock();
    }
    
    /**
     * Saving search result data
     * 
     * @param mixed $val 
     */
    public function saveSearchData($val)
    {
        if($this->isLocked()) {
            $this->unlock();
        }
        $this->{$this->_getSerchVarName()} = $val;
        $this->lock();
    }
    
    /**
     * Clearing search result data
     *   
     */
    public function clearSearchData()
    {
        if($this->isLocked()) {
            $this->unlock();
        }
        if(isset($this->{$this->_getSerchVarName()})) {
            unset($this->{$this->_getSerchVarName()});
        }
        $this->lock();
    }
    
    /**
     * 
     */
    public function isSetSearchData()
    {
        return isset($this->{$this->_getSerchVarName()});
    }
    
    /**
     * 
     */
    public function getSearchData()
    {
        return $this->{$this->_getSerchVarName()};
    }
    
    abstract protected function _getSerchVarName();
}