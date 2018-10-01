<?php

/**
 * Company card form
 */
class Companies_Form_CompanyCard extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(1, 1000, 'UTF-8'))
            ),
            'decorators' => array('ViewHelper'),
            'required' => true,
            'label' => 'Name*',
            'maxlength' => '1000',
        ));

        $this->addElement('text', 'address', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(1, 1000, 'UTF-8'))
            ),
            'decorators' => array('ViewHelper'),
            'required' => true,
            'label' => 'Address*',
            'maxlength' => '1000',
        ));

        $this->addElement('text', 'city', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(1, 1000, 'UTF-8'))
            ),
            'decorators' => array('ViewHelper'),
            'required' => true,
            'label' => 'City*',
            'maxlength' => '1000',
        ));

        $this->addElement('select', 'state', array(
            'validators' => array(
                array('StringLength', false, array(2, 2, 'UTF-8'))
            ),
            'decorators' => array('ViewHelper'),
            'label' => 'State*',
            'required' => true,
            'multioptions' => $this->_getStatesMultioptions()
        ));

        $this->addElement('text', 'zip', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'Digits',
                array('StringLength', false, array(5, 5, 'UTF-8'))
            ),
            'decorators' => array( 'ViewHelper' ),
            'required' => true,
            'label' => 'ZIP*',
            'maxlength' => '5',
        ));

        $this->addElement('text', 'number', array(
            'required' => true,
        ));

        $this->addElement('text', 'month', array(
            'required' => true,
        ));

        $this->addElement('text', 'year', array(
            'required' => true,
        ));

        $this->addElement('text', 'cvv', array(
            'required' => true,
        ));

        $this->addElement('checkbox', 'agree', array(
            'required' => true,
        ));
    }

    /**
     * Get state options
     * @return mixed
     */
    protected function _getStatesMultioptions() {
        return $this->getView()->states()->getStatesArray();
    }
}