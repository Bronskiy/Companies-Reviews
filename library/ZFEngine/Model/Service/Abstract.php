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
 * @package    ZFEngine_Model
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Abstract service for model which does not use database
 *
 * @category   ZFEngine
 * @package    ZFEngine_Model
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
abstract class ZFEngine_Model_Service_Abstract
{

    const MESSAGE_INFO          = 'info';
    const MESSAGE_WARNING   = 'warning';
    const MESSAGE_ERROR       = 'error';
    const MESSAGE_SUCCESS     = 'success';
    
    /**
     * @var Zend_View_Interface
     */
    protected $_view;

    /**
     * @var array
     */
    protected $_messages = array();

    /**
     * @var 
     */
    private $_forms = array();


    public function  __construct()
    {
        $this->init();
    }

    public function init()
    {
        // init form code
    }

    public function __get($fieldName)
    {
        $type = $by = '';
        if (substr($fieldName, 0, 4) == 'form') {
            $by = substr($fieldName, 4, strlen($fieldName));
            $type = 'form';
        }

        switch ($type) {
            case 'form':
                if (empty($this->_forms)) {
                    $this->_parseFormDocProperty($this);
                }
                if (!$this->_hasFormInstance($by) && !$this->_formInitialize($by)) {
                    return false;
                }
                return $this->_getFormInstance($by);
                break;
            default:
                throw new Exception(sprintf($this->getView()->translate("Property %s not found"), $type));
                break;
        }
    }
    
    public function __set($fieldName, $value)
    {
        $type = $by = '';
        if (substr($fieldName, 0, 4) == 'form') {
            $by = substr($fieldName, 4, strlen($fieldName));
            $type = 'form';
        }

        switch ($type) {
            case 'form':
                if ($value instanceof Zend_Form) {
                    $this->_setFormInstance($by, $value);
                }
                break;
            default:
                throw new Exception(sprintf($this->getView()->translate("Property %s not found"), $type));
                break;
        }
    }

    /**
     * Find parenting classes
     *
     * @param object|string $currentClass
     */
    protected function _parseFormDocProperty($currentClass, $last = false)
    {
        if ($currentClass instanceof  Zend_Reflection_Class) {
            $currentClass = $currentClass->getName();
        }
        $class = new Zend_Reflection_Class($currentClass);
        try {
            $doc = $class->getDocblock();
        } catch (Exception $exc) {
            $doc = false;
        }

        if ($doc != false) {
            foreach ($doc->getTags('property') as $property) {
                $part = explode(' ', $property->getDescription());
                if (substr($part[1], 1, 4) != 'form') {
                    continue;
                } else {
                    $name = substr($part[1], 5, strlen($part[1]));
                    $fullName = $part[0];
                    if (!isset($this->_forms[$name])) {
                        $this->_forms[$name]['fullName'] = $fullName;
                    }
                }
            }
        }
        $parentClass = $class->getParentClass();
        if (!$parentClass->isAbstract()) {
            $this->_parseFormDocProperty($parentClass, $last);
        }
    }


    protected function _hasFormInstance($name)
    {
        if (isset($this->_forms[$name]['instance'])) {
            return true;
        } else {
            return false;
        }
    }

    protected function _getFormInstance($name)
    {
        if (isset($this->_forms[$name]['instance'])) {
            return $this->_forms[$name]['instance'];
        } else {
            return false;
        }
    }

    protected function _setFormInstance($name, $form)
    {
        if (!empty($name) && is_object($form)) {
            $this->_forms[$name]['instance'] = $form;
        }
        return  $this;
    }


    /**
     * Создание формы
     *
     * @return boolean
     */
    protected function _formInitialize($name)
    {
        if (isset($this->_forms[$name]['fullName'])) {
            $class = $this->_forms[$name]['fullName'];
            $reflectionObj = new ReflectionClass($class);
            
            $parameters = $reflectionObj->getMethod('__construct')->getParameters();
            if (count($parameters) > 1) {
                $this->_setFormInstance($name, $reflectionObj->newInstanceArgs(array($this)));
            } else {
                $this->_setFormInstance($name, new $class);
            }
            return true;
        } else {
            return false;
        }
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
            $this->setView($viewRenderer->view);
        }

        return $this->_view;
    }

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return ZFEngine_Service_Abstract
     */
    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    public function addMessage($message, $type = self::MESSAGE_INFO)
    {
        switch ($type) {
            case self::MESSAGE_INFO:
            case self::MESSAGE_ERROR:
            case self::MESSAGE_SUCCESS:
            case self::MESSAGE_WARNING:
                break;
            default:
                $type = self::MESSAGE_INFO;
        }
        $this->_messages[] = array($type => $message);
        return $this;
    }

    /**
     * Get messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
