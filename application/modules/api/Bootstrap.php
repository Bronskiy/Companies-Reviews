<?php

/**
 * Bootstrap class for API module
 */
class Api_Bootstrap extends Zend_Application_Module_Bootstrap
{
    /**
     * Init plugins
     */
    public function _initPlugins()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Main_Controller_Plugin_DisableView(), 100000);
    }
}