<?php

/**
 * Index controller
 */
class IndexController extends Main_Controller_Action {
    /**
     * Home page
     */
    public function indexAction()
    {
        $reviews = Companies_Model_ReviewTable::getInstance()->getLatest();
        $companies = Companies_Model_CompanyTable::getInstance()->getTop();

        $this->_helper->layout->setLayout('home');
        $this->view->title = 'Home';
        $this->view->reviews = $reviews;
        $this->view->companies = $companies;
    }
}
