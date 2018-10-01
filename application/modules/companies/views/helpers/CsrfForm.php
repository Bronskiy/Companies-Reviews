<?php

/**
 * Return CSRF-protected form
 */
class Zend_View_Helper_CsrfForm extends Zend_View_Helper_Abstract {
    /**
     * CSRF form
     * @param array $options
     * @return Main_Forms_Csrf
     */
    public function csrfForm(array $options = null) {
        return new Main_Forms_Csrf($options);
    }
}