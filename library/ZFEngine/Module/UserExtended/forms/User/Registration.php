<?php

class ZFEngine_Module_UserExtended_Form_User_Registration extends Zend_Form
{

    public function init()
    {
        $this->setName(strtolower(__CLASS__));

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel(_('Email'))
              ->setRequired(true)
              ->addFilter('StringTrim')
              ->addValidator('EmailAddress', true)
              ->addValidator(new ZFEngine_Validate_Doctrine_NoRecordExist(
                      'Users_Model_User', 'email',
                      _('Пользователь с таким e-mail адресом уже зарегистрирован'))
                  );
        $this->addElement($email);

        $login = new Zend_Form_Element_Text('login');
        $login->setLabel(_('Логин'))
              ->setRequired(true)
              ->addFilter('StringTrim')
              ->addValidator(new ZFEngine_Validate_Doctrine_NoRecordExist(
                      'Users_Model_User', 'login',
                      _('Пользователь с таким логином уже зарегистрирован'))
                  );
        $this->addElement($login);

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel(_('Пароль'))
                 ->setRequired(true)
                 ->setValue(null)
                 ->addValidator('StringLength', true,
                                array(Users_Model_User::MIN_PASSWORD_LENGTH))
                 ->setErrorMessages(array(
                     sprintf($this->getView()->translate('Длина пароля должна быть не меньше %s символов'),
                     Users_Model_User::MIN_PASSWORD_LENGTH))
                 );
                 
        $passwordConfirm = new Zend_Form_Element_Password('password_confirm');
        $passwordConfirm->setLabel(_('Потверждение пароля'))
                        ->setValue(null);
        $password->addValidator(new ZFEngine_Validate_InputEquals($passwordConfirm->getName(),
                                                             _('Пароль и подтверждение пароля не совпадают')));
        $this->addElements(array($password, $passwordConfirm));


        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel(_('Зарегистрироваться'))
            ->setIgnore(true)
            ->setOrder(100); // В самый конец формы
        
        $this->addElement($submit);


        return $this;
    }

}