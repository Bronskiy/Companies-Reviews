<?php

/**
 * Resend activation email form
 */
class Users_Form_Resend extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();

        $this->addElement('hidden', 'resend', array(
            'decorators' => array('ViewHelper'),
            'required' => true,
            'value' => 1,
        ));
    }
}