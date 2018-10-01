<?php

/**
 * Main form class
 */
class Main_Forms_ZForm extends Main_Forms_Abstract
{
    /**
     * Init form
     */
    public function init()
    {
        parent::init();
        $this->addElementPrefixPath('Main_Validate', 'Main/Validate/', 'validate');
    }
}