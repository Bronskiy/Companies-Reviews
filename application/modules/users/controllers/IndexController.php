<?php

class Users_IndexController extends Main_Controller_Action
{
    /**
     * Login action
     */
    public function loginAction() {
        if ($this->_request->isPost() && $this->getService('user')->processLogin()) {
            $user = Main_Service_Models::getAuthUser();
            $this->_redirectAuthUserByRole($user->Role->name);

            return;
        }

        $this->view->title = 'Login';
        $this->view->loginForm = $this->getService('user')->getForm('login');
    }
    
    /**
     * Signup action
     */
    public function signupAction() {
        $this->view->showSuccess = false;
        $resendForm = $this->getService("user")->getForm("resend");

        if ($this->_request->isPost()) {
            if (isset($_POST["plan_id"]) && $this->getService('user')->signup()) {
                $this->view->showSuccess = true;
            } else if (isset($_POST["resend"]) && $this->getService("user")->resend()) {
                $this->view->showSuccess = true;
            }
        }

        $session = new Zend_Session_Namespace();

        if ($session->activation_user_id) {
            $user = $this->getService("user")->getTable("user")->findOneById($session->activation_user_id);

            if ($user && $user->Role->name == Users_Model_Role::MEMBER_ROLE && $user->status == Users_Model_User::STATUS_UNCONFIRMED) {
                $this->view->showSuccess = true;
            }
        }

        $signupForm = $this->getService('user')->getForm('register');
        $plan = $this->_request->getParam("plan", null);

        if ($plan) {
            $signupForm->getElement("plan_id")->setValue($plan);
        }

        $this->view->title = 'Sign Up';
        $this->view->regForm = $signupForm;
        $this->view->resendForm = $resendForm;
    }

    /**
     * Resend activation email
     */
    public function signupResendAction() {
        if ($this->_request->isPost() && $this->getService('user')->signupResend()) {
            $this->view->success = true;
        }

        $form = $this->getService('user')->getForm('register');
        $plan = $this->_request->getParam("plan", null);

        if ($plan) {
            $form->getElement("plan_id")->setValue($plan);
        }

        $this->view->title = 'Sign Up';
        $this->view->regForm = $form;
    }
    
    /**
     * Trying to activate user
     */
    public function activateAction() {
        $hash = $this->_request->getParam('hash');
        $user = $this->getService("user")->getTable("user")->findOneByHash($hash);

        if (!$user) {
            $this->_redirectNotFoundPage();
        }

        $this->getService("user")->activate($user);
        $this->getService('user')->rewriteUserInStorage($user);
        
        if ($user->Role->name == Users_Model_Role::MEMBER_ROLE) {
            if ($user->Company->status == Companies_Model_Company::STATUS_TAKEN) {
                $this->redirect($this->url("business_reviews"), array("exit" => true));
            } else {
                $this->redirect($this->url("business_billing"), array("exit" => true));
            }
        } else {
            $this->_redirectAuthUserByRole($user->Role->name);
        }
    }
    
    /**
     * The action is triggered when a user forgets his password
     */
    public function restoreAction()
    {
        if ($this->_request->isPost())
            $this->view->result = $this->getService('user')->restorePassword();

        $this->view->title = 'Restore Password';
        $this->view->restoreForm = $this->getService('user')->getForm('restore-password');
    }
    
    /**
     * Changing user password coming from mail link
     */
    public function changePasswordAction()
    {
        $hash = $this->_request->getParam('hash');
        $user = $this->getService('user')->getTable('user')->findOneByRestoreHash($hash);

        if (!$user)
            $this->_redirectNotFoundPage();

        if ($this->_request->isPost() && $this->getService('user')->changePassword($user))
        {
            $this->getService('user')->rewriteUserInStorage($user);
            $this->_redirectAuthUserByRole($user->Role->name);
        }

        $this->view->title = 'Change Password';
        $this->view->changePassForm = Main_Service_Models::getStaticForm('change-password');
    }

    /**
     * Logout user
     */
    public function logoutAction()
    {
        if(Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Auth::getInstance()->clearIdentity();
        }
        $this->redirect('/', array( 'exit' => true ));
    }
    
    /**
     * Redirecting user after authentication
     * @staticvar array $actions
     * @param string $roleName 
     */
    protected function _redirectAuthUserByRole($roleName) {
        $urls = array(
            Users_Model_Role::ADMIN_ROLE => $this->url("admin"),
            Users_Model_Role::SUBADMIN_ROLE => $this->url("admin"),
            Users_Model_Role::MEMBER_ROLE => $this->url("business_reviews"),
            Users_Model_Role::DEFAULT_ROLE => "/"
        );
        
        $url = $urls[Users_Model_Role::DEFAULT_ROLE];
        
        if (array_key_exists($roleName, $urls)) {
            $url = $urls[$roleName];
        }

        $this->redirect($url, array("exit" => true));
    }
}

