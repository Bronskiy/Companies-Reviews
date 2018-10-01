<?php
abstract class ZFEngine_Module_UserExtended_Controller_Index extends ZFEngine_Module_UserBase_Controller_Index
{

    /**
     * Сервисный слой пользователя
     * @return ZFEngine_Module_UserExtended_Model_UserService
     */
    protected function _getUserService()
    {
        return $this->_helper->serviceLayer->get('user', __METHOD__);
    }

    /**
     * Registration user
     *
     * @return void
     */
    public function registrationAction()
    {
        $this->view->setTitle(_('Регистрация'));
        
        $userService = $this->_getUserService();

        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $formResult = $userService->processRegistration($formData);
            if ($formResult == true) {
                $this->_helper->FlashMessenger(
                    $this->view->translate('Регистрация прошла успешно. На ваш email была отправлена ссылка для активации аккаунта.')
                );
                $this->_helper->redirector->gotoUrl('/');
            }
        }
        $this->view->form = $userService->formRegistration;
    }


    /**
     * Activation user
     *
     * @return void
     */
    public function activationAction()
    {
        if (! ($code = $this->getRequest()->getParam('code'))) {
            throw new Exception($this->view->translate('Не указан код активации'));
        }
        if (! ($email = $this->getRequest()->getParam('email'))) {
            throw new Exception($this->view->translate('Не указан адрес почты'));
        }

        $userService = $this->_getUserService();
        $userService->findUserByEmail($email);

        if ($userService->checkActivation($code)) {
            $this->_helper->FlashMessenger($this->view->translate('Учетная запись успешно активирована'));
            $this->_helper->redirector->gotoUrl('/');
        }
    }
    
    /**
     * Users list
     *
     * @return void
     */
    public function listAction()
    {
        $this->view->setTitle(_('Список пользователей'));

        $query = $this->_getUserService()->getMapper()->findAllAsQuery();
        $paginator =  $this->_helper->paginator->getPaginator($query);
        $this->view->paginator = $paginator;
        $this->view->users = $paginator->getCurrentItems();
    }

}