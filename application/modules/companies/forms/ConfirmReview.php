<?php

/**
 * Confirm review form
 */
class Companies_Form_ConfirmReview extends Main_Forms_ZForm
{
    /**
     * Init form
     */
    public function init()
    {
        parent::init();  
        $this->addElementPrefixPath('Zend_Validate_Db', 'Zend/Validate/Db', 'validate');
        
        $this->addElement('text', 'code_num', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'Digits', true ),
                array( 'StringLength', true, array( 5, 5 ) ),
                array( 'RecordExists', true, array(
                    'companies',
                    'code_num',
                ))
            ),
            'decorators'  => array( 'ViewHelper' ),
            'required'    => true,
            'description' => 'Unique 5-digit code',
            'label'       => 'Company Code*:',
            'maxlength'   => '5',
        ));
    }
}