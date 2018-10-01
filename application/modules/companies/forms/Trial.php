<?php

/**
 * Trial code creation form
 */
class Companies_Form_Trial extends Main_Forms_ZForm
{
    /**
     * Init form
     */
    public function init()
    {
        parent::init();  
        
        $this->addElementPrefixPath('Zend_Validate_Db', 'Zend/Validate/Db', 'validate');
        
        $this->addElement('text', 'code', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', true, array( 1, 1000, 'UTF-8' ))
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Code:',
            'maxlength'  => '1000'
        ));
        
    }
}