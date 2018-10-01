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
/** Zend_Controller_Action_Helper_Abstract */
require_once 'Zend/Controller/Action/Helper/Abstract.php';
/**
 * Acl helper
 *
 * @category   ZFEngine
 * @package    ZFEngine_Controller
 * @subpackage Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var ZFEngine_Controller_Plugin_Acl
     **/
    protected $_aclPlugin;

    /**
     * Constructor
     *
     * @return void
     **/
    function __construct()
    {
        $this->_aclPlugin = $this->getAclPlugin();
    }

    /**
     * Returns the Acl Plugin object
     *
     * @return ZFEngine_Controller_Plugin_Acl
     **/
    public function getAclPlugin()
    {
        if (null === $this->_aclPlugin) {
            require_once 'Zend/Controller/Front.php';
            $front = Zend_Controller_Front::getInstance();
            if ($front->hasPlugin('ZFEngine_Controller_Plugin_Acl')) {
                $this->_aclPlugin = $front->getPlugin('ZFEngine_Controller_Plugin_Acl');
            }
             else {
                require_once 'ZFEngine/Controller/Plugin/Acl.php';
                $front->registerPlugin(new ZFEngine_Controller_Plugin_Acl());
                $this->_aclPlugin = $this->getAclPlugin();
            }
        }

        return $this->_aclPlugin;
    }

    /**
     * Call the denyAccess function of the Acl Plugin object
     *
     * @return void
     **/
    public function denyAccess()
    {
        $this->_aclPlugin->denyAccess();
    }

    /**
     * Call the pleaseLogin function of the Acl Plugin object
     *
     * @return void
     **/
    public function pleaseLogin()
    {
        $this->_aclPlugin->pleaseLogin();
    }

}