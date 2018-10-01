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
 * @subpackage    Action_Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Check access to resource
 *
 * @category   ZFEngine
 * @package    ZFEngine_Controller
 * @subpackage    Action_Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Controller_Action_Helper_IsAllowed extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Check access to resource
     *
     * @return boolean
     **/
    public function direct($resource, $privilege = null)
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $acl = $bootstrap->getResource('acl');
        $role = $bootstrap->getResource('role');

        return $acl->isAllowed($role, $resource, $privilege);
    }

}