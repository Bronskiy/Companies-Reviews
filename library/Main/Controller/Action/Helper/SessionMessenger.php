<?php
class Main_Controller_Action_Helper_SessionMessenger extends Zend_Controller_Action_Helper_FlashMessenger
{
    const MAIN_CONTAINER = 'SESSION_MESSENGER';
    
    const ERRORS_NAMESPACE = 'ERRORS';
    
    const SUCCESS_NAMESPACE = 'SUCCESS';
    
    const NOTICE_NAMESPACE = 'NOTICE';
    
    private static $_allowedNamespaces = array (
        self::ERRORS_NAMESPACE,
        self::SUCCESS_NAMESPACE,
        self::NOTICE_NAMESPACE
    );
    
    public function __construct()
    {
        if (!self::$_session instanceof Zend_Session_Namespace) {
            self::$_session = new Zend_Session_Namespace(self::MAIN_CONTAINER);
        }
        // setting default namespace
        $this->_namespace = self::NOTICE_NAMESPACE;
    }
    
    public function setNamespace($namespace = 'NOTICE')
    {
        if (!is_string($namespace) || $namespace == '' 
            || !in_array($namespace, self::$_allowedNamespaces)) 
        {
            $namespace = $this->getNamespace();
        }
        
        $this->_namespace = $namespace;
        return $this;
    }
    
    /**
     * addMessage() - Add a message to session message
     *
     * @param  mixed $message
     * @return Zend_Controller_Action_Helper_FlashMessenger Provides a fluent interface
     */
    public function addMessage($message, $namespace = null)
    {
        if (!is_string($namespace) || $namespace == '' 
            || !in_array($namespace, self::$_allowedNamespaces)) 
        {
            $namespace = $this->getNamespace();
        }
        
        if (!is_array(self::$_session->{$namespace})) {
            self::$_session->{$namespace} = array();
        }

        self::$_session->{$namespace}[] = $message;
        self::$_messageAdded = true;

        return $this;
    }
    
    /**
     * Returns allowed namespaces where users can write data
     * 
     * @return array 
     */
    public function getAllowedNamespaces()
    {
        return self::$_allowedNamespaces;
    }
    
    /**
     * Clear data from current namespace
     * 
     * If set to null, than clear from all namespaces
     * 
     * @param string $namespace 
     */
    public function clearCurrentMessages($namespace = null)
    {
        if (!is_string($namespace) || $namespace == '' 
            || !in_array($namespace, self::$_allowedNamespaces)) 
        {
            $namespace = $this->getAllowedNamespaces();
        }
        
        if(!is_array($namespace)) { $namespace = array($namespace); }
        
        foreach($namespace as $curNamespace) {
            if ($this->hasCurrentMessages($curNamespace)) {
                unset(self::$_session->{$curNamespace});
            }
        }
    }
}