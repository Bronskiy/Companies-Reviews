<?php

/**
 * Review search form
 */
class Companies_Form_SearchReview extends Zend_Form {
    /**
     * Init form
     */
    public function init() {
        parent::init();

        $this->addElement('text', 'search', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty')
            ),
            'decorators' => array('ViewHelper'),
            'required' => true,
            'label' => 'Search:*',
            'maxlength' => '255',
        ));
    }
}