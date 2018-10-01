<?php

/**
 * Category form
 */
class Companies_Form_Category extends Main_Forms_ZForm
{
    /**
     * Init form
     */
    public function init()
    {
        parent::init();

        $this->addElement('text', 'name', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 1, 100, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'Name:*',
            'maxlength'  => '100',
        ));
    }
}