<?php

/**
 * CSRF element
 */
class Main_Form_Element_Csrf extends Zend_Form_Element_Hash {
    protected $_formName;

    /**
     * TTL for CSRF token
     * @var int
     */
    protected $_timeout = 300;

    /**
     * Constructor
     * @param string $formName
     * @param string $spec
     */
    public function __construct($formName, $spec)
    {
        $this->_formName = $formName;
        parent::__construct($spec, null);

        $this->setAllowEmpty(false)
            ->setRequired(true)
            ->initCsrfValidator();

        $this->setTimeout(60 * 60 * 24);
    }

    /**
     * Get session name
     * @return string
     */
    public function getSessionName()
    {
        return __CLASS__ . '_' . $this->getSalt() . '_' . $this->_formName . '_' . $this->getName();
    }
}
