<?php 
/**
 * Multilingual decorator for route objects
 * 
 * @author domencom
 */
class Main_Controller_Router_Route_MultilingualDecorator extends Zend_Controller_Router_Route_Abstract
{
	
    /**
     * URI delimiter
     */
    const URI_DELIMITER = '/';

    /**
     * Delegate
     * @var Zend_Controller_Router_Route_Abstract
     */
    protected $_route = null;

    /**
     * Language key
     * @var string
     */
    protected $_languageKey = 'lang';
    
    /**
     * Constructor
     * 
     * @param Zend_Controller_Router_Route_Abstract $route
     */
    public function __construct(Zend_Controller_Router_Route_Abstract $route)
    {
        $this->_route = $route;

        $config = new Zend_Config_Ini(
                APPLICATION_PATH . '/configs/application.ini',
                'production');

        if(@$config->languages->langKey) {
            $this->_languageKey = $config->languages->langKey;
        }

    }
	
    /**
     *
     *@see library/Zend/Controller/Router/Route/Zend_Controller_Router_Route_Interface::match()
     */
    public function match($path, $partial = false)
    {
        if(!$partial) {
            $path = trim($path, self::URI_DELIMITER);
        } else {
            $matchedPath = $path;
        }

        $aPath    = explode(self::URI_DELIMITER, $path);
        $langInfo = array();

        if (strlen($aPath[0]) == 2) {
            $langInfo = array($this->_languageKey => array_shift($aPath));
            $path = implode(self::URI_DELIMITER, $aPath);
        }

        if($matches = $this->_route->match($path)) {
            return $matches + $langInfo;
        }
        return false;
    }
	
	/**
	 * 
	 * @see library/Zend/Controller/Router/Route/Zend_Controller_Router_Route_Interface::assemble()
	 */
    public function assemble($data = array(), $reset = false, $encode = false)
    {
    	$language = '';

    	if(array_key_exists($this->_languageKey, $data)) {
            $language = $data[$this->_languageKey];
            unset($data[$this->_languageKey]);
    	}

    	$url = $this->_route->assemble($data, $reset, $encode);
    	$url = ltrim($language . '/' . $url, self::URI_DELIMITER);
    	return $url;
    }
    
    public static function getInstance(Zend_Config $config)
    {
    	throw new Exception('MultiligualDecorator doesn\'t provide method ' . __METHOD__);
    }
    
    /**
     * 
     * @see library/Zend/Controller/Router/Route/Zend_Controller_Router_Route_Abstract::getVersion()
     */
    public function getVersion()
    {
        return 1;
    }
    
    /**
     * Magic method
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
    	return $this->_route->$name();
    } 
}