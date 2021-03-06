<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: View.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Abstract master class for extension.
 */
require_once 'Zend/View/Abstract.php';


/**
 * Concrete class for handling view scripts.
 *
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZFEngine_View extends Zend_View_Abstract
{

    /**
     * Script file name to execute
     *
     * @var string
     */
    private $_file = null;

    /**
     * Constructor
     *
     * @param  array $config
     * @return void
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    
    /**
     * Finds a view script from the available directories.
     *
     * @param $name string The base name of the script.
     * @return void
     */
    protected function _script($name, $separator = '/')
    {
    	if($separator == '/') {
			$parts = explode($separator, $name);
			if (count($parts) < 2) {
			 	$this->_script($name, DIRECTORY_SEPARATOR);
			}
		}else {
			$parts = explode($separator, $name);
		}
    	
    	
        if (count($parts) > 2) {
            $options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
            $type = $parts[0]; unset($parts[0]);
            $module = $parts[1]; unset($parts[1]);

            if ($type == 'helper') {
                $scriptsDirectory = DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'scripts';
                $type = $module;
                $has = true;
                $module = array_shift($parts);
            } else {
                $scriptsDirectory = DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts';
            }

        
            switch ($type) {
                case 'module':
                    $path = $options['resources']['frontController']['moduleDirectory'] . DIRECTORY_SEPARATOR
                        . $module . $scriptsDirectory . DIRECTORY_SEPARATOR
                        . implode(DIRECTORY_SEPARATOR, $parts);
                    break;
                case 'zfengine':
                    // foo-bar -> FooBar
                    $module = implode('', array_map('ucfirst', explode('-', $module)));
                    $path = $options['includePath']['library'] . DIRECTORY_SEPARATOR
                        . 'ZFEngine' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR
                        . $module . $scriptsDirectory. DIRECTORY_SEPARATOR
                        . implode(DIRECTORY_SEPARATOR, $parts);
                    break;
                default:
                    break;
            }
            /*echo $path;
        	if(stripos($name, 'cities-select.phtml') !== false ) {
            	echo file_exists($path); die;
        	}*/
        			
            if (isset($path)) {
                if (($realpath = realpath($path)) == false) {
                    $e = new Zend_View_Exception( "script '$name' not found in path (" . $path . ")");
                    $e->setView($this);
                    throw $e;
                }

                return $realpath;
            }
        }

        return parent::_script($name);
    }


    /**
     * Includes the view script in a scope with only public $this variables.
     *
     * @param string The view script to execute.
     */
    protected function _run()
    {
        include func_get_arg(0);
    }

    /**
    * Render a partial template, which is essentially
    *
    * @param string $name
    * @param array $localScopeVariables
    *   A keyed array of variables that will appear in the output.
    *
    * @return
    *   The output generated by the template.
    */
    public function part($name, $localScopeVariables)
    {
        // find the script file name using the parent private method
        $this->_file = $this->_script($name);
        unset($name); // remove $name from local scope
        extract($localScopeVariables, EXTR_SKIP);  // Extract the variables to a local namespace
        unset($localScopeVariables); // remove $variables from local scope
        ob_start();
        include $this->_file;
        return ob_get_clean(); // output
    }
}
