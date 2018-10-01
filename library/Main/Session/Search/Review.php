<?php

/**
 * Search review class
 */
class Main_Session_Search_Review extends Main_Session_Search_Abstract {
    protected $_serchVarName = 'review';

    /**
     * Get search variable name
     * @return string
     */
    protected function _getSerchVarName() {
        return $this->_serchVarName;
    }
}