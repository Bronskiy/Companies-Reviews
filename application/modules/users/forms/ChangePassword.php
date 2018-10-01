<?php

/**
 * Change password form
 */
class Users_Form_ChangePassword extends Main_Forms_ZForm
{
    /**
     * Init form
     */
    public function init()
    {
        parent::init();

        $this->addElement('password', 'password', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 1, 255, 'UTF-8' ) ),
                array( 'alnum', true, 'allowWhiteSpace' => false ),
            ),
            'decorators'   => array( 'ViewHelper' ),
            'required'     => true,
            'autocomplete' =>'off',
            'label'        => 'Password:*',
            'maxlength'    => '255',
        ));

        $this->addElement('password', 'password_confirm', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', true, array( 1, 255, 'UTF-8' ) ),
                array( 'alnum', true, 'allowWhiteSpace' => false ),
                array( 'identical', false, array(
                    'token'    => 'password',
                    'messages' => array(
                        Zend_Validate_Identical::NOT_SAME => 'Passwords should be the same'
                    )
                ))
            ),
            'decorators'  => array( 'ViewHelper' ),
            'required'    => true,
            'autocomplete'=>'off',
            'label'       => 'Confirm:*',
            'maxlength'   => '255',
        ));
    }
}