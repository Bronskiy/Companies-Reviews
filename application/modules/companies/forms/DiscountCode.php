<?php

/**
 * Discount code form
 */
class Companies_Form_DiscountCode extends Main_Forms_ZForm {
    /**
     * Form init
     */
    public function init() {
        parent::init();  

        $this->addElement('text', 'code', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(1, 1000, 'UTF-8')),
            ),
            'decorators' => array('ViewHelper'),
            'required' => true,
            'label' => 'Discount Code*',
            'maxlength' => '1000',
            'description' => 'If you have a discount code, please enter it here.',
        ));
    }
}