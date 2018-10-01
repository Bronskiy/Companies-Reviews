<?php 
abstract class Main_Translate_Abstract
{
    const REGISTRY_KEY = 'Zend_Translate';
    
    abstract public static function regTranslation(Zend_Controller_Request_Abstract $request);

    protected static function _getConfig()
    {
        return new Zend_Config_Ini(
                  APPLICATION_PATH . '/configs/application.ini',
                  'production');
    }
}