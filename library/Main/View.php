<?php

/**
 * Abstract master class for extension.
 */
require_once 'Zend/View/Abstract.php';

/**
 * Concrete class for handling view scripts.
 *
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2012-2013 http://www.domencom.com.ua 
 */
class Main_View extends Zend_View_Abstract
{
    /**
     * Is locked changing page head data
     * @var bool
     */
    private $_isPageHeadDataChangeLocked = false;
    
    /**
     * Data for current page (meta info, content)
     * 
     * @var Pages_Model_Page 
     */
    private $_page = null;
    
    /**
     * Constructor
     *
     * @param  array $config
     * @return void
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }
    
    /**
     * Includes the view script in a scope with only public $this variables.
     *
     * @param string The view script to execute.
     */
    protected function _run()
    {
        include func_get_arg(0);
    }
    
    /**
     * Locking page head data changing
     *  
     */
    public function setPageHeadDataLocked($islocked)
    {
        $this->_isPageHeadDataChangeLocked = (bool)$islocked;
    }
    
    /**
     * Sets Page Info for current page in system
     * 
     * @param Pages_Model_Page $page 
     */
    public function setPage(Pages_Model_Page $page)
    {
        $this->_page = $page; 
    }
    
    /**
     * Return Pages_Model_Page
     * 
     * @return Pages_Model_Page | null
     */
    public function getPage()
    {
        return $this->_page;
    }
    
    /**
     * Whether page data change was locked
     * 
     * @return type 
     */
    public function isPageHeadDataChangeLocked()
    {
        return $this->_isPageHeadDataChangeLocked;
    }

    /**
     * Build URL
     * @return string
     */
    public function url($route, $params=array())
    {
    	$router = Zend_Controller_Front::getInstance()->getRouter();
        $url = $router->assemble($params, $route);

        return $url;
    }
}