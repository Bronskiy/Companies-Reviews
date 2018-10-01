<?php

abstract class ZFEngine_Module_UserExtended_Controller_Mailer extends ZFEngine_Module_UserBase_Controller_Mailer
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
     * Сервисный слой мейлера
     * @return ZFEngine_Module_UserExtended_Model_MailerService
     */
    protected function _getMailerService()
    {
        return $this->_helper->serviceLayer->get('mailer', __METHOD__);
    }

}