<?php
/**
 * Defined APPLICATION_ENV, APPLICATION_PATH 
 */

if (file_exists("common.local.php")) {
    define("APPLICATION_ENV", "development");
} else {
    define("APPLICATION_ENV", "production");
}

define("APPLICATION_PATH", realpath(dirname(__FILE__) . "/../application"));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . "/../library"),
    get_include_path(),
)));

/**
 * composer autoloader
 * ACHTUNG!!!
 * Need change implementation to another, because this loader also try to load
 * classes from system. 
 */
require "vendor/autoload.php";
require_once "Zend/Application.php";

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . "/configs/application.ini"
);

$application->getBootstrap()->bootstrap();

/**
 * Main CLI abstract class
 */
abstract class Task {
    protected $_db;
    protected $_view;
    protected $_lockedFileResource;
            
    /**
     * Constructor
     */
    public function __construct() {
        $this->_db = Zend_Db_Table::getDefaultAdapter();
        $this->_db->setFetchMode(Zend_Db::FETCH_ASSOC);
    }

    /**
     * Execute task
     */
    public function exec() {
        $this->_startExecution(); 
    }
    
    /**
     * Opening and locking file
     * @throws Exception
     */
    protected function _startExecution() {
        $file = $this->_getLockedFileName();
        $this->_lockedFileResource = fopen($file, 'w'); 
        
        if (!is_resource($this->_lockedFileResource)) {
            throw new Exception('Can not open file');
        }
        
        if (!flock($this->_lockedFileResource, LOCK_EX)) {
            // file apparently locked by enother script
            throw new Exception('Can not lock file');
        } 
    }
    
    /**
     * Must return full locking file name path
     * @return string 
     */
    abstract protected function _getLockedFileName();

    /**
     * Unlocking and deleting file
     */
    public function __destruct() {
        if (is_resource($this->_lockedFileResource)) {
            flock($this->_lockedFileResource, LOCK_UN);
            @unlink($this->_getLockedFileName());
        }
    }
    
    /**
     * Retrieve view object
     * @return Zend_View_Interface|null
     */
    public function getView() {
        if (null === $this->_view) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->_view = $viewRenderer->view;
        }

        return $this->_view;
    }

    /**
     * Get url
     * @param $url
     * @param array $params
     */
    public function url($url, $params=array()) {
        return $this->getView()->url($url, $params);
    }

    /**
     * Run external command
     * @param string $cmd
     * @return int return code
     */
    protected function _runExternal($cmd) {
        $descriptors = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $pipes = array();
        $process = proc_open($cmd, $descriptors, $pipes);

        if (!is_resource($process)) {
            return false;
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $code = proc_close($process);

        return $code;
    }

    /**
     * Run external command and get output
     * @param string $cmd
     * @param $code
     * @return string
     */
    protected function _getOutput($cmd, &$code) {
        $descriptors = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $pipes = array();
        $process = proc_open($cmd, $descriptors, $pipes);

        if (!is_resource($process)) {
            return false;
        }

        $data = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $code = proc_close($process);

        return $data;
    }
}