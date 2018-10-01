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
 * @subpackage Plugin
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Acl Plugin
 *
 * @category   ZFEngine
 * @package    ZFEngine_Controller
 * @subpackage Plugin
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * 
 * @edited by domencom
 */

class ZFEngine_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Acl
     **/
    protected $_acl;

    /**
     * @var string
     **/
    protected $_roleName;

    /**
     * @var array
     **/
    protected $_errorPage;

    /**
     * @var array
     **/
    protected $_notFoundPage;

    /**
     * @var array
     **/
    protected $_loginPage;

    /**
     * Constructor
     *
     * @param Zend_Acl $acl
     * @param $roleName
     * @return void
     **/
    public function __construct(Zend_Acl $acl = null, $roleName = Users_Model_Role::DEFAULT_ROLE)
    {
        $this->_errorPage = array('module' => 'default',
                                  'controller' => 'error',
                                  'action' => 'denied');

        $this->_notFoundPage = array('module' => 'default',
                                     'controller' => 'error',
                                     'action' => 'not-found');

        $this->_loginPage = array('module' => 'users',
                                  'controller' => 'index',
                                  'action' => 'login');

        $this->_roleName = $roleName;

        if (null !== $acl) {
            $this->setAcl($acl);
        }
    }

    /**
     * Sets the ACL object
     *
     * @param mixed $aclData
     * @return void
     **/
    public function setAcl(Zend_Acl $aclData)
    {
        $this->_acl = $aclData;
    }

    /**
     * Returns the ACL object
     *
     * @return Zend_Acl
     **/
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * Sets the ACL role to use
     *
     * @param string $roleName
     * @return void
     **/
    public function setRoleName($roleName)
    {
        $this->_roleName = $roleName;
    }

    /**
     * Returns the ACL role used
     *
     * @return string
     * @author
     **/
    public function getRoleName()
    {
        return $this->_roleName;
    }

    /**
     * Sets the error page
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     * @return void
     **/
    public function setErrorPage($action, $controller = 'error', $module = null)
    {
        $this->_errorPage = array('module' => $module,
                                  'controller' => $controller,
                                  'action' => $action);
    }

    /**
     * Sets the login page
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     * @return void
     **/
    public function setLoginPage($action, $controller = 'users', $module = null)
    {
        $this->_errorPage = array('module' => $module,
                                  'controller' => $controller,
                                  'action' => $action);
    }

    /**
     * Returns the error page
     *
     * @return array
     **/
    public function getErrorPage()
    {
        return $this->_errorPage;
    }

    /**
     * Returns the login page
     *
     * @return array
     **/
    public function getLoginPage()
    {
        return $this->_loginPage;
    }

    /**
     * Predispatch
     * Checks if the current user identified by roleName has rights to the requested url (module/controller/action)
     * If not, it will call denyAccess to be redirected to errorPage
     *
     * @return void
     **/
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        //Получаєм имена controller и action
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
        $controllerName = $request->getControllerName();
        $moduleName = $request->getModuleName();
        if (empty($controllerName)) {
            $controllerName = $dispatcher->getDefaultController();
        }
        $className = $dispatcher->getControllerClass($request);
        $finalClassName = $className;
        if (($dispatcher->getDefaultModule() != $moduleName)
            || $dispatcher->getParam('prefixDefaultModule'))
        {
            $finalClassName = $dispatcher->formatClassName($moduleName, $className);
        }
        //ucfirst($request->getModuleName()) . '_' .
        if ($className)
        {
            try
            {
                $executedBootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getPluginResource('modules')->getExecutedBootstraps();
                if (!isset($executedBootstrap[$moduleName])) {
                    throw new Zend_Controller_Dispatcher_Exception('Module ' . $moduleName . ' doesn\'t exist');
                }
                // if this fails, an exception will be thrown and
                // caught below, indicating that the class can't
                // be loaded.

                $dispatcher->loadClass($className);
                $actionName = $request->getActionName();
                if (empty($actionName)) {
                    $actionName = $dispatcher->getDefaultAction();
                }
                $methodName = $dispatcher->formatActionName($actionName);

                $class = new ReflectionClass( $finalClassName );
                if( $class->hasMethod( $methodName ) )
                {
                    $role = $this->getRoleName();
                    $resource = 'mvc:' . $request->getModuleName() . ':' . $request->getControllerName();
                    $privilege = $request->getActionName();
                    $this->_loadModuleAcl($moduleName);

                    /** Check if the controller/action can be accessed by the current user */
                    if (!$this->getAcl()->isAllowed($role, $resource, $privilege)) {
                        if (Zend_Auth::getInstance()->hasIdentity()) {
                            /** Redirect to access denied page */
                            $this->denyAccess();
                        } else {
                            /** Redirect to login page */
                            $this->pleaseLogin();
                        }
                    }
                    return;
                }
            }
            catch (Zend_Exception $e)
            {
                //throw $e;
                // @todo для отладки добавить возможность отображение пойманого исключения
                $this->notFoundAccess();
            }
        }

    }
    
    protected function _loadModuleAcl($moduleName)
    {
        $moduleName = strtolower($moduleName);
        $aclConfigFile = APPLICATION_PATH . '/modules/' . $moduleName . '/configs/Acl.php';
        $aclClass = ucfirst($moduleName) . '_Configs_Acl';

        if(is_file($aclConfigFile)) {
            require_once $aclConfigFile;
            if(class_exists($aclClass)) {
                $acl = new $aclClass;
                Zend_Registry::set(Main_Service_Acl::REGISTRY_NAME, $acl);
                $this->setAcl($acl);
                return true;
            }
        }
        return false;
    }

    /**
     * Deny Access Function
     * Redirects to errorPage, this can be called from an action using the action helper
     *
     * @return void
     **/
    public function denyAccess()
    {
        $this->_request->setModuleName($this->_errorPage['module']);
        $this->_request->setControllerName($this->_errorPage['controller']);
        $this->_request->setActionName($this->_errorPage['action']);
    }


    /**
     * Not Found page Function
     * Redirects to errorPage, this can be called from an action using the action helper
     *
     * @return void
     **/
    public function notFoundAccess()
    {
        $this->_request->setModuleName($this->_notFoundPage['module']);
        $this->_request->setControllerName($this->_notFoundPage['controller']);
        $this->_request->setActionName($this->_notFoundPage['action']);
    }

    /**
     * Please Login Function
     * Redirects to loginPage, this can be called from an action using the action helper
     *
     * @return void
     **/
    public function pleaseLogin()
    {
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setCode(302)
                   ->setExit(true)
                   ->gotoRoute(array(), 'login');
    }

}