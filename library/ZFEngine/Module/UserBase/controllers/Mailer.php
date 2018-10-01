<?php

abstract class ZFEngine_Module_UserBase_Controller_Mailer extends Zend_Controller_Action
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
     * Сервисный слой мейлера
     * @return ZFEngine_Module_UserBase_Model_MailerService
     */
    protected function _getMailerService()
    {
        return $this->_helper->serviceLayer->get('mailer', __METHOD__);
    }

    /**
     *  Action New
     */
    public function newAction()
    {
        $title = $this->view->translate('Новая рассылка');
        $this->view->headTitle($title);
        $this->view->title = $title;

        $mailer = $this->_getMailerService();

        $user   = $this->_getUserService();

        $form = $mailer->formNew;

        //  вытаскиваем роли и сохраняем их в multiselect
        $form->getElement('user_role')->setMultiOptions(
            $user->getMapper()
                ->getAllRoles()
                ->toKeyValueArray('role', 'role')
        );

        if ($this->_request->isPost()) {
            $postData = $this->_request->getPost();

            $count = $mailer->processNew($postData, $user);
            if ($count != false) {
                $this->_helper->FlashMessenger('Письма отосланы [ Counts: ' . count($recipients) . ']');
                $this->_helper->redirector->gotoRoute(array(
                                    'module' => $this->_request->getModuleName(),
                                    'controller' => $this->_request->getControllerName(),
                                    'action' => 'new'),
                                  'default', true);
            } else {
                throw new Exception('Users not found');
            }
        } else {
//            $form->populate();
        }
        $this->view->form = $form;
    }

}