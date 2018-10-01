<?php
class Main_Session_Search_Users extends Main_Session_Search_Abstract
{    
    protected $_serchVarName = 'users';
    
    protected function _getSerchVarName()
    {
        return $this->_serchVarName;
    }
}