<?php

class ZFEngine_Validate_Uri extends Zend_Validate_Abstract
{
    
    const INVALID_URL = 'invalidUrl';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID_URL => "'%value%' is not a valid URL",
    );

    /**
     * Returns true if $value is valid url
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);

        if (strlen($value) && (!strpos($value, 'http://') && !strpos($value, 'https://') && !strpos($value, 'mailto:'))) {
            $value = 'http://' . $value;
        }

        if (!Zend_Uri::check($value)) {
            $this->_error(self::INVALID_URL);
            return false;
        }
        return true;
    }

}