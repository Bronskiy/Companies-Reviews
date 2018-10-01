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
 * Hidden value with hash protect
 *
 * @category   ZFEngine
 * @package    ZFEngine_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Multiselect.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class ZFEngine_Form_Element_Value extends Zend_Form_Element_Hidden
{

    /**
     *
     * @var string
     */
    static $_salt = 'value_salt'; // @todo get from config

    /**
     * Use formValue view helper by default
     * @var string
     */
    public $helper = 'formValue';

    /**
     *
     * @param mixed $value
     * @param array $context
     */
    public function isValid($value, $context = null) {
        $hash = self::getHash($this->getName(), $value);
        if ($hash != $context[$this->getName() . '_check']) {
            $this->addError('Форма была изменена');
        }
        return parent::isValid($value, $context);
    }

    /**
     * Убираем по дефолту лишние декораторы
     */
    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();
        $this->removeDecorator('HtmlTag');
        $this->removeDecorator('Label');
    }

    /**
     *
     * @param string $name
     * @param string $value
     * @param string $salt
     * @return string
     */
    static function getHash($name, $value, $salt = null)
    {
        if ($salt) {
            $salt = self::$salt;
        }
        return md5($name . $value . $salt);
    }

}
