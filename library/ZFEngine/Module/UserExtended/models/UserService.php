<?php

/**
 * @property ZFEngine_Module_UserExtended_Form_User_Registration $formRegistration
 */
class ZFEngine_Module_UserExtended_Model_UserService extends ZFEngine_Module_UserBase_Model_UserService
{
    /**
     * Обработка формы регистрации
     *
     * @param array $postData
     * @return boolean
     */
    public function processRegistration($postData)
    {
        $form = $this->formRegistration;

        if ($form->isValid($postData)) {
                $this->getModel()->fromArray($form->getValues());

                $this->getModel()->role = Users_Model_User::ROLE_MEMBER;
                $this->getModel()->registration_ip = $_SERVER['REMOTE_ADDR'];
                $this->getModel()->activation_code = substr(md5(mktime() + rand(0, 100)), -8);

                $this->getModel()->save();
                $this->auth();

                $this->sendActivation();
        } else {
            $form->populate($postData);
            return false;
        }
    }

}