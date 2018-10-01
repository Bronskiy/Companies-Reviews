<?php
class Users_Model_UserService extends Main_Service_Models
{
    /**
    * Array for salt string generating
    * @var array
    */
    private $_salt = array(
        '$','#','%','&','^','*','(',')',':','>','<','?','/',
        'F','S','G','H','Q','W','E','R','T','Y','U','P','O',
        'A','D'
    );
    
    /**
     * Method tries to register a user and send an activation email
     * @return boolean 
     */
    public function signup() {
        $form = $this->getForm('register');
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if ($form->isValid($post)) {
            $filter = new Main_Service_Filter_StringToUri();

            if ($form->getValue('local_business')) {
                $uri = '/' . $filter->filter($form->getValue('business_name')) .
                       '/' . $filter->filter($form->getValue('business_city')) .
                       '/' . $filter->filter($form->getValue('state'));
            } else {
                $uri = '/' . $filter->filter($form->getValue('business_name'));
            }
            
            $validator = new Zend_Validate_Db_NoRecordExists('companies', 'uri');
            
            if (!$validator->isValid($uri)) {
                $form->business_name->addError(
                    $form->getUnfilteredValue('business_name') . ' in ' .
                    $form->getUnfilteredValue('business_city') . ', ' .
                    $form->getUnfilteredValue('state') . ' already exists'
                );

                return false;
            }
            
            $curConnection = Doctrine_Manager::connection();

            try {
                $curConnection->beginTransaction();

                $regData = $this->_getRegData($form->getValues());
                $company = new Companies_Model_Company();

                $company->plan_id = $regData['plan_id'];
                $company->name = $regData['business_name'];
                $company->local_business = $form->getValue("local_business");
                $company->show_address = $form->getValue("show_address");
                $company->category_id = $regData['category_id'];
                $company->code_num = $regData['code_num'];
                $company->city = $regData['business_city'];
                $company->state = $regData['state'];
                $company->uri = $uri;
                $company->payment_date = null;
                
                $company->save();
                
                $companyService = new Companies_Model_CompanyService();
                $companyService->createCompanyFolders($company);
                
                unset(
                    $regData['business_name'],
                    $regData['business_city'],
                    $regData['code_num'],
                    $regData['state'],
                    $regData['plan_id'],
                    $regData['status']
                );
                
                $user = new Users_Model_User();                
                $user->fromArray($regData);
                $user->company_id = $company->id;
                $user->status = Users_Model_User::STATUS_UNCONFIRMED;
                $user->save();

                $this->sendRegMail($user);
                $this->_notifyAdmin($user);
                
                $curConnection->commit();

                return true;
            } catch (Exception $e) {
                $curConnection->rollback();

                self::getLogger()->log($e);
                self::addProcessingInfo('Error creating account, please contact the administrator or try again.');

                return false;
            }
        } else {
            $form->populate($post);

            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME)) {
                self::addProcessingInfo('Error creating account, please contact the administrator or try again.');
            } else {
                self::addProcessingInfo('Please fix the errors below.');
            }
        }
    }

    /**
     * Resend activation email
     * @return boolean
     */
    public function resend() {
        $form = $this->getForm('resend');
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if ($form->isValid($post)) {
            try {
                $session = new Zend_Session_Namespace();
                $userId = $session->activation_user_id;
                $user = null;

                if ($userId) {
                    $user = $this->getTable("user")->findOneById($userId);
                }

                if (!$user || $user->Role->name != Users_Model_Role::MEMBER_ROLE || $user->status != Users_Model_User::STATUS_UNCONFIRMED) {
                    throw new Exception("Invalid account");
                }

                $this->sendRegMail($user);

                Main_Service_Models::addProcessingInfo(
                    "Activation message successfully sent to {$user->mail}.",
                    Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                );

                return true;
            } catch (Exception $e) {
                self::getLogger()->log($e);
                self::addProcessingInfo('Error resending the activation message, please contact the administrator or try again.');

                return false;
            }
        } else {
            self::addProcessingInfo('Error resending the activation message, please contact the administrator or try again.');
        }

        return false;
    }
    
    /**
     * Activating user account
     * 
     * @param Users_Model_User $user
     * @return boolean 
     */
    public function activate(Users_Model_User $user) {
        if (!in_array($user->status, array(Users_Model_User::STATUS_UNCONFIRMED, Users_Model_User::STATUS_ACTIVE))) {
            return;
        }

        $user->status = Users_Model_User::STATUS_ACTIVE;
        $user->save();

        $session = new Zend_Session_Namespace();
        $session->activation_user_id = null;
        
        $this->getView()->user = $user;
        $this->getView()->isActivated = true;
    }
    
    /**
     * Method attempts to find user and send restore mail
     * 
     * @return boolean 
     */
    public function restorePassword()
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('restore-password');

        if ($form->isValid($post))
        {
            try
            {
                $user = $this->getTable('user')->findOneByMail($form->getValue('mail'));
                
                if ($user->status != Users_Model_User::STATUS_ACTIVE) {
                    $form->mail->addError('User not found');
                    return false;
                }

                $uniqueHash = md5(uniqid() . $user->mail);
                
                $this->_sendRestoreMail($user, $uniqueHash);
                
                $user->restore_hash = $uniqueHash;
                $user->save();

                return true;
                
            }
            catch (Exception $e)
            {
                self::getLogger()->log($e);
                self::addProcessingInfo('Error restoring password, please contact the administrator or try again.');

                return false;
            }
        }
        else
        {
            $form->populate($post);

            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME))
                self::addProcessingInfo('Error restoring password, please contact the administrator or try again.');
            else
                self::addProcessingInfo('Please fix the errors below.');

        }

        return false;
    }

    /**
     * Changing pass for user
     * @param Users_Model_User $user
     * @return boolean 
     */
    public function changePassword(Users_Model_User $user) {
        if ($user->status != Users_Model_User::STATUS_ACTIVE) {
            $this->_redirectNotFoundPage();
        }

        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm("change-password", "users");

        if ($form->isValid($post)) {
            $curConnection = Doctrine_Manager::connection();

            try {
                $curConnection->beginTransaction();
                
                $newPass = $form->getValue("password");
                $newSalt = $this->_getSalt();
                $hash = $this->_generatePassHash($newPass, $newSalt);

                $user->password_hash = $hash;
                $user->password_salt = $newSalt;
                $user->restore_hash = null;
                $user->save();
                
                $curConnection->commit();

                Main_Service_Models::addProcessingInfo("Password changed", Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

                return true;
            } catch (Exception $e) {
                $curConnection->rollback();
                self::getLogger()->log($e);
                self::addProcessingInfo("Error changing password, please contact the administrator or try again.");

                return false;
            }
        } else {
            $form->populate($post);

            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME)) {
                self::addProcessingInfo("Error changing password, please contact the administrator or try again.");
            } else {
                self::addProcessingInfo("Please fix the errors below.");
            }
        }

        return false;
    }

    /**
     * Updating user
     *
     * @param Users_Model_User $user
     * @return boolean
     */
    public function updateUser(Users_Model_User $user) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('edit-user', 'users');

        if ($form->isValid($post)) {
            $curConnection = Doctrine_Manager::connection();
            $curConnection->beginTransaction();

            try {
                $email = $form->getValue("mail");

                $validator = new Main_Validate_NoOtherSameRecords(
                    "users", "mail", array("id" => $user->id)
                );

                if (!$validator->isValid($email)) {
                    $form->mail->addError("User with this e-mail address already exists.");
                    return false;
                }

                $user->mail = $email;
                $user->phone = $form->getValue("phone");
                $user->name = $form->getValue("name");

                if ($form->getValue('password')) {
                    $newPass = $form->getValue('password');
                    $newSalt = $this->_getSalt();

                    $user->password_hash = $this->_generatePassHash($newPass, $newSalt);
                    $user->password_salt = $newSalt;
                    $user->restore_hash = null;
                }

                $user->save();
                $curConnection->commit();

                Main_Service_Models::addProcessingInfo('User updated.', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

                return true;
            } catch(Exception $e) {
                $curConnection->rollback();
                self::getLogger()->log($e);
                self::addProcessingInfo('Error updating user, please contact the administrator or try again.');

                return false;
            }
        } else {
            $form->populate($post);

            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME))
                self::addProcessingInfo('Error updating user, please contact the administrator or try again.');
            else
                self::addProcessingInfo('Please fix the errors below.');
        }

        return false;
    }
    
    /**
     * Deleting user
     * 
     * @param Users_Model_User $user 
     */
    public function deleteUser(Users_Model_User $user)
    {
        $user->delete();
        return true;
    }

    /**
     * Changing user in auth storage
     * 
     * @param Users_Model_User $user 
     */
    public function rewriteUserInStorage(Users_Model_User $user)
    {
        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            $auth->clearIdentity();
        }

        $oData = new stdClass();
        
        $oData->id     = $user->id;
        $oData->mail   = $user->mail;
        $oData->role   = $user->Role->name;
        $oData->status = $user->status;
        
        $auth->getStorage()->write($oData);
        Zend_Session::rememberMe(60*60*24*5); // 5 days
        
        return true;
    }
    
    /**
     * Start processing login attempt
     * @param array $postData
     * @return boolean
     */
    public function processLogin() {
        $form = $this->getForm("login");
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if ($form->isValid($post)) {
            return $this->authenticate($form->getValue("mail"), $form->getValue("password"));
        }

        if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME)) {
            self::addProcessingInfo("Login error, please contact the administrator or try again.");
        } else {
            self::addProcessingInfo("Please fix the errors below.");
        }

        $form->populate($post);

        return false;
    }
    
    /**
     * User authentication
     * @param string $mail
     * @param string $password
     */
    public function authenticate($mail, $password) {
        $authAdapter = $this->_getAuthAdapter($mail, $password);
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);

        if ($result->isValid()) {
            if (Zend_Auth::getInstance()->hasIdentity()) {
                Zend_Auth::getInstance()->clearIdentity();
            }
            
            $data = $authAdapter->getResultRowObject(array("id", "mail", "role_id", "status", "company_id"));
            
            if ($data->status != Users_Model_User::STATUS_ACTIVE) {
                self::addProcessingInfo("Invalid login or password.");
                return false;
            }
            
            $role = $this->getTable("role")->findOneById($data->role_id);
            $data->role = $role === false ? Users_Model_Role::DEFAULT_ROLE : $role->name;

            if ($data->role == Users_Model_Role::MEMBER_ROLE) {
                $company = null;

                if ($data->company_id) {
                    $company = Companies_Model_CompanyTable::getInstance()->findOneById($data->company_id);
                }

                if (!$company || !in_array($company->status, Companies_Model_Company::getStatusesAvailableForLogin())) {
                    self::addProcessingInfo("Invalid login or password.");
                    return false;
                }
            }

            unset($data->role_id);
            $auth->getStorage()->write($data);
            Zend_Session::rememberMe(60 * 60 * 24 * 5);

            return true;
        } else {
            self::addProcessingInfo("Invalid login or password.");
            return false;
        }
    }
    
     /**
     * Generate auth adapter based on doctrine
     * @param string $username
     * @param string $password
     * @return ZendX_Doctrine_Auth_Adapter 
     */
    protected function _getAuthAdapter($mail, $password) {
        $authAdapter = new ZendX_Doctrine_Auth_Adapter(Doctrine_Core::getConnectionByTableName("Users_Model_User"));
        
        $authAdapter->setTableName("Users_Model_User u")
            ->setIdentityColumn("u.mail")
            ->setCredentialColumn("u.password_hash")
            ->setCredentialTreatment("MD5(MD5(CONCAT(?, password_salt)))")
            ->setIdentity($mail)
            ->setCredential($password);

        return $authAdapter;
    }
    
    /**
     * Generating data for registration
     * @param array $formData 
     * return array 
     */
    protected function _getRegData(array $formData) {
        $salt = $this->_getSalt();
        $pass = $formData["password"];

        $addingData = array (
            "password_salt" => $salt,
            "password_hash" => $this->_generatePassHash($pass, $salt),
            "role_id" => $this->_getUserRoleIdByRoleName(Users_Model_Role::MEMBER_ROLE)
        );
        
        return array_merge($addingData, $formData);
    }

    /**
     * Get role id by role name
     * @param $roleName
     * @return int
     */
    protected function _getUserRoleIdByRoleName($roleName) {
        $role = $this->getTable("role", "users")->findOneByName($roleName);

        if ($role == FALSE) {
            $role = new Users_Model_Role();
            $role->name = $roleName;
            $role->description = "Role is created automatically after user registration";
            $role->save();
        }

        return $role->id; 
    }
    
    /**
     * Sending mail to user after successful registration
     * @param Users_Model_User $user 
     */
    public function sendRegMail(Users_Model_User $user) {
        $session = new Zend_Session_Namespace();
        $session->activation_user_id = $user->id;

        $templateVars = array(
            "userName" => !empty($user->name) ? $user->name : $user->mail,
            "activationUrl" => $this->getView()->serverUrl() . $this->url(
                "activate",
                array("hash" => md5($user->mail))
            )
        );

        $this->getView()->assign($templateVars);
        $urlBody = $this->getView()->render("registration.phtml");

        $mailConfig = array(
            "toMail" => $user->mail,
            "body" => $urlBody,
            "fromText" => "Revudio",
            "subject" => "Sign Up"
        );
        
        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }
    
    /**
     * Sending mail to the user with a unique link for password recovery
     * 
     * @param Users_Model_User $user
     */
    protected function _sendRestoreMail(Users_Model_User $user, $hash)
    {                    
        $templateVars = array(
            'userName' => !empty($user->name) ? $user->name : $user->mail,
            'restoreUrl' => $this->getView()->serverUrl() . $this->url(
                'change_password',
                array( 'hash' => $hash )
            )
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('restore.phtml');

        $mailConfig = array(
            'toMail'   => $user->mail,
            'body'     => $mailBody,
            'fromText' => 'Revudio',
            'subject'  => 'Password Recovery'
        );
        
        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }
    
    /**
     * Sending contact mail to the business owner
     * 
     * @param Users_Model_User $user
     * @param array $contactData 
     */
    public function sendContactMail(Users_Model_User $user, array $contactData)
    {       
        $templateVars = array(
            'userName'=>!empty($user->name) ? $user->name : $user->mail,
            'contactName' => $contactData['name'],
            'contactMail' => $contactData['mail'],
            'contactPhone' => $contactData['phone'],
            'contactMessage' => $contactData['message']);

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('mail-contact.phtml');

        $mailConfig = array(
            'toMail'   => $user->mail,
            'body'     => $mailBody,
            'fromText' => 'Revudio',
            'subject'  => 'Business contact mail'
        );
        
        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }

    
    /**
    * Generating salt for user
    * @return string 
    */
    protected function _getSalt() {
        shuffle($this->_salt);
        return implode("", $this->_salt);
    }
    
   
    /**
     * Generating password hash string
     * @param string $pass
     * @param string $salt
     * @return md5 hash string 
     */
    private function _generatePassHash($pass, $salt) {
        return md5(md5($pass . $salt));
    }
    
    /**
     * All users paginator
     * @param array $fieldsWhere
     * @param bool $skipAdmins
     * @return Zend_Paginator 
     */
    public function getUsersPaginator(array $fieldsWhere = NULL, $skipAdmins = false) {
        $pageNumber = (int)Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        $query = $this->getTable()->getQueryToFetchAll($fieldsWhere, $skipAdmins);
        $itemsPerPage = @self::getConfig()->pagination->users->itemsPerPage;
        $itemsPerPage = (int)$itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();

        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }
    
    /**
     * Validating and preparing users search data
     * 
     * @param Main_Session_Search_Users $container
     * @return boolean 
     */
    public function prepareSearch(Main_Session_Search_Users $container)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('search', 'users');

        if ($form->isValid($post))
        {
            $search = $form->getValue('search');
            
            try
            {
                $searchData = array('search' => $search);
                $container->saveSearchData($searchData);

                return true;
            }
            catch(Exception $e)
            {
                self::getLogger()->log($e);
                self::addProcessingInfo('Search error, please contact the administrator or try again.');

                return false;
            }
        }
        else
        {
            self::addProcessingInfo('Please fix the errors below.');
        }
        return false;
    }
    
    
    public function cancelAccount(Users_Model_User $user)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || ! $this->isValidCsrfToken($post)) {
            self::addProcessingInfo('Error cancelling account, please contact the administrator or try again.');
            return false;
        }
        
        $curConnection = Doctrine_Manager::connection();
        
        try {
            $curConnection->beginTransaction();
            
            $user->status = Users_Model_User::STATUS_CANCELLED;
            $user->save();

            $compService = new Companies_Model_CompanyService();
            
            if (!$compService->cancelSubscription($user->Company)) {
                self::addProcessingInfo('Error cancelling subscription, please contact the administrator or try again.');
                $curConnection->rollback();

                return false;
            }            

            $curConnection->commit();

            return true;
        } catch(Exception $e) {
            $curConnection->rollback();
        
            self::getLogger()->log($e->getMessage());
            self::addProcessingInfo('Error cancelling account, please contact the administrator or try again.');
        }

        return false;
    }

    /**
     * Sending contact mail to the business owner
     * @param Users_Model_User $user
     * @param Companies_Model_CompanyArticle $article
     * @param array $commentData
     */
    public function sendArticleCommentEmail(Users_Model_User $user, Companies_Model_CompanyArticle $article, array $commentData) {
        $templateVars = array(
            "userName" => !empty($user->name) ? $user->name : $user->mail,
            "name" => $commentData["name"],
            "email" => $commentData["email"],
            "comment" => $commentData["comment"],
            "article" => $article->title,
            "articleUrl" => $this->getView()->serverUrl() . $this->url("business_article", array("id" => $article->id)),
            "commentsUrl" => $this->getView()->serverUrl() . $this->url("business_article_comments", array("id" => $article->id)),
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render("mail-comment.phtml");

        $mailConfig = array(
            "toMail" => $user->mail,
            "body" => $mailBody,
            "fromText" => "Revudio",
            "subject" => "New Article Comment"
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }

    /**
     * Create user
     * @param $data
     * @return Users_Model_User
     */
    public function create($data) {
        $data = $this->_getRegData($data);

        $user = new Users_Model_User();
        $user->fromArray($data);
        $user->save();

        return $user;
    }

    /**
     * Notify admin on user creation
     * @param Users_Model_User $user
     */
    private function _notifyAdmin($user) {
        $admins = Users_Model_UserTable::getInstance()->getAdmins();

        foreach ($admins as $admin) {
            $templateVars = array(
                "userName" => empty($admin->name) ? $admin->mail : $admin->name,
                "url" => $this->getView()->serverUrl() . $this->getView()->url("admin_user_edit", array("id" => $user->id)),
                "name" => $user->name,
                "email" => $user->mail,
                "companyUrl" => $this->getView()->serverUrl() . $this->getView()->url("admin_company_edit", array("id" => $user->company_id)),
                "companyName" => $user->Company->name,
            );

            $this->getView()->assign($templateVars);
            $mailBody = $this->getView()->render("admin/signup.phtml");

            $mailConfig = array(
                "toMail" => $admin->mail,
                "body" => $mailBody,
                "fromText" => "Revudio",
                "subject" => "New Sign Up"
            );

            $mail = new Main_Mail_Smtp($mailConfig);
            $mail->send();
        }
    }
}
