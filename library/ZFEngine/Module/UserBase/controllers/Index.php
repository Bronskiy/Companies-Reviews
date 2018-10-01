<?php

abstract class ZFEngine_Module_UserBase_Controller_Index extends Zend_Controller_Action
{

    /**
     * Сервисный слой пользователя
     * @return ZFEngine_Module_UserBase_Model_UserService
     */
    protected function _getUserService()
    {
        return $this->_helper->serviceLayer->get('user', __METHOD__);
    }

    /**
     * Login user
     *
     * @return void
     */
    public function loginAction()
    {
        $this->view->setTitle(_('Вход'));

        $forward = new Zend_Session_Namespace('forward');
        if ($this->_request->isPost()) {
            $postData = $this->_request->getPost();
            $formResult = $this->_getUserService()->processLogin($postData);
            if ($formResult == true) {
                if ($forward->redirect != null) {
                    $this->_redirect($forward->redirect);
                    $forward->redirect = null;
                } else {
                    if ($forward->accessDenied === true) {
                        unset($forward->accessDenied);
                        $this->_helper->redirector->gotoRoute(
                            $forward->url,
                            'default',
                            true
                         );
                    } else {
                        $this->_redirect('/');
                    }
                }
            } else {
                $this->_helper->FlashMessenger->addMessages($this->_getUserService()->getMessages(), true);
                $this->view->forgotPassword = true;
            }
        } else {
            $params = $this->_request->getParams();
            if (isset($params['accessDenied'])) {
                $forward->accessDenied = $params['accessDenied'];
                unset ($params['accessDenied']);
            }
            $forward->url = $params;
        }

        $this->view->form = $this->_getUserService()->formLogin;
    }


    /**
     * Activation user
     *
     * @return void
     */
    public function sendActivationAction()
    {
        if (! ($code = $this->getRequest()->getParam('code'))) {
            throw new Exception($this->view->translate('Не указан код проверки'));
        }
        if (! ($email = $this->getRequest()->getParam('email'))) {
            throw new Exception($this->view->translate('Не указан адрес почты'));
        }

        $userService = $this->_getUserService();
        $userService->findUserByEmail($email);

        if ($userService->resendActivation($code)) {
            $this->view->message = $this->view->translate('Инструкции по активации повторно отослана на Ваш e-mail. Пройдите в электронную почту и следуя инструкции активируйте свою учетную запись.');
        }
    }

    /**
     * User logout
     *
     * @return void
     */
    public function logoutAction()
    {
        $this->_getUserService()->logout();

        $this->_helper->redirector->gotoUrl('/');
    }

    /**
     * Restore password
     *
     * @return value
     */
    public function passwordRestoreAction()
    {
        $this->view->setTitle(_('Восстановление пароля'));

        $userService = $this->_getUserService();

        $email = $this->getRequest()->getParam('email');
        $validator = new Zend_Validate_EmailAddress();
        if ($email && $validator->isValid($email)) {
            $userService->formPasswordRestore
                    ->getElement('email')
                    ->setValue($email);
        }

        if ($this->_request->isPost()) {
            $postData = $this->_request->getPost();
            $formResult = $userService->processPasswordRestore($postData);
            if ($formResult == true) {
                $this->_helper->FlashMessenger(
                    $this->view->translate('На ваш e-mail отправлено сообщение с ссылкой для сброса пароля. Ссылка будет действительна два часа.')
                );
                $this->_helper->redirector->gotoUrl('/');
            } else {
                $this->_helper->FlashMessenger(
                    $this->view->translate('Произошла ошибка при востановлении пароля'),
                    ZFEngine_Controller_Action_Helper_FlashMessenger::ERROR,
                    true
                );
            }
        }
        $this->view->form = $userService->formPasswordRestore;
    }

    /**
     * Reset password
     *
     * @return void
     */
    public function passwordResetAction()
    {
        $this->view->setTitle(_('Сброс пароля'));

        if (! ($code = $this->getRequest()->getParam('code'))) {
            throw new Exception($this->view->translate('Не указан код сброса пароля.'));
        }
        if (! ($email = $this->getRequest()->getParam('email'))) {
            throw new Exception($this->view->translate('Не указан адрес почты.'));
        }

        $userService = $this->_getUserService();
        $userService->findUserByEmail($email);

        if ($userService->checkResetPasswordCode($code)) {

            if ($this->getRequest()->isPost()) {
                $postData = $this->_request->getPost();
                $formResult = $userService->processPasswordReset($postData);
                if ($formResult == true) {
                    $this->_helper->FlashMessenger(
                            $this->view->translate('Пароль успешно изменен.')
                    );
                    $this->_helper->redirector->gotoRoute(
                        array(
                            'module' => 'users',
                            'controller' => 'index',
                            'action' => 'edit'),
                        'default',
                        true
                     );
                }
            }
            $this->view->form = $userService->formPasswordReset;
        } else {
            throw new Exception($this->view->translate('Неправильный код сброса пароля.'));
        }
    }


    /**
     * Reset password
     *
     * @return void
     */
    public function passwordChangeAction()
    {
        $this->view->setTitle(_('Сменить пароль'));

        $userService = $this->_getUserService();
        $userService->findUserByAuth();

        if ($this->getRequest()->isPost()) {
            $postData = $this->_request->getPost();
            try {
                $formResult = $userService->processPasswordChange($postData);
                if ($formResult == true) {
                    $this->_helper->FlashMessenger(
                            $this->view->translate('Пароль успешно изменен.')
                    );
                    $this->_helper->redirector->gotoRoute(
                        array(
                            'module' => 'users',
                            'controller' => 'index',
                            'action' => 'edit'),
                        'default',
                        true
                     );
                }
            } catch (Exception $e) {
                $this->_helper->FlashMessenger(
                    $e->getMessage(), ZFEngine_Controller_Action_Helper_FlashMessenger::ERROR, true
                );
            }
        }
        $this->view->form = $userService->formPasswordChange;


    }


    /**
     * Edit user
     *
     * @return void
     */
    public function editAction()
    {
        $userService = $this->_getUserService();
        $userService->findUserByIdOrAuth($this->getRequest()->getParam('id'));
        if ($this->_helper->isAllowed($userService->getModel(), 'edit')) {
            if ($this->getRequest()->getParam('id')) {
                $this->view->setTitle(_('Редактирование профиля'));
            }

            $form = $userService->formEdit;
            if ($this->_request->isPost()) {
                $postData = $this->_request->getPost();
                $formResult = $userService->processEdit($postData);
            }

            $form->populate($userService->getModel()->toArray());

            $this->view->form = $form;

            if ($this->_request->isPost()) {
                if ($formResult == true) {
                    $this->_helper->FlashMessenger($this->view->translate('Изменения профиля сохранены.'));
                    if ($this->view->isAllowed('mvc:users:index', 'list')) {
                        $this->_helper->redirector->gotoRoute(
                            array(
                                'module' => 'users',
                                'controller' => 'index',
                                'action' => 'list'),
                            'default',
                            true
                         );
                    } else {
                        $this->_helper->redirector->gotoRoute(
                            array(
                                'module' => 'users',
                                'controller' => 'index',
                                'action' => 'edit'),
                            'default',
                            true
                         );
                    }
                }
            }
        } else {
            $this->_forward('denied', 'error', 'default');
        }
    }


    /**
     * Delete user
     *
     * @return void
     */
    public function deleteAction()
    {
        $userService = $this->_getUserService();
        $userService->findUserByIdOrAuth($this->getRequest()->getParam('id'));

        if ($this->_helper->isAllowed($userService->getModel(), 'delete')) {
            $title = sprintf($this->view->translate('Удаление пользователя "%s"?'), $userService->getModel()->email);
            $this->view->title = $title;
            $this->view->headTitle($title);

            if ($this->_request->isPost()) {
                $postData = $this->_request->getPost();
                $formResult = $userService->processDelete($postData);
                $this->_helper->redirector->gotoRoute(
                    array(
                        'module' => 'users',
                        'controller' => 'index',
                        'action' => 'list'
                    ),
                    'default',
                    true
                );
            }
            $this->view->form = $userService->formDelete;
        } else {
            $this->_forward('denied', 'error', 'default');
        }
    }

}
