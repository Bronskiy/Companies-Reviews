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
 * @uses        ZFEngine_Application_Exception
 * @category    ZFEngine
 * @package     ZFEngine_Application
 * @copyright   Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license     http://zfengine.com/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * Resource for bootstraping modules and loading modules settings
 *
 * @uses        ZFEngine_Application_Exception
 * @category    ZFEngine
 * @package     ZFEngine_Application
 * @copyright   Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license     http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Application_Resource_Modules extends Zend_Application_Resource_Modules
{
    /**
     * Initialize modules
     * Redefine the parent init() method
     *
     * @return array
     * @throws Zend_Application_Resource_Exception When bootstrap class was not found
     */
    public function init()
    {
        parent::init();

        // Add module-autoloader from library
        $this->getBootstrap()->getApplication()
                ->getAutoloader()->pushAutoloader(array('ZFEngine_Loader_Autoloader', 'autoload'), 'ZFEngine_Module');

        // resource options
        foreach ($this->getOptions() as $name => $options) {
            $methodName = $this->_generateInitMethodName($name);
            if (method_exists($this, $methodName)) {
                $result = true;
                 // method that calling before calling intit method for every modules
                $preMethodName = $this->_generatePreInitMethodName($name);
                if (method_exists($this, $preMethodName)) {
                    $result = call_user_func_array(
                        array($this, $preMethodName),
                        array($moduleName, $moduleBootstrap, $options)
                    );
                }
                if (!$result) {
                    continue;
                }
                // modules bootstraps
                $bootstraps = $this->getExecutedBootstraps();
                foreach ($bootstraps as $moduleName => $moduleBootstrap) {
                    call_user_func_array(
                        array($this, $methodName),
                        array($moduleName, $moduleBootstrap, $options)
                    );
                }

                // method that calling after calling intit method for every modules
                $postMethodName = $this->_generatePostInitMethodName($name);
                if (method_exists($this, $postMethodName)) {
                    call_user_func_array(
                        array($this, $postMethodName),
                        array($moduleName, $moduleBootstrap, $options)
                    );
                }
        }
    }


    }

    /**
     * Generate init method name
     *
     * @param string $name
     * @return string
     */
    private function _generateInitMethodName($name)
    {
        return '_init' . ucfirst(strtolower($name));
    }

    /**
     * Generate post init method name
     *
     * @param string $name
     * @return string
     */
    private function _generatePostInitMethodName($name)
    {
        return '_postInit' . ucfirst(strtolower($name));
    }

    /**
     * Generate pre init method name
     *
     * @param string $name
     * @return string
     */
    private function _generatePreInitMethodName($name)
    {
        return '_preInit' . ucfirst(strtolower($name));
    }

    /**
     * Generate full path to file and check it's is exist
     *
     * @param Zend_Application_Bootstrap_BootstrapAbstract $moduleBootstrap
     * @param string $filename
     * @return string|boolean
     */
    private function _generateFullFilePath(
        Zend_Application_Bootstrap_BootstrapAbstract $moduleBootstrap, $filename
    ) {
        $moduleBasePath = $moduleBootstrap->getResourceLoader()->getBasePath();
        // generate full path to file and check it's is exist
        $configFilename = realpath($moduleBasePath . DIRECTORY_SEPARATOR . $filename);

        return $configFilename;
    }

    /**
     * Loading module configuration file
     *
     * @param string $moduleName
     * @param Zend_Application_Bootstrap_BootstrapAbstract $moduleBootstrap
     * @param array $options
     * @return void
     */
    protected function _initConfig($moduleName, $moduleBootstrap, $options = array())
    {
        // @todo добавить кеширование

        $filename = isset($options['filename']) ? $options['filename'] : '/configs/application.ini';
        if ($configFilePath = $this->_generateFullFilePath($moduleBootstrap, $filename)) {
            if (!$this->getBootstrap()->hasResource('Config')) {
                $this->getBootstrap()->bootstrap('Config');
            }
            $config = $this->getBootstrap()->getResource('config');

            $moduleConfig = new Zend_Config_Ini($configFilePath, APPLICATION_ENV);
             // add module settings to main config
            $config->$moduleName = $moduleConfig;
        }
    }


    /**
     * Check if cached acl oject.
     *
     * @return boolean
     */
    protected function _preInitAcl()
    {
        if (!$this->getBootstrap()->hasResource('Cache')) {
            $this->getBootstrap()->bootstrap('Cache');
        }
        $cache = $this->getBootstrap()->getResource('Cache');

        $cacheCore = $cache->core;

        $id = 'acl';
        if (!($aclChached = $cacheCore->load($id))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add ACL rules modules to main ACL
     *
     * @param string $moduleName
     * @param Zend_Application_Bootstrap_BootstrapAbstract $moduleBootstrap
     * @param array $options
     * @return void
     */
    protected function _initAcl($moduleName, $moduleBootstrap, $options = array())
    {
        $filename = isset($options['filename']) ? $options['filename'] : '/configs/acl.php';
        if ($aclFilePath = $this->_generateFullFilePath($moduleBootstrap, $filename)) {
            // Получаем объекты из главного Bootstrap
            if (!$this->getBootstrap()->hasResource('Acl')) {
                $this->getBootstrap()->bootstrapAcl();
            }
            $acl = $this->getBootstrap()->getResource('acl');

            require_once $aclFilePath;
        }
    }

     /**
     * Cache acl oject.
     *
     * @return void
     */
    protected function _postInitAcl()
    {
        if (!$this->getBootstrap()->hasResource('Cache')) {
            $this->getBootstrap()->bootstrap('Cache');
        }
        $cache = $this->getBootstrap()->getResource('Cache');

        $cacheCore = $cache->core;

        $id = 'acl';
        if (!($aclChached = $cacheCore->load($id))) {
            $cacheCore->save($aclChached, $id);
        }
    }
    
    /**
     * Add routes modules to router
     *
     * @param string $moduleName
     * @param Zend_Application_Bootstrap_BootstrapAbstract $moduleBootstrap
     * @param array $options
     * @return void
     */
    protected function _initRoutes($moduleName, $moduleBootstrap, $options = array())
    {
        // @todo добавить кеширование
        $filename = isset($options['filename']) ? $options['filename'] : '/configs/routes.xml';
        
        if ($routesFilePath = $this->_generateFullFilePath($moduleBootstrap, $filename)) {
            if (file_exists($routesFilePath)) {
                $this->getBootstrap()->bootstrapFrontController();
                $router = $this->getBootstrap()->getResource('FrontController')->getRouter();

                // caching routes.xml
                if (!$this->getBootstrap()->hasResource('Cache')) {
                    $this->getBootstrap()->bootstrap('Cache');
                }
                $cache = $this->getBootstrap()->getResource('Cache');

                $cacheConfig = $cache->config;
                if (!($config = $cacheConfig->load($moduleName . '_routes_xml'))) {
                    $config = new Zend_Config_Xml($routesFilePath);
                    $cacheConfig->save($config->toArray(), $moduleName . '_routes_xml');
                } else {
                    $config = new Zend_Config($config);
                }
                $router->addConfig($config);
            }
        }
    }

    /**
     * Add navigation settings modules to main navigation settings
     *
     * @param string $moduleName
     * @param Zend_Application_Bootstrap_BootstrapAbstract $moduleBootstrap
     * @param array $options
     * @return void
     */
    protected function _initNavigation($moduleName, $moduleBootstrap, $options = array())
    {
        // @todo добавить кеширование

        $filename = isset($options['filename']) ? $options['filename'] : '/configs/navigation.php';
        if ($navigationFilePath = $this->_generateFullFilePath($moduleBootstrap, $filename)) {

            $this->getBootstrap()->bootstrapNavigationContainer();
            $container = $this->getBootstrap()->getResource('navigationContainer');

            $pages = require_once $navigationFilePath;
            $container->addPages($pages);
        }
    }

    /**
     * Add resources types to modules
     *
     * @param string $moduleName
     * @param Zend_Application_Bootstrap_BootstrapAbstract $moduleBootstrap
     * @param array $options
     * @throws ZFEngine_Application_Exception if resource namespace or path is not specified
     * @return void
     */
    protected function _initResourceloader($moduleName, $moduleBootstrap, $options = array())
    {
        if (is_array($options)) {
            $resourceLoader = $moduleBootstrap->getResourceLoader();

            foreach ($options as $resourceTypeName => $resourceTypeOptions) {
                if (!isset($resourceTypeOptions['namespace'])) {
                    throw new ZFEngine_Application_Exception(sprintf(
                        'Not specified namespace to resource "%s"', $resourceTypeName)
                    );
                }
                if (!isset($resourceTypeOptions['path'])) {
                    throw new ZFEngine_Application_Exception(sprintf(
                        'Not specified path to resource "%s"', $resourceTypeName)
                    );
                }
                
                $resourceLoader->addResourceType($resourceTypeName,
                    $resourceTypeOptions['path'],
                    $resourceTypeOptions['namespace']
                );
            }
        }
    }
}