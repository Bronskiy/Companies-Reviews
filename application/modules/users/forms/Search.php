<?php

/**
 * Search user form
 */
class Users_Form_Search extends Main_Forms_Abstract
{
    /**
     * Init form
     */
    public function init()
    {
        parent::init();
        
        $this->addElement('text', 'search', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 1, 100, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'Name or mail:*',
            'maxlength'  => '100',
        ));

    }
}