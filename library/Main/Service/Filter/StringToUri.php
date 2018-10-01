<?php

class Main_Service_Filter_StringToUri implements Zend_Filter_Interface
{
    protected $_filters = array();
    
    public function filter($value) 
    {
        $value = $this->_getFilter('Zend_Filter_StringTrim')->filter($value);
        $value = $this->_getFilter('Zend_Filter_StringToLower', 'UTF-8')->filter($value);
        $value = $this->_getFilter('Zend_Filter_PregReplace', array('match' => '/\W/', 'replace' => ' '))->filter($value);
        $value = $this->_getFilter('Zend_Filter_Word_SeparatorToDash', ' ')->filter($value);
        
        return $value;
    }
    
    /**
     * Filters lazy loading
     * 
     * @param string $name
     * @param mixed $params
     * @param bool $replace 
     */
    protected function _getFilter($name, $params = null)
    {
        if(!array_key_exists($name, $this->_filters)) {
            $this->_filters[$name] = new $name($params);
        }
        return $this->_filters[$name];
    }
}
