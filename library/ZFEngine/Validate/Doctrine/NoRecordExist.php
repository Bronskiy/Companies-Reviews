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
 * @subpackage Doctrine
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Doctrine. Confirms a record does not exist in a table.
 *
 * @category   ZFEngine
 * @package    ZFEngine_Validate
 * @subpackage Doctrine
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Validate_Doctrine_NoRecordExist extends Zend_Validate_Abstract
{
    /**
     * Error constants
     */
    const ERROR_RECORD_FOUND    = 'recordFound';
    const CUSTOM    = 'custom';

    /**
     * Message templates
     * @var array 
     */
    protected $_messageTemplates = array(self::ERROR_RECORD_FOUND    => 'A record matching %value% was found');

    /**
     * Table name
     * @var string
     */
    protected $_table = '';

    /**
     * Field name
     * @var string
     */
    protected $_field = '';

    /**
     * Basic configuration
     *
     * @param string $table
     * @param string $field
     */
    public function __construct($table, $field, $customMessage = false)
    {
        $this->_table   = (string) $table;
        $this->_field   = (string) $field;
        if ($customMessage != false) {
            $this->_messageTemplates[self::CUSTOM] = $customMessage;
        }
    }

    /**
     * Validation
     *
     * @param string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $query = Doctrine_Query::create()
            ->from($this->_table . ' t')
            ->where('t.' . $this->_field . ' = ?', $value);
        $result = $query->fetchOne();

        $valid = true;

        if ($result) {
            $valid = false;
            if (isset($this->_messageTemplates[self::CUSTOM])) {
                $this->_error(self::CUSTOM);
            } else {
                $this->_error(self::ERROR_RECORD_FOUND);
            }
        }

        return $valid;
    }
    
}