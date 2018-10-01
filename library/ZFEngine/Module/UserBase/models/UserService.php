<?php

/**
 * @property ZFEngine_Module_UserBase_Form_User_Login $formLogin
 * @property ZFEngine_Module_UserBase_Form_User_PasswordRestore $formPasswordRestore
 * @property ZFEngine_Module_UserBase_Form_User_PasswordReset $formPasswordReset
 * @property ZFEngine_Module_UserBase_Form_User_PasswordChange $formPasswordChange
 * @property ZFEngine_Module_UserBase_Form_User_Edit $formEdit
 * @property ZFEngine_Module_UserBase_Form_User_Delete $formDelete
 */
class ZFEngine_Module_UserBase_Model_UserService extends ZFEngine_Model_Service_Database_Abstract
{

    /**
     * var ZFEngine_Module_UserBase_Model_MailerService
     */
    protected $_mailerService = null;

    /**
     *
     * @return ZFEngine_Module_UserBase_Model_MailerService
     */
    protected function _getMailerService()
    {
        if (!$this->_mailerService) {
            $this->_mailerService = new ZFEngine_Module_UserBase_Model_MailerService();
        }
        return $this->_mailerService;
    }

    /**
     *
     */
    public function init()
    {
        $this->_modelName = 'Users_Model_User';
    }

    /**
     * Обработка формы логина
     *
     * @param array $postData
     * @return boolean
     */
    public function processLogin($postData)
    {
        $form = $this->formLogin;

        if ($form->isValid($postData)) {
            if ($this->authenticate($form->getValue('username'), $form->getValue('password'))) {
                return true;
            } else {
                return false;
            }
        } else {
            $form->populate($postData);
            return false;
        }
    }


    /**
     * Обработка формы востановления пароля
     *
     * @param array $postData
     * @return boolean
     */
    public function processPasswordRestore($postData)
    {
        $form = $this->formPasswordRestore;

        if ($form->isValid($postData)) {
            $this->findUserByEmail($form->getValue('email'));

            if ($this->getModel()->registered != true) {
                throw new Exception($this->getView()->translate('Вы еще не зарегистрированы на сайте!'));
            }
            
            if ($this->getModel()->activated != true) {
                throw new Exception($this->getView()->translate('Вы еще не активировали свою учетную запись!'));
            }

            $this->getModel()->password_reset_code = $this->getModel()->generateRestoreCode();
            $this->getModel()->save();

            
            return $this->sendPasswordRestore();
        } else {
            $form->populate($postData);
            return false;
        }
    }

    /**
     * Send Password Restore mail
     * 
     * @param string $template
     * @return boolean
     */
    public function sendPasswordRestore($template ='module/users/index/mail/password-restore.phtml' )
    {
        $this->getView()->user = $this->getModel();
        $body = $this->getView()->render($template);
            try {
                // send link to password reset
                $this->_getMailerService()->sendmail(
                        $this->getModel()->email,
                        $this->getView()->translate('Восстановление пароля'),
                        $body
                );
                return true;
            } catch (Zend_Exception $e) {
                throw new Exception(
                    $this->getView()->translate('Произошла ошибка при отправке Вам на почту ссылки для активации аккаунта!') . '<br/>' .
                    $this->getView()->translate('Свяжитесь пожалуйста с администратором сайта.')
                );
            }
    }

    /**
     * Обработка формы востановления пароля
     *
     * @param array $postData
     * @return boolean
     */
    public function processPasswordReset($postData)
    {
        $form = $this->formPasswordReset;

        if ($form->isValid($postData)) {
            
            $this->getModel()->password = $form->getValue('password');
            $this->getModel()->save();
           
            $this->auth();
            
            return true;
        } else {
            $form->populate($postData);
            return false;
        }
    }


    /**
     * Обработка формы востановления пароля
     *
     * @param array $postData
     * @return boolean
     */
    public function processPasswordChange($postData)
    {
        $form = $this->formPasswordChange;

        if ($form->isValid($postData)) {
            $this->changePassword($postData['password'], $postData['old_password']);
            $this->getModel()->save();

            $this->auth();
            
            return true;
        } else {
            $form->populate($postData);
            return false;
        }
    }


    /**
     * Обработка формы редактирование профиля пользователя
     *
     * @param array $postData
     * @return boolean
     */
    public function processEdit($postData)
    {
        $form = $this->formEdit;

        if ($form->isValid($postData)) {
            $this->getModel()->fromArray($form->getValues());
            $this->getModel()->save();
            return true;
        } else {
            $form->populate($postData);
            return false;
        }
    }
    

    /**
     * Обработка формы удаления пользователя
     *
     * @param array $postData
     * @return boolean
     */
    public function processDelete($postData)
    {
        if (array_key_exists('submit_ok', $postData)) {
            $this->getModel()->delete();
            return true;
        }
        return false;
    }


    /**
     * Авторизация пользователя
     */
    public function auth()
    {
        $user = new stdClass();
        $data = $this->getModel()->toArray();
        unset($data['password_hash'], $data['password_salt']);
        foreach ($data as $key => $value) {
            $user->$key = $value;
        }
        Zend_Auth::getInstance()->getStorage()->write($user);

        // remember user for 2 weeks
        Zend_Session::rememberMe(60*60*24*14);
    }

    /**
     * Сброс авторизации
     */
    public function logout()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::forgetMe();
    }


    /**
     * Проверка кода сброса пароля
     *
     * @param string $code код сброса пароля
     * @return boolean
     */
    public function checkResetPasswordCode($code)
    {
        if (strlen($code) && $code == $this->getModel()->password_reset_code) {
            $timeDiff = time() - strtotime($this->getModel()->password_reset_code_created_at);
            if ($timeDiff > 7200) {
                throw new Exception($this->getView()->translate('Прошло больше 2х часов с момента генерации вашего кода для восстановления пароля.'));
            }
            return true;
        } else {
            return false;
        }
    }


    /**
     * Активация пользователя
     *
     * @param string $code код активации
     * @return boolean
     */
    public function checkActivation($code)
    {
        if (!$this->getModel()->activated && $code == $this->getModel()->activation_code) {
            $this->getModel()->activated = true;
            $this->getModel()->save();
            return true;
        } elseif ($this->getModel()->activated) {
            throw new Exception(sprintf($this->getView()->translate('Пользователь %s уже активирован.'), $this->getModel()->email));
        } else {
            throw new Exception($this->getView()->translate('Неправильный код активации.'));
        }

    }
    
    /**
     * Отправка ссылки активации
     *
     * @return boolean
     */
    public function sendActivation($template = 'module/users/index/mail/activation.phtml')
    {
        $this->getView()->user = $this->getModel();
        $body = $this->getView()->render($template);

        try {
            $this->_getMailerService()->sendmail(
                    $this->getModel()->email,
                    $this->getView()->translate('Активация аккаунта'),
                    $body
            );
            return true;
        } catch (Zend_Exception $e) {
            throw new Exception(
                $this->getView()->translate('Произошла ошибка при отправке Вам на почту ссылки для активации аккаунта!') . '<br/>' .
                $this->getView()->translate('Свяжитесь пожалуйста с администратором сайта.')
            );
        }
    }
    
    /**
     * Повторная отправка ссылки активации
     *
     * @param string $code код проверки
     * @return boolean
     */
    public function resendActivation($code)
    {
        if (!$this->getModel()->activated && $code == $this->getReactivationCode()) {
            return $this->sendActivation();
        } else {
            throw new Exception($this->getView()->translate('Неправильный код проверки.'));
        }
    }

    /**
     * Get user by auth
     *
     * @return Users_Model_User
     */
    public function getUserByAuth()
    {
        if (!$this->getUserAuthIdentity()) {
            throw new Exception($this->getView()->translate('Пользователь не найден.'));
        }
        return $this->getMapper()->find($this->getUserAuthIdentity()->id);
    }

    /**
     * Get user auth identity
     *
     * @return mixed|null
     */
    static function getUserAuthIdentity()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return null;
        }
        return $auth->getIdentity();
    }

    /**
     * find user by auth and set model object for service layer
     *
     * @return Users_Model_UserService
     */
    public function findUserByAuth()
    {
        $this->setModel($this->getUserByAuth());
        return $this;
    }

    /**
     * Get user by id
     *
     * @param integer $id
     * @return Users_Model_User
     */
    public function getUserById($id)
    {
        $user = $this->getMapper()->findOneById((int) $id);

        if ($user == false) {
            throw new Exception($this->getView()->translate('Такой пользователь не найден'));
        }

        return $user;
    }
    
    /**
     * find user by id and set model object for service layer
     *
     * @param integer $id
     * @return Users_Model_UserService
     */
    public function findUserById($id)
    {
        $this->setModel($this->getUserById($id));
        return $this;
    }
    

    /**
     * Get user by email
     *
     * @param string $email
     * @return User
     */
    public function getUserByEmail($email)
    {
        $user =$this->getMapper()->findOneByEmail($email);
        if (!$user) {
            throw new Exception($this->getView()->translate('Пользователь не найден.'));
        }

        return $user;
    }

    /**
     * find user by email and set model object for service layer
     *
     * @param integer $email
     * @return Users_Model_UserService
     */
    public function findUserByEmail($email)
    {
        $this->setModel($this->getUserByEmail($email));
        return $this;
    }


    /**
     * Get user by id or auth
     *
     * @param integer $id
     * @return Users_Model_User
     */
    public function getUserByIdOrAuth($id)
    {
        $user = $id ? $this->getUserById($id) : $this->getUserByAuth() ;

        if (!$user) {
            throw new Exception($this->getView()->translate('Пользователь не найден.'));
        }

        return $user;
    }


    /**
     * find user by id or auth
     *
     * @param integer $id
     * @return Users_Model_UserBaseService
     */
    public function findUserByIdOrAuth($id)
    {
        $this->setModel($this->getUserByIdOrAuth($id));
        return $this;
    }

    /**
     * Change password
     *
     * @param string $newPassword
     * @param string $oldPassword
     */
    public function changePassword($newPassword, $oldPassword)
    {
        if (!empty ($oldPassword)) {
            if (empty ($newPassword)) {
                throw new Exception($this->getView()->translate('Не введен новый пароль.'));
            }
            if (md5(md5($oldPassword) . $this->getModel()->password_salt) == $this->getModel()->password_hash) {
                $this->getModel()->setPassword($newPassword);
            } else {
                 throw new Exception($this->getView()->translate('Старый пароль введен неверно.'));
            }
        }
    }

    /**
     * Авторизация пользователя
     *
     * @param string $username
     * @param string $password
     */
    public function authenticate($username, $password)
    {
        $authAdapter = $this->_getAuthAdapter($username, $password);
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);

        if ($result->isValid()) {
            $data = $authAdapter->getResultRowObject(null, array('password_hash', 'password_salt'));

            if($data->activated != true && $data->registered == true) {
                $this->addMessage($this->getView()->translate('Ваш аккаунт зарегистрирован, но еще не активирован. Письмо с инструкцией по активации было выслано Вам на почту при регистрации.')
                        . ' <a href="'. $this->getView()->url(array(
                            'module' => 'users',
                            'controller' => 'index',
                            'action' => 'send-activation',
                            'email' => $data->email,
                            'code' => $this->getReactivationCode($data),
                        ), 'default', true) .'">'.$this->getView()->translate('Отправить еще раз?').'</a>', self::MESSAGE_ERROR);
                $auth->getStorage()->clear();
                return false;
            }

            $auth->getStorage()->write($data);

            // @todo to config
            // remember user for 2 weeks
            Zend_Session::rememberMe(60*60*24*14);
            return true;
        } else {
            $this->addMessage($this->getView()->translate('Ошибка авторизации. Проверьте правильность ввода логина и пароля.'), self::MESSAGE_ERROR);
            return false;
        }
    }

    /**
     * Запрос авторизации
     * @param string $username
     * @param string $password
     * @return ZendX_Doctrine_Auth_Adapter 
     */
    protected function _getAuthAdapter($username, $password)
    {
        $authAdapter = new ZendX_Doctrine_Auth_Adapter(Doctrine_Core::getConnectionByTableName($this->_modelName));
        $authAdapter->setTableName($this->_modelName . ' u')
                            ->setIdentityColumn('u.email = ? OR u.login')
                            ->setCredentialColumn('u.password_hash')
                            ->setCredentialTreatment('MD5(CONCAT(MD5(?), password_salt))')
                            // email, login
                            ->setIdentity(array($username, $username))
                            ->setCredential($password);
        return $authAdapter;
    }


    /**
     * Get code
     *
     * @param Users_Model_User
     * @return string
     */
    public function getReactivationCode($model = null)
    {
        if (is_null($model)) {
            $model = $this->getModel();
        }
        return md5($model->email.$model->id.$model->activation_code);
    }

}