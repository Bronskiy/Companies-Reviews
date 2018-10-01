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
 * @package    ZFEngine_View
 * @subpackage    Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   ZFEngine
 * @package    ZFEngine_View
 * @subpackage    Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
abstract class ZFEngine_View_Helper_Abstract extends Zend_View_Helper_Abstract
{
    /**
     * Service layer object
     * 
     * @var ZFEngine_Model_Service_Abstract
     */
    protected $_serviceLayer = null;

    /**
     * Request object
     * 
     * @var Zend_Controller_Request_Abstract
     */
    static protected $_request = null;

    /**
     * Название модуля
     */
    protected $_moduleName = null;
    /**
     * Название модели
     */
    protected $_modelName = null;
    /**
     * Название контролера
     */
    protected $_controllerName = null;
    /**
     * Название вызываемого скрипта представления
     */
    protected $_viewScriptName = null;
    /**
     * Название модуля из которого будет браться представление
     */
    protected $_moduleOfViewScript = null;

    /**
     * Set custom service layer
     *
     * @param string $moduleName
     * @param string $modelName
     * @param string $controller
     *
     */
    public function setServiceLayer($moduleName, $modelName) {
        $this->_moduleName = $moduleName;
        $this->_modelName = $modelName;

        $moduleName = ucfirst($moduleName);
        $modelName = ucfirst($modelName);
        $class = $moduleName . '_Model_' . $modelName . 'Service';
        $layer = new  $class;
        if ($layer instanceof ZFEngine_Model_Service_Abstract) {
            $this->_serviceLayer = $layer;
        }
        return $this;
    }

    /**
     * Return request object
     *
     * @return Zend_Controller_Request_Abstract
     */
    protected function _getRequest()
    {
        if (!isset(self::$_request)) {
            self::$_request = Zend_Controller_Front::getInstance()->getRequest();
        }
        return self::$_request;
    }
    
     /**
     * Получаем название модуля
     *
     * @return string
     */
    protected function getModuleName() {
        return $this->_moduleName;
    }

    /**
     * Получаем название котроллера
     *
     * @return string
     */
    protected function getContorllerName() {
        return $this->_controllerName;
    }

    /**
     * Получаем название модели
     *
     * @return string
     */
    protected function getModelName() {
        return $this->_modelName;
    }

    /**
     * Устанавливает путь к скриптам представления
     *
     * @param string $moduleName
     * @param string $viewScriptName
     * @return string
     */
    public function setViewScript($moduleName, $viewScriptName = NULL) {
        $this->_moduleOfViewScript = $moduleName;
        $this->_viewScriptName = $viewScriptName;
        return $this;
    }

    /**
     * Получаем путь к скриптам представления
     *
     * @param string $viewScriptName
     * @return string
     */
    public function getViewScript($viewScriptName = NULL) {
    	$separator = '/';
        if (isset($this->_moduleOfViewScript)) {
            $module = $this->_moduleOfViewScript;
        } elseif ($this->getModuleName() != null) {
            $module = $this->getModuleName();
        } else {
            $module = $this->_getModuleNameFromClassName(get_class($this));
        }

        $viewScriptPath = 'helper' . $separator . 'module' . $separator . $module;

        $helperName = $this->_formatClassName(get_class($this));
        $viewScriptPath .=   $separator . $helperName;
        
        if (isset($viewScriptName)) {
            $viewScriptName = $separator . $viewScriptName;
        } else {
           if (isset($this->_viewScriptName)) {
                $viewScriptName = $separator . $this->_viewScriptName;
            }
        }

        $viewScriptPath .= $viewScriptName . '.phtml' ;
        return $viewScriptPath;
    }

    /*
     * Format class name: Foo_Bar_CamelCase -> camel-case
     *
     * @param string $name
     * @return string
     */
    protected function _formatClassName($name)
    {
        // Cut the name of the class after the last character "_"
        $name = substr($name, strrpos($name, '_')+1);
        // Adds hyphen before upper characters
        $name = preg_replace('/([A-Z])/', '-$1', $name);
        // To lower case
        $name = strtolower($name);
        // Removing unnecessary hyphens
        return trim($name, "-");
    }

    /**
     * Format module name: Foo_Bar_CamelCase -> foo
     *
     * @param string $name
     * @return string
     */
    protected function _getModuleNameFromClassName($name)
    {
        // Cut the name of the class before the first character "_"
        $name = substr($name, 0, strpos($name, '_'));
        // To lower case
        $name = strtolower($name);

        return $name;
    }

}
