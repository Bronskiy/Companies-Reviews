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
 * Resource for initializing the view helpers
 *
 * @uses        ZFEngine_Application_Exception
 * @category    ZFEngine
 * @package     ZFEngine_Application
 * @copyright   Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license     http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Application_Resource_View extends Zend_Application_Resource_View
{
    protected $_helpers = array();

    /**
     * Setup view helpers
     *
     * @return Zend_View
     */
    public function init()
    {
        $options = $this->getOptions();
        
        if (isset($options['helpers'])) {
            $this->setupViewHelpers($options['helpers']);
            unset ($options['helpers']);
        }

        return parent::init();
    }

    /**
     * Retrieve view object
     *
     * @return Zend_View
     */
    public function getView()
    {
        if (null === $this->_view) {
            $options = $this->getOptions();
            $this->_view = new ZFEngine_View($options);

            if(isset($options['doctype'])) {
                $this->_view->doctype()->setDoctype(strtoupper($options['doctype']));
            }
        }
        return $this->_view;
    }

    /**
     * Setup view helpers
     *
     * @param array $options
     * @return ZFEngine_Application_Resource_View
     */
    public function setupViewHelpers(array $options)
    {
        $view = $this->getView();

        if (is_array($options)) {
            // collect settings in one structure
            foreach ($options as $helper => $settings) {
                if (is_string($settings)) {
                    // one setting

                    // method name = helper name
                    $call[$helper][][$helper][] = $settings;
                } elseif (is_array($settings)) {
                    // several settings
                    if (isset($settings[0])) {
                        // one method
                        foreach ($settings as $methods) {
                            foreach($methods as $method => $data) {
                                if (is_string($data)) {
                                    $data = array($data);
                                }
                                $call[$helper][][$method] = $data;
                            }
                        }
                    } else {
                        // several methods
                        foreach ($settings as $method => $params) {
                            if (is_string($params)) {
                                // one method call and one argumert
                                $call[$helper][][$method][] = $params;
                            } elseif (is_array($params)) {
                                if (isset($params[0])) {
                                    // multiple method calls
                                    foreach($params as $data) {
                                        $call[$helper][][$method] = $data;
                                    }
                                } else {
                                    // call method with multiple arguments
                                    $call[$helper][][$method] = $params;
                                }
                            }
                        }
                    }
                }
            }

            // initialization helpers
            foreach($call as $helper => $methods) {
                $obj = $view->getHelper($helper);
                for ($i = 0; $i < count($methods); $i++) {
                    foreach($methods[$i] as $method => $params) {
                        call_user_func_array(array($obj, $method), $params);
                    }
                }
            }
        }

        return $this;
    }

}