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
 * Returns service layer for model of module
 *
 * @category   ZFEngine
 * @package    ZFEngine_Controller
 * @subpackage Action_Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Controller_Action_Helper_GetServiceLayer extends Zend_Controller_Action_Helper_Abstract
{
   /**
     * Returns service layer object|class
     *
     * @param string $module name
     * @param string $model name
     * @return ZFEngine_Model_Service_Abstract|string
     **/
    public function direct($module, $model)
    {
        $module = ucfirst($module);
        $model = ucfirst($model);

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $currentModule = ucfirst($request->getModuleName());

        $classIsExist = false;
        
        // Если указаный модуль отличается от текущего
        // проверяем, есть ли для текущего модуля переопределенный Service Layer
        if ($currentModule != $module) {
            $class = $currentModule . '_Model_' . $model . 'Service';

            $classIsExist = Zend_Controller_Front::getInstance()
                    ->getParam('bootstrap')
                    ->getPluginResource('modules')
                    ->getExecutedBootstraps()
                    ->{$request->getModuleName()}
                    ->getResourceLoader()
                    ->getClassPath($class);
        }

        // Если переопределенного класа нет - вызываем базовый класс
        if ($classIsExist == false) {
            $class = $module . '_Model_' . $model . 'Service';
        }
        
        return new $class;
    }
}