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
class ZFEngine_Form_Element_Datetimepicker extends ZFEngine_Form_Element_Datepicker
{

    /**
     * init hook
     */
    public function init() {

        parent::init();
    }


    protected function _render($view)
    {
        if (!$this->getAttrib('disable') && !$this->getAttrib('readonly')) {
            $translate = Zend_Registry::get(Main_Translate_Abstract::REGISTRY_KEY);
            $lang = $translate->getLocale();
            $this->setAttrib('readonly', null);
            $this->getView()->headScript()->appendFile('/js/datepicker/jquery-ui-1.8.20.custom.min.js')
                                        ->appendFile('/js/datepicker/jquery.ui.datetimepicker.min.js')
                                        ->appendFile('/js/i18n/jquery.ui.datepicker-'.$lang.'.js');
            $this->getView()->headLink()->appendStylesheet('/js/datepicker/css/ui-lightness/jquery-ui-1.8.20.custom.css')
                                        ->appendStylesheet('/css/ui/datetimepicker.css');

$script = <<<JS
jQuery(document).ready(function(){
    var {$this->getName()} = jQuery('#{$this->getName()}');
    jQuery({$this->getName()}).datetimepicker("option", "currentText", jQuery({$this->getName()}).val() );
    jQuery({$this->getName()}).datetimepicker({$this->_getWidgetOptionsAsJS()});
    jQuery({$this->getName()}).datetimepicker($.datepicker.regional['$lang']);
});
JS;
            $this->getView()->headScript()->appendScript($script);
        }
    }

}
