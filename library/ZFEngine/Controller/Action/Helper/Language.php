<?php
/**
 * ZFEngine
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://zfengine.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zfengine.com so we can send you a copy immediately.
 *
 * @category   ZFEngine
 * @package    ZFEngine_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Translator helper
 *
 * @category   ZFEngine
 * @package    ZFEngine_Controller
 * @subpackage Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Controller_Action_Helper_Language extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Default language
     * @var string
     */
    protected $_defaultLanguage;

    /**
     * Locales
     * @var array
     */
    protected $_locales;

    /**
     * Path to languages files
     * @var string
     */
    protected $_languagesDirectoryPath;

    /**
     * @param array $locales - Available locales
     * @param string $languagesDirectoryPath
     */
    public function __construct(array $locales, $languagesDirectoryPath)
    {
        $this->_languagesDirectoryPath = $languagesDirectoryPath;
        $this->_locales = $locales;
        $this->_defaultLanguage = key($locales); // get first language
    }
    
    public function init()
    {
        // try get current language from request
//      $lang =   $this->getRequest()->getParam('lang');
        $lang = Zend_Registry::isRegistered('lang') ? Zend_Registry::get('lang') : '';

        if (!$lang) {
            $locale = new Zend_Locale('auto');
            $lang = array_key_exists($locale->getLanguage(), $this->_locales)
                ? $locale->getLanguage() : $this->_defaultLanguage;
        }

        if (!array_key_exists($lang, $this->_locales)) {
            $lang = $this->_defaultLanguage;
        }

        $this->setTranslatorLang($lang);
    }

    public function setTranslatorLang($lang)
    {
        $cache = $this->getFrontController()->getParam('bootstrap')->getResource('Cache');
        Zend_Translate::setCache($cache->core);
        
        $languageDirPath = $this->_getLanguageDirPath($lang);
        // setup translate object
        if(is_dir($languageDirPath)) {
            $translator = new Zend_Translate(Zend_Translate::AN_GETTEXT, $languageDirPath, $lang, array('scan'=>Zend_Translate::LOCALE_DIRECTORY));
        } else {
            $translator = new Zend_Translate(Zend_Translate::AN_GETTEXT, null, $lang, array('disableNotices'=>true, 'scan'=>Zend_Translate::LOCALE_DIRECTORY));
        }
        Zend_Form::setDefaultTranslator($translator); // translated Zend_Form
        Zend_Registry::set('Zend_Translate', $translator);

        // get current locale by current language
        $locale = $this->_locales[$lang];
        $this->_actionController->_locale = $locale;
        $this->_actionController->_lang = $lang;
        Zend_Registry::set('lang', $lang);

        $this->_actionController->_translator = $translator;

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->view->locale = $locale;
        $viewRenderer->view->lang = $lang;

        $session = new Zend_Session_Namespace('locale');
        $session->lang = $lang;
    }

    private function _getLanguageDirPath($lang)
    {
        return $this->_languagesDirectoryPath . '/' . $lang;
    }
}
