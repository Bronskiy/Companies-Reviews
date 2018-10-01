<?php

class ZFEngine_Module_UserBase_Form_User_PasswordRestore extends Zend_Form
{

    public function init()
    {
        $this->setName(strtolower(__CLASS__));

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel(_('Введите свой email:'))
              ->setRequired(true)
              ->addFilter('StringTrim')
              ->addValidator('EmailAddress', true);
        $this->addElement($email);

        $captcha = new Zend_Form_Element_Captcha('foo', array(
            'label' => _("Введите текст, расположенный ниже:"),
            'captcha' => array(
                'captcha' => 'Figlet',
                'wordLen' => 4,
                'timeout' => 300,
            ),
        ));
        $this->addElement($captcha);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel(_('Отправить'))
            ->setIgnore(true);
        $this->addElement($submit);
    }
}