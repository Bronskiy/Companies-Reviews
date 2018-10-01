<?php
class Zend_View_Helper_StringToUri extends Zend_View_Helper_Abstract
{
    private $_filter;
    
    public function __construct() 
    {
        $this->_filter = new Main_Service_Filter_StringToUri();
    }
    
    public function stringToUri($value)
    {
        return $this->_filter->filter($value);
    }
}