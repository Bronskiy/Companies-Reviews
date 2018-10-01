<?php

/**
 * Abstract form
 */
abstract class Main_Forms_Abstract extends Zend_Form
{
    const TOKEN_NAME = 'csrf_token';

    /**
     * Init form
     */
    public function init()
    {
        $uniqueSalt = $this->_getHashItemSalt();

        $csrfToken = new Main_Form_Element_Csrf(get_class($this), self::TOKEN_NAME);
        $csrfToken->setDecorators(array('ViewHelper'));
        $csrfToken->setSalt($uniqueSalt);

        //$this->addElement($csrfToken);
    }
    
    /**
     * Salt for csrf hash element
     * @return type 
     */
    protected function _getHashItemSalt()
    {
        return md5(uniqid() . microtime());
    }
}
