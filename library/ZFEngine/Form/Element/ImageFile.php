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
 * @package    ZFEngine_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Form_Element_File */
require_once 'Zend/Form/Element/File.php';

/**
 * Element allows upload file without $_FILES
 *
 * @category   ZFEngine
 * @package    ZFEngine_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Form_Element_ImageFile extends Zend_Form_Element_File
{
    /**
     * Set transfer adapter
     *
     * @param  string|Zend_File_Transfer_Adapter_Abstract $adapter
     * @return Zend_Form_Element_File
     */
    public function setTransferAdapter($adapter)
    {
        if ($adapter instanceof Zend_File_Transfer_Adapter_Abstract) {
            $this->_adapter = $adapter;
        } elseif (is_string($adapter)) {
            if ($adapter == 'HttpBase64') {
                $this->_adapter = new ZFEngine_File_Transfer_Adapter_HttpBase64();
            } else {
                $loader = $this->getPluginLoader(self::TRANSFER_ADAPTER);
                $class  = $loader->load($adapter);
                $this->_adapter = new $class;
            }
        } else {
            require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception('Invalid adapter specified');
        }

        foreach (array('filter', 'validate') as $type) {
            $loader = $this->getPluginLoader($type);
            $this->_adapter->setPluginLoader($loader, $type);
        }

        return $this;
    }

    /**
     * Get transfer adapter
     *
     * Lazy loads HTTP transfer adapter when no adapter registered.
     *
     * @param  string $type
     * @return Zend_File_Transfer_Adapter_Abstract
     */
    public function getTransferAdapter($type = 'Http')
    {
        if (null === $this->_adapter) {
            $this->setTransferAdapter($type);
        }

        return $this->_adapter;
    }

    /**
     * Set file for Http Base64 adapter
     *
     * @param  string $file
     * @param  string $name
     * @return Zend_Form_Element_File
     */
    public function setValue($file = NULL, $name = NULL)
    {
        if ($file == NULL || $name == NULL) {
            return $this;
        }
        $adapter = $this->getTransferAdapter();
        if ($adapter instanceof ZFEngine_File_Transfer_Adapter_HttpBase64) {
            $adapter->setFile($file, $name);
            return true;
        } else {
            $methods = get_class_methods($this);
            $results = array();
            //retrive results of all getters
            foreach ($methods as $method) {
                if (preg_match('/^set(.*)/', $method, $matches)) {
                    //array with getters which will be skipped
                    $notUse = array('PluginLoader', 'Value', 'TransferAdapter', 'Attrib','Validator');
                    $methodGet = 'get'.$matches[1];
                    if (method_exists($this, $methodGet) && !in_array($matches[1], $notUse)) {
                        $results[$method] = call_user_method($methodGet, $this);
                    }
                    //remove Zend_Validate_File_Upload validator
                    if ($methodGet == 'getValidators') {
                        unset($results[$method]['Zend_Validate_File_Upload']);
                    }
                }
            }
           
            $this->setTransferAdapter('HttpBase64');
            $adapter = $this->getTransferAdapter();
            $adapter->setFile($file, $name);
            //set results of all getters
            foreach ($results as $key => $value) {
                call_user_method($key, $this, $value);
            }
        }
        return $this;
    }

    /**
     * Validate upload
     *
     * @param  string $value   File, can be optional, give null to validate all files
     * @param  mixed  $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        if ($this->_validated) {
            return true;
        }

        if ($value) {
            $this->setValue($value, $this->getName());
        }
        
        $adapter    = $this->getTransferAdapter();
        $translator = $this->getTranslator();
        if ($translator !== null) {
            $adapter->setTranslator($translator);
        }

        if (!$this->isRequired()) {
            $adapter->setOptions(array('ignoreNoFile' => true), $this->getName());
        } else {
            $adapter->setOptions(array('ignoreNoFile' => false), $this->getName());
            if ($this->autoInsertNotEmptyValidator() and
                   !$this->getValidator('NotEmpty'))
            {
                $validators = $this->getValidators();
                $notEmpty   = array('validator' => 'NotEmpty', 'breakChainOnFailure' => true);
                array_unshift($validators, $notEmpty);
                $this->setValidators($validators);
            }
        }

        if($adapter->isValid($this->getName())) {
            $this->_validated = true;
            return true;
        }

        $this->_validated = false;
        return false;
    }
}