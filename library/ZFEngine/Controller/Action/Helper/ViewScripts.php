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
 * @package    ZFEngine_Controller
 * @subpackage Action_Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Set directories to view scripts
 *
 * @category   ZFEngine
 * @package    ZFEngine_Controller
 * @subpackage Action_Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Controller_Action_Helper_ViewScripts extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Stack parent classes of controller
     *
     * @var array
     */
    protected $_parentClassStack = array();

    /**
     * Set the module folder for view scripts
     *
     * @param $moduleName
     * @return void
     **/
    protected function _setScriptsPathByModuleName($moduleName)
    {

        $view = Zend_Layout::getMvcInstance()->getView();
        
        // Get already set path to folders
        $scriptPaths = $view->getScriptPaths();
        
        // Create stack parent classes of controller
        $this->_findParentClass($this->getActionController());
        // Disabling inserting controller name in path
        $this->getActionController()->getHelper('viewRenderer')->setNoController(true);

        // Path to module directory
        $modulePath = APPLICATION_PATH . '/modules';

        // Adding path to view scripts for every parent class
        foreach (array_reverse($this->_parentClassStack) as $class) {
            $view->addScriptPath( 
                    $modulePath . DIRECTORY_SEPARATOR .
                    $this->_formatModuleName($class) .
                    '/views/_abstract/' .
                    $this->_formatClassName($class)
            );
        }
        
        // And also adds a controller name for paths created earlier by Zend
        foreach ($scriptPaths as $path) {
            $view->addScriptPath($path . $this->getRequest()->getControllerName());
        }

        // Add path to module directory
        $view->addScriptPath($modulePath);
        
    }

    /**
     * Find parenting classes
     *
     * @param object|string $currentClass
     */
    protected function _findParentClass($currentClass)
    {
        $parentClass = get_parent_class($currentClass);
        // Recursion, while parent class exist
        if ($parentClass != 'Zend_Controller_Action' && $parentClass != false) {
            $this->_parentClassStack[] = $parentClass;
            $this->_findParentClass($parentClass);
        }
    }

    /**
     * Format module name: Foo_Bar_CamelCase -> foo
     *
     * @param string $name
     * @return string
     */
    protected function _formatModuleName($name)
    {
        // Cut the name of the class before the first character "_"
        $name = substr($name, 0, strpos($name, '_'));
        // To lower case
        $name = strtolower($name);

        return $name;
    }

    /**
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
     * Predispatch hooks
     * Set directories to view scripts
     * 
     * @return void
     **/
    public function preDispatch() 
    {
        $this->_setScriptsPathByModuleName( $this->getRequest()->getModuleName());
    }
    

}