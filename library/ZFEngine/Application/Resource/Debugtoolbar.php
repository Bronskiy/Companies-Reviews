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
 * Resource for initializing the ZFDebug toolbar
 *
 * @uses       ZFEngine_Application_Exception
 * @category   ZFEngine
 * @package    ZFEngine_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Application_Resource_DebugToolbar extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Setup ZFDebug panel
     * http://code.google.com/p/zfdebug/wiki/Installation
     * 
     * @return void
     */
    public function init()
    {
        $bootstrap = $this->getBootstrap();

        $autoloader = $bootstrap->getApplication()->getAutoloader();
        $autoloader->registerNamespace('ZFDebug');

        $options = $this->getOptions();

        // if plugins options is empty then set default
        if (!(isset($options['plugins']) && is_array($options['plugins']))) {
            $options['plugins'] = array(
                'Variables',
                'File' => array('base_path' => realpath(APPLICATION_PATH . '/../')),
                'Memory',
                'Time',
                'Registry',
                'Exception'
            );
        }

        $debug = new ZFDebug_Controller_Plugin_Debug($options);

        // Instantiate the database adapter and setup the plugin.
        // Alternatively just add the plugin like above and rely on the autodiscovery feature.
        if ($bootstrap->hasPluginResource('db')) {
            $bootstrap->bootstrapDb();
            $db = $bootstrap->getPluginResource('db')->getDbAdapter();
            $options['plugins']['Database']['adapter'] = $db;
        }

        // Setup the cache plugin
//        if ($bootstrap->hasPluginResource('cache')) {
//            $bootstrap->bootstrapCache();
//            $cache = $bootstrap->getPluginResource('cache')->getDbAdapter();
//            $options['plugins']['Cache']['backend'] = $cache->getBackend();
//        }

        $bootstrap->bootstrapFrontController();
        $frontController = $bootstrap->getResource('frontController');
        $frontController->registerPlugin($debug);
    }
}