<?php
/**
 *  Plugin monitoring users in Auth storage
 * 
 */
class Main_Controller_Plugin_AuthWatcher extends Zend_Controller_Plugin_Abstract
{
    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        try {
            $authUser = Main_Service_Models::getAuthUser();
            $auth = Zend_Auth::getInstance();

            if($auth->hasIdentity() 
               && ($authUser === FALSE 
                   || $authUser->status == Users_Model_User::STATUS_DELETED
                   || $authUser->status == Users_Model_User::STATUS_CANCELLED)) 
            {
                $auth->clearIdentity();
                $this->_redirectToLoginPage();
            }
        }catch(Exception $e) {
            Main_Service_Models::getLogger()->log($e->getMessage());
            // invalid data in storage
            if(Zend_Auth::getInstance()->hasIdentity()) {
                Zend_Auth::getInstance()->clearIdentity();
            }
        }
        
    }
    
    private function _redirectToLoginPage()
    {
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setCode(303)
                   ->setExit(true)
                   ->gotoRoute(array(), 'login');
    }
}