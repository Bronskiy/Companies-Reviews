<?php
class Main_Session_Search_Business extends Main_Session_Search_Abstract
{    
    protected $_serchVarName = 'business';
    
    protected function _getSerchVarName()
    {
        return $this->_serchVarName;
    }
}