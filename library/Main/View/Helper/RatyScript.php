<?php

/**
 * Generate raty script
 */
class Zend_View_Helper_RatyScript extends Zend_View_Helper_Abstract
{
    protected $_options = array('path' => '/images/raty', 'element'=>'#star');

    public function ratyScript(array $options = array(), $pathPrepend=null)
    {
        $this->mergeOptions($options);

        if ($pathPrepend !== null) {
            $this->_options['path'] = $pathPrepend . '/images/banner/stars';
        }

        return $this;
    }
    
    public function mergeOptions($options) 
    {
        $this->_options = array_merge($this->_options, $options);
    }
    
    public function setOptions(array $options)
    {
        $this->_options = $options;
    }
    
    public function render($return = false)
    {
        $script = "$(function(){" . PHP_EOL . "$('".$this->_options['element']
                   ."').raty({" . PHP_EOL;
        
        unset($this->_options['element']);
        
        $params = array();
        foreach ($this->_options as $name => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            if (!is_bool($value) && strpos($value, 'function') === FALSE) {
                $value = '"' . $value . '"';
            }
            $params[] = $name . ': ' . $value;
        }
        $params[] = "hints: ['', '', '', '', '']";
        $script .= implode(',' . PHP_EOL, $params) . PHP_EOL;

        $script .= '});});';

        if ($return) {
            return $script;
        } else {
            $this->view->headScript(Zend_View_Helper_HeadScript::SCRIPT, $script)->toString();
        }
    }
}