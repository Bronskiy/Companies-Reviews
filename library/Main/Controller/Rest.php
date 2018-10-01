<?php

/**
 * Rest controller base class
 */
class Main_Controller_Rest extends Zend_Rest_Controller {
    /**
     * Maintenance check
     */
    public function preDispatch() {
        if (file_exists(".maintenance")) {
            $this->forward("maintenance", "static", "default");
        }
    }

    /**
     * Controller initialization
     */
    public function init() {
        parent::init();
        $this->getResponse()->setHeader('Content-Type', 'application/json');
    }

    /**
     * List action
     */
    public function indexAction() {
        $this->_setErrorCode(501);
    }

    /**
     * Get
     */
    public function getAction() {
        $this->_setErrorCode(501);
    }

    /**
     * Create
     */
    public function postAction() {
        $this->_setErrorCode(501);
    }

    /**
     * Update
     */
    public function putAction() {
        $this->_setErrorCode(501);
    }

    /**
     * Delete
     */
    public function deleteAction() {
        $this->_setErrorCode(501);
    }

    /**
     * Get status
     */
    public function headAction() {
        $this->_setErrorCode(501);
    }

    /**
     * Set error code
     */
    protected function _setErrorCode($code) {
        $this->getResponse()->clearHeaders();
        $this->getResponse()->clearBody();
        $this->getResponse()->setHttpResponseCode($code);
    }

    /**
     * Get logger
     */
    protected function _getLogger() {
        return Zend_Registry::get(Main_Application_Resource_Logger::DEFAULT_REGISTRY_KEY);
    }
}
