<?php

/**
 * Login form
 */
class Users_Form_Login extends Main_Forms_ZForm
{
    /**
     * Init form
     */
    public function init()
    {
        parent::init();
        $this->addElementPrefixPath('Zend_Validate_Db', 'Zend/Validate/Db', 'validate');

    	$this->addElement('text', 'mail', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', true, array( 4, 255 ) ),
                array( 'EmailAddress', true ),
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'E-mail:*',
            'maxlength'  => '255',
        ));

        $this->addElement('password', 'password', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 1, 255, 'UTF-8' ) ),
                array( 'alnum', true, 'allowWhiteSpace' => false ),
            ),
            'decorators'   => array( 'ViewHelper' ),
            'required'     => true,
            'autocomplete' => 'off',
            'label'        => 'Password:*',
            'maxlength'    => '255',
        ));
    }
}
    