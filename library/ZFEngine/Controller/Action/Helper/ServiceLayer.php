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
class ZFEngine_Controller_Action_Helper_ServiceLayer extends Zend_Controller_Action_Helper_Abstract
{
    
    protected $_services = array();

    public function has($name)
    {
        if (isset($this->_services[$this->_getNamespase()][$name])) {
            return true;
        } else {
            return false;
        }
    }

    public function set($name, ZFEngine_Model_Service_Abstract $object)
    {
        $this->_services[$this->_getNamespase()][$name] = $object;
        return $this;
    }
    
    public function get($name, $metod = false)
    {
        if (!$this->has($name) && $metod != false) {
            $metod = new Zend_Reflection_Method($metod);
            $class = $metod->getDocblock()->getTag('return')->getType();
            $this->set($name, new $class);
        } 

        return $this->_services[$this->_getNamespase()][$name];
    }
    
    public function remove($name)
    {
        if ($this->has($name)) {
            unset($this->_services[$this->_getNamespase()][$name]);
        }
        return $this;
    }
    
    public function reset()
    {
        $this->_services = array();
        return $this;
    }
    
    protected function _getNamespase()
    {
        $request = $this->getRequest();
        return $request->getModuleName() . '/' . $request->getControllerName();
    }

    public function postDispatch()
    {
//        $this->getActionController()->_helper->removeHelper('serviceLayer');
        parent::postDispatch();
    }

}