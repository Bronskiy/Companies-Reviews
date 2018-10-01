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
 * @package    ZFEngine_Validate
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Validator to compare values of two fields
 *
 * @category   ZFEngine
 * @package    ZFEngine_Validate
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Validate_InputEquals extends Zend_Validate_Abstract
{

    /**
     * Error constant
     */
    const NOT_MATCH = 'notMatch';

    /**
     * Message templates
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Значения не совпадают'
    );

    /**
     * @var string
     */
    protected $_contextKey = null;

    /**
     * Constructor
     *
     * @param string $key Field name with value wich to compare
     * @return void
     */
    public function __construct($key = 'passwordConfirm', $notMatchMessage = null)
    {
        $this->_contextKey = $key;
        
        if ($notMatchMessage) {
            $this->_messageTemplates[self::NOT_MATCH] = $notMatchMessage;
        }
    }

    /**
     * Validator
     *
     * @param string $value Field value
     * @param string|array $context All form values
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        if (is_array($context)) {
            if ( array_key_exists($this->_contextKey, $context) && ($value == $context[ $this->_contextKey]) ) {
                return true;
            }
        } elseif ( is_string($context) && ($value == $context) ) {
            return true;
        }

        $this->_error(self::NOT_MATCH);

        return false;
    }
}