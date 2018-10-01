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
 * Check access to resource
 *
 * @category   ZFEngine
 * @package    ZFEngine_View
 * @subpackage    Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_View_Helper_IsAllowed extends Zend_View_Helper_Abstract
{

    /**
     * Check access to resource
     *
     * @param string $resource
     * @param string $privilege
     * @return boolean
     */
    public function isAllowed($resource, $privilege = null)
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $acl = $bootstrap->getResource('acl');
        $role = $bootstrap->getResource('role');
        // if resource not found in main acl trying to load module acl
        if(! $acl->has($resource) && Zend_Registry::isRegistered(Main_Service_Acl::REGISTRY_NAME)) 
        {
            $acl = Zend_Registry::get(Main_Service_Acl::REGISTRY_NAME);
        }
        return $acl->isAllowed($role, $resource, $privilege);
    }
}