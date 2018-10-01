<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initForceSSL() {
        if($_SERVER['SERVER_PORT'] != '443') {
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    /**
     * Init config
     */
    protected function _initConfig()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Main_');
        $autoloader->registerNamespace('Doctrine_');
        $autoloader->registerNamespace('ZFEngine_');
        Main_Service_Config::throwExceptions(false);
        Main_Service_Config::registerPlugins();
    }

    /**
     * Role
     * @return string
     */
    protected function _initRole() {
        $this->bootstrap("modules");
        $auth = Zend_Auth::getInstance();

        $role = Users_Model_Role::DEFAULT_ROLE;

        if ($auth->hasIdentity() && !empty($auth->getIdentity()->role)) {
            $role = $auth->getIdentity()->role;
        }

        return $role;
    }

    /**
     * Setup ACL
     * @return Zend_Acl
     */
    protected function _initAcl() {
        $this->bootstrap("modules");

        if (!$this->hasResource("Role")) {
            $this->bootstrap("Role");
        }

        $role = $this->getResource("Role");

        $this->bootstrap("Cache");
        $cache = $this->getResource("Cache");

        $id = "acl";
        if (!($acl = $cache->load($id))) {
            $acl = new Main_Service_Acl();
            $cache->save($acl);
        }

        // Loading plugin to access controll
        $this->bootstrap("frontController");
        $front = $this->getResource("frontController");
        $front->registerPlugin(new ZFEngine_Controller_Plugin_Acl($acl, $role), 3);
        Zend_Controller_Action_HelperBroker::addHelper(new ZFEngine_Controller_Action_Helper_Acl());

        return $acl;
    }

    /**
     * Init view
     * @return Main_View
     */
    protected function _initView()
    {
        $view = new Main_View();

        $view->doctype('HTML5');
        $view->headMeta()->setCharset('UTF-8');
        $view->headMeta()->appendName('google-site-verification', 'EmM4rVM55wPkBSB_ngcUQWt655Bv6TWvRs08PDbiLDU');
        $view->headTitle()->prepend($this->getOption('title'));
        $view->headTitle()->setSeparator(' :: ');

        // CSS links
        $view->headLink()->prependStylesheet('/css/bootstrap.css');
        $view->headLink()->appendStylesheet('/css/fancybox.css');
        $view->headLink()->appendStylesheet('/css/flowplayer/skin/minimalist.css');
        $view->headLink()->appendStylesheet('/css/jquery.bxslider.css');
        $view->headLink()->appendStylesheet('/css/select2.min.css');
        $view->headLink()->appendStylesheet('/css/main.css');

        // JavaScript files
        $view->headScript()->appendFile('https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js', 'text/javascript');
        $view->headScript()->appendFile('/js/bootstrap.js', 'text/javascript');
        $view->headScript()->appendFile('/js/fancybox.js', 'text/javascript');
        $view->headScript()->appendFile('/js/jquery.raty/js/jquery.raty.js', 'text/javascript');
        $view->headScript()->appendFile('/js/flowplayer/flowplayer.min.js', 'text/javascript');
        $view->headScript()->appendFile('/js/jquery.bxslider.js', 'text/javascript');
        $view->headScript()->appendFile('/js/jquery.cookie.js', 'text/javascript');
        $view->headScript()->appendFile('/js/select2.full.min.js', 'text/javascript');
        $view->headScript()->appendFile('/js/main.js', 'text/javascript');
        $view->headScript()->appendFile('//www.google.com/recaptcha/api.js');

        // view helper paths
        $view->addHelperPath(realpath(APPLICATION_PATH . '/../library/Main/View/Helper/'));
        $view->addHelperPath(realpath(APPLICATION_PATH . '/../library/ZFEngine/View/Helper/'), 'ZFEngine_View_Helper_');
        $view->addScriptPath(realpath(APPLICATION_PATH . '/../library/Main/View/Scripts/'));
        $view->addScriptPath(realpath(APPLICATION_PATH . '/../library/Main/View/Scripts/Mail'));
        $view->addHelperPath(realpath(APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/View/Helper'), 'Cgsmith\\View\\Helper\\');


        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);

        return $view;
    }

    /**
     * Init routes
     */
    protected function _initRoutes()
    {
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();
        $router->removeDefaultRoutes();

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', APPLICATION_ENV);
        $router->addConfig($config, 'routes');

        // add API route
        $route = new Zend_Rest_Route($front, array(), array('api'));
        $router->addRoute('api', $route);
    }

    /**
     * Init helpers prefix
     */
    protected function _initHelpers()
    {
        Zend_Controller_Action_HelperBroker::addPrefix('Main_Controller_Action_Helper');
    }
}
