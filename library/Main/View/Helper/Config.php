<?php

/**
 * Helper to get data from config
 */
class Zend_View_Helper_Config extends Zend_View_Helper_Abstract {
    protected $_config = null;

    /**
     * Get config
     * @return array
     */
    public function config() {
        return Main_Service_Models::getConfig();
    }

    /**
     * Get domain
     */
    public function getDomain() {
        return @$this->_config->domain;
    }
}