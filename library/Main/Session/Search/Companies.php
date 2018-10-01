<?php
class Main_Session_Search_Companies extends Main_Session_Search_Abstract
{    
    protected $_serchVarName = 'companies';
    
    protected function _getSerchVarName()
    {
        return $this->_serchVarName;
    }
}