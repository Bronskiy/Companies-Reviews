<?php
/**
 * Controller base class
 * @author domencom
 */
abstract class Main_Controller_Action extends Zend_Controller_Action
{
    /**
     * Array of Service classes wich previously called from controller action
     * @var array 
     */
    protected $_services = array();
    
    /**
     * @var Zend_Controller_Action_Helper_Redirector 
     */
    protected $_redirector = null;
    static $maintenanceRedirect = false;
    
    public function init()
    {
        $this->_redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $this->_redirector->setExit(true);
    }
    
    /**
     * Автоматическая загрузка класса сервиса с именем $serviceName
     *  например если есть класс Users_Model_UserService
     *  $serviceName будет 'user'
     *  
     *  $moduleName будет 'users' при загрузке данного класса из контроллеров
     *              других модулей или null если загрузка происходит
     *              из контроллера модуля users
     * 
     * @param string $serviceName
     * @param string $moduleName
     * @return Main_Service_Models service
     * @throws Exception 
     */
    public function getService($serviceName, $moduleName = null)
    {
        if(empty($moduleName)) {
            $moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        }
        
        $moduleName = strtolower($moduleName);
        
        $filterDashToCamelCase = new Zend_Filter_Word_DashToCamelCase();
        $serviceName = $filterDashToCamelCase->filter($serviceName). 'Service';

        $serviceClassName = ucfirst($moduleName) . '_Model_' . $serviceName;
        
        if(array_key_exists($serviceClassName, $this->_services)) {
            return $this->_services[$serviceClassName];
        }
        
        $dir = APPLICATION_PATH . '/modules/' . $moduleName . '/models/';
        $isExists = is_file($dir . $serviceName . '.php');
        
        if($isExists !== true) {
            throw new Exception('Could not load service: ' . $serviceClassName);
        }
        
        $this->_services[$serviceClassName] = new $serviceClassName();
        
        return $this->_services[$serviceClassName];
    }
    
    /**
     * Checks if $checkName is current route name
     * 
     * Method useful when controller action could be called from different urls
     * 
     * @param string $checkName
     * @param bool $isRedirect
     * @return bool 
     * @throws Zend_Controller_Action_Exception
     */
    public function checkRoute($checkName, $isRedirect = false)
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $routeName = $router->getCurrentRouteName();
        
        $res = strcmp($routeName, $checkName) === 0 ? true : false;
        
        if($res === false && $isRedirect === true) {
            $this->_redirectNotFoundPage();
        }
        return $res;
    }
    
    /**
     * Check company existance and activity by id (only for frontend part)
     * 
     * @throws  Zend_Controller_Action_Exception
     */
    public function checkCompanyById($id)
    {
        $company = $this->getService('company')->getCompanyById($id);

        if (! $this->isCompanyActive($company)) {
            $this->_redirectNotFoundPage();
        }
        return true;
    }
    
     /**
     * Check company existance and activity by id for admin only!!!
     * 
     * @throws  Zend_Controller_Action_Exception
     */
    public function adminCheckCompanyById($id)
    {
        $company = $this->getService('company')->getCompanyById($id);

        if (!$company || $company->status == Companies_Model_Company::STATUS_DELETED) {
            $this->_redirectNotFoundPage();
        }
        return true;
    }
    
    /**
     * Check company existence by uri
     * @throws  Zend_Controller_Action_Exception
     */
    public function checkCompanyByUri() {
        try {
            $uriParts = array();

            foreach ($this->_request->getParams() as $k => $v) {
                if (in_array($k, array("name", "city", "state"))) {
                    $uriParts[] = $v;
                }
            }

            $uri = "/" . implode("/", $uriParts);
        } catch(Exception $e) {
            $this->_redirectNotFoundPage();
        }
        
        $company = $this->getService("company")->getCompanyByUri($uri);
        
        if (!$this->isCompanyActive($company)) {
            $this->_redirectNotFoundPage();
        }

        return true;
    }
    
    /**
     * If company in active status (site frontend)
     * 
     * @staticvar array $activeStatuses
     * @param mixed $company
     * @return boolean 
     */
    public function isCompanyActive($company = null)
    {
        $activeStatuses = Companies_Model_Company::getActiveStatuses();
        
        if(! is_object($company) 
           || ! ($company instanceof Companies_Model_Company)
           || ! (in_array($company->status, $activeStatuses)))
        {
            return false;
        }
        
        return true;
    }
    
    /**
     *  Throwing exception with page status code this will lead to 
     *     redirect in error page 
     * 
     *  WARNING method usefull only if exists correct errorHandler plugin
     */
    protected function _redirectNotFoundPage()
    {
        throw new Zend_Controller_Action_Exception('page not found', 404);
    }
    
    /**
     * Sets headers for update browser cache
     * 
     */
    public function setUpdateCacheHeaders()
    {
        $response = $this->getResponse();
        $response->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true)
                 ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate', true)
                 ->setHeader('Pragma', 'no-cache', true);
    }

    /**
     * Build URL
     * @return string
     */
    public function url($route, $params=array())
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $url = $router->assemble($params, $route);

        return $url;
    }
    
    /**
     * Redirection to request page
     *  
     */
    protected function _redirectToRequestPage()
    {
        $url = $_SERVER['HTTP_REFERER'];
        $this->_redirector->gotoUrlAndExit($url);
    }

    /**
     * Maintenance check
     */
    public function preDispatch() {
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getActionName();

        if (self::$maintenanceRedirect) {
            return;
        }

        if (file_exists(".maintenance") && $controller != "error" && ($controller != "index" || $action != "maintenance")) {
            self::$maintenanceRedirect = true;
            $this->forward("maintenance", "static", "default");
        }
    }
}

