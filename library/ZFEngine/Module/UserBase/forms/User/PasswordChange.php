<?php

class ZFEngine_Module_UserBase_Form_User_PasswordChange extends ZFEngine_Module_UserBase_Form_User_PasswordReset
{

    public function init()
    {
        parent::init();
        $this->setName(strtolower(__CLASS__));

        $password = new Zend_Form_Element_Password('old_password');
        $password->setLabel(_('Старый пароль'))
                 ->setRequired(true)
                 ->setOrder(-100)
                 ->setValue(null)
                 ->addValidator('StringLength', false,
                                array(Users_Model_User::MIN_PASSWORD_LENGTH));

        $this->addElement($password);

    }
}