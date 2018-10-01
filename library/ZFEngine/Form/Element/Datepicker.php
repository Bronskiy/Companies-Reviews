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

/**
 * Data container for form
 *
 * @category   ZFEngine
 * @package    ZFEngine_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Multiselect.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class ZFEngine_Form_Element_Datepicker extends Zend_Form_Element_Text
{
    /**
     * Набор опций
     * @var array
     */
    protected $_option = array();

    /**
     * init hook
     */
    public function init() {
        // Цепляем фильтр
        $this->addFilter('StringTrim');
        // Цепляем валидатор на дату для элемента
        $this->addValidator(new Zend_Validate_Date());
        $this->setWidgetOption('changeYear', 'true');
        $this->setWidgetOption('changeMonth', 'true');
        parent::init();
    }

    /**
     * Установка опций
     * @param string $name
     * @param string $value
     * @return ZFEngine_Form_Element_Datepicker
     */
    public function setWidgetOption($name, $value)
    {
        $this->_option[$name] = $value;
        return $this;
    }
    
    /**
     * Извлечение опций
     * @return array
     */
    public function getWidgetOptions()
    {
        return $this->_option;
    }
    
    /**
     * Извлечение опций
     * @return string
     */
    protected function _getWidgetOptionsAsJS()
    {
        $result = '{';
        foreach ($this->_option as $name=>$value) {
            $result .= $name . ': ' . $value . ',' ;
        }
        $result = (strlen($result)>1)? substr($result, 0, strlen($result)-1): $result ;
        $result .= '}';
        return $result;
    }

    /**
     * Рендер елемента для выбора даты
     *
     * @param Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        $this->_render($view);
        return parent::render($view);
    }

    /**
     * Рендер елемента для выбора даты
     * @param string $view
     */
    protected function _render($view)
    {
        if (!$this->getAttrib('disable') && !$this->getAttrib('readonly')) {
            $translate = Zend_Registry::get(Main_Translate_Abstract::REGISTRY_KEY);
            $lang = $translate->getLocale();
            $this->setAttrib('readonly', null);
            $this->getView()->headScript()->appendFile('/js/datepicker/jquery-ui-1.8.20.custom.min.js')
                                          ->appendFile('/js/i18n/jquery.ui.datepicker-'.$lang.'.js');
            $this->getView()->headLink()->appendStylesheet('/js/datepicker/css/ui-lightness/jquery-ui-1.8.20.custom.css');

$script = <<<JS
jQuery(document).ready(function(){
    var {$this->getName()} = jQuery('#{$this->getName()}');
    jQuery({$this->getName()}).datepicker("option", "currentText", jQuery({$this->getName()}).val() );
    jQuery({$this->getName()}).datepicker({$this->_getWidgetOptionsAsJS()});
    jQuery({$this->getName()}).datepicker($.datepicker.regional['$lang']);
});
JS;
            $this->getView()->headScript()->appendScript($script);
        }

    }

}
