<?php

/**
 * Base service class
 */
abstract class Main_Service_Models {
    protected $_view = null;
    
    protected static $_mailer;
    
    protected $_tables = array();
    
    protected static $_forms = array();
    
    const PROCESSING_INFO_ERROR_TYPE   = Main_Controller_Action_Helper_SessionMessenger::ERRORS_NAMESPACE;
    const PROCESSING_INFO_SUCCESS_TYPE = Main_Controller_Action_Helper_SessionMessenger::SUCCESS_NAMESPACE;
    const PROCESSING_INFO_NOTICE_TYPE  = Main_Controller_Action_Helper_SessionMessenger::NOTICE_NAMESPACE;
    
    const ITEMS_PER_PAGE = 20;
    
    public function getTable($tableName = false, $moduleName = false)
    {
        if(empty($tableName) && empty($moduleName)) {
            $className = get_class($this);
            if(strpos($className, 'Service') === false) {
                throw new Exception(__CLASS__ . ' Incorrect class name: ' . $className);
            }
            $endPos = strlen($className) - strlen('Service');
            $modelName = substr($className, 0, $endPos);
        }else {
            if(empty($moduleName)) {
                $moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
            }

            $moduleName = ucfirst(strtolower($moduleName));
            
            $filterDashToCamelCase = new Zend_Filter_Word_DashToCamelCase();
            $tableName = $filterDashToCamelCase->filter($tableName);

            $modelName  = $moduleName . '_Model_' . $tableName;
        }
        
        if(array_key_exists($modelName, $this->_tables)) {
            return $this->_tables[$modelName];
        }
        
        $this->_tables[$modelName] = Doctrine_Core::getTable($modelName);
        return $this->_tables[$modelName];
    }
    
    /**
     * Return Form object from certain module
     * 
     * Form Class should be placed in forms directory
     * 
     * If from file and class CamelCased 
     *  for example filename: RestorePass.php and class RestorePass
     *  then $formName parameter should be restore-pass
     * 
     * @param type string
     * @param type string
     * @return Zend_Form
     * @throws Exception 
     */
    public function getForm($formName, $moduleName = false)
    {
        return self::getStaticForm($formName, $moduleName);
    }
    
    /**
     * Return Form object from certain module
     * 
     * Form Class should be placed in forms directory
     * 
     * If from file and class CamelCased 
     *  for example filename: RestorePass.php and class RestorePass
     *  then $formName parameter should be restore-pass
     * 
     * @param string $formName
     * @param string $moduleName
     * @return Zend_Form
     * @throws Exception 
     */
    public static function getStaticForm($formName, $moduleName = false)
    {
        if(empty($moduleName)) {
            $moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        }
        $moduleName = strtolower($moduleName);
        
        $filterDashToCamelCase = new Zend_Filter_Word_DashToCamelCase();
        $formName = $filterDashToCamelCase->filter($formName);

        $formClassName = ucfirst($moduleName) . '_Form_' . $formName;
        
        if(array_key_exists($formClassName, self::$_forms)) {
            return self::$_forms[$formClassName];
        }
        
        $dir = APPLICATION_PATH . '/modules/' . $moduleName . '/forms/';
        $isExists = is_file($dir . $formName . '.php');
        
        if($isExists !== true) {
            throw new Exception('Could not load form: ' . $formClassName);
        }
        
        self::$_forms[$formClassName] = new $formClassName();
        
        return self::$_forms[$formClassName];
    }
    
    /**
     * 
     * @return Main_Application_Resource_Logger 
     */
    public static function getLogger()
    {
        return Zend_Registry::get(Main_Application_Resource_Logger::DEFAULT_REGISTRY_KEY);
    }
    
    /**
     * 
     * @return array 
     * @see Main_Service_ConfigsLoader::getConfig()
     */
    public static function getConfig()
    {
        return Main_Service_ConfigsLoader::getConfig();
    }
    
    /**
     * Return current client IP if valid
     * 
     * @return string | NULL 
     */
    public static function getClientIp()
    {
        $clientIp    = Zend_Controller_Front::getInstance()->getRequest()->getClientIp(true);
        $ipValidator = new Zend_Validate_Ip();
        
        return $ipValidator->isValid($clientIp) ? $clientIp : null;
    }
    
    /**
     * Adding information about some dispatching process. 
     * This information will be displayed to the user
     *  
     * @param array | string $data
     * @param string $type
     * @return boolean
     * @throws Exception 
     */
    public static function addProcessingInfo($message, $type = self::PROCESSING_INFO_ERROR_TYPE)
    {
        $messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('SessionMessenger');

        if(! in_array($type, $messenger->getAllowedNamespaces())) {
            throw new Exception('$type parameter contains an unsupported type name');
        }
        
        if(is_array($message) || is_string($message)) {
            $messenger->addMessage($message, $type);
            return true;
        }
        
        return false;
    }
    
   
    /**
     * Retrieve view object
     *
     * If none registered, attempts to pull from ViewRenderer.
     *
     * @return Zend_View_Interface|null
     */
    public function getView()
    {
        if (null === $this->_view) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->_view = $viewRenderer->view;
        }
        return $this->_view;
    }
    
    /**
     * Return paginator object
     * 
     * @param type $queryFetchAll
     * @param type $pageNumber
     * @param type $itemsPerPage
     * @return Zend_Paginator 
     */
    public function getPaginator($queryFetchAll, $pageNumber, $itemsPerPage,
                                 $hydrationMode = null, $options = array())
    {
        $paginator = new Zend_Paginator(
                new ZFEngine_Paginator_Adapter_Doctrine($queryFetchAll, 
                                                        $hydrationMode, $options));
        $paginator->setCurrentPageNumber($pageNumber);
        $paginator->setItemCountPerPage($itemsPerPage);
        return $paginator;
    }
    
    /**
     * Default number items per page for pagination
     * 
     * @return int 
     */
    public static function getItemsPerPageDefault()
    {
        $config = self::getConfig();
        $itemsPerPage = @$config->pagination->itemsPerPage;
        return ((int)$itemsPerPage > 0) ? $itemsPerPage : self::ITEMS_PER_PAGE;
    }
    
    /**
     * Adding data to the FlashMessenger
     * 
     * @param string | array $messages
     * @return boolean 
     */
    public static function addMessagesToFlashMessenger($messages)
    {
        if(empty($messages)) return false;
        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        // можно добавлять массив, ибо проверка есть в хелпере ProcessingInfo
        if(is_string($messages) || is_array($messages)) {
            $flashMessenger->addMessage($messages);
        }
        return true;
    }
    
    /**
     * Finding logged in user object in database
     * 
     * @return  Users_Model_User | FALSE
     */
    public static function getAuthUser()
    {
        if(! Zend_Auth::getInstance()->hasIdentity()) {
            return false;
        }
        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        return Users_Model_UserTable::getInstance()->findOneById($userId);
    }
    
    /**
     * Validating csrf token in request
     * 
     * @param array $data
     * @return boolean 
     */
    public function isValidCsrfToken(array $data)
    {
        if(empty($data['postfix'])) {
            return false;
        }
        $form = new Main_Forms_Csrf(array('postfix' => $data['postfix']));
        return $form->isValid($data);
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
     * Unique hash generation
     * 
     * @return hash 
     */
    public static function generateUniqueHash()
    {
        return md5(
            mt_rand(1,1000000)
            . uniqid()
            .  mt_rand(1,1000000)
        );
    }
    
    /**
     * Credit card system configure
     *  
     */
    public static function configureBrainTree()
    {
        $config = @self::getConfig()->braintree;
        
        Braintree_Configuration::environment($config->environment);
        Braintree_Configuration::merchantId($config->merchantId);
        Braintree_Configuration::publicKey($config->publicKey);
        Braintree_Configuration::privateKey($config->privateKey);
    }
    
    /**
     * Returns mailer class
     * @return Main_Service_Mailer 
     */
    public static function getMailer() {
        if (null === self::$_mailer) {
            self::$_mailer = new Main_Service_Mailer();
        }

        return self::$_mailer;
    }

    /**
     * Get extension by MIME type
     * @param $type MIME type
     */
    public static function getExtensionByMimeType($type) {
        $ext = "mp4";

        switch ($type) {
            case "video/mp4":
                $ext = "mp4";
                break;

            case "video/webm":
                $ext = "webm";
                break;

            case "video/quicktime":
                $ext = "mov";
                break;

            case "video/3gpp":
                $ext = "3gp";
                break;
        }

        return $ext;
    }

    /**
     * Get a list of states
     * @return array
     */
    public function getStatesArray() {
        return array(
            "AL" => "Alabama",
            "AK" => "Alaska",
            "AZ" => "Arizona",
            "AR" => "Arkansas",
            "CA" => "California",
            "CO" => "Colorado",
            "CT" => "Connecticut",
            "DE" => "Delaware",
            "FL" => "Florida",
            "GA" => "Georgia",
            "HI" => "Hawaii",
            "ID" => "Idaho",
            "IL" => "Illinois",
            "IN" => "Indiana",
            "IA" => "Iowa",
            "KS" => "Kansas",
            "KY" => "Kentucky",
            "LA" => "Louisiana",
            "ME" => "Maine",
            "MD" => "Maryland",
            "MA" => "Massachusetts",
            "MI" => "Michigan",
            "MN" => "Minnesota",
            "MS" => "Mississippi",
            "MO" => "Missouri",
            "MT" => "Montana",
            "NE" => "Nebraska",
            "NV" => "Nevada",
            "NH" => "New Hampshire",
            "NJ" => "New Jersey",
            "NM" => "New Mexico",
            "NY" => "New York",
            "NC" => "North Carolina",
            "ND" => "North Dakota",
            "OH" => "Ohio",
            "OK" => "Oklahoma",
            "OR" => "Oregon",
            "PA" => "Pennsylvania",
            "RI" => "Rhode Island",
            "SC" => "South Carolina",
            "SD" => "South Dakota",
            "TN" => "Tennessee",
            "TX" => "Texas",
            "UT" => "Utah",
            "VT" => "Vermont",
            "VA" => "Virginia",
            "WA" => "Washington",
            "WV" => "West Virginia",
            "WI" => "Wisconsin",
            "WY" => "Wyoming",
        );
    }

    /**
     * Reversed states array
     * @return array a list of states
     */
    public function getReversedStatesArray() {
        $reversed = array();

        foreach ($this->getStatesArray() as $code => $state) {
            $reversed[$state] = $code;
        }

        return $reversed;
    }
}
