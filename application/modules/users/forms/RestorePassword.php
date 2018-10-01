<?php

/**
 * Restore password form
 */
class Users_Form_RestorePassword extends Main_Forms_ZForm
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
                array( 'RecordExists', true, array(
                    'users',
                    'mail',
                    'messages' => array(
                        'noRecordFound' => 'User not found'
                    )
                )),
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'E-mail:*',
            'maxlength'  => '255',
        ));
    }
}