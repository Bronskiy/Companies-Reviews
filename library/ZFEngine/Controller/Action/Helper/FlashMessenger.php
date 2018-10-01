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
 * @package    ZFEngine_Controller
 * @subpackage Action_Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Flash Messenger - implement simple/session-based messages
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @category   ZFEngine
 * @package    ZFEngine_Controller
 * @subpackage Action_Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */

class ZFEngine_Controller_Action_Helper_FlashMessenger extends Zend_Controller_Action_Helper_FlashMessenger
{

    const INFO        = 'info';
    const WARNING     = 'warning';
    const ERROR       = 'error';
    const SUCCESS     = 'success';
    
    /**
     * Messages from this request
     *
     * @var array
     */
    static protected $_currentlyMessages = array();


    /**
     * Strategy pattern: proxy to addMessage()
     *
     * @param  string $message - message text
     * @param  string $type - type of message
     * @param  string $showNow  - true: show in this response; false: show on the next page (or after redirect)
     * @return void
     */
    public function direct($message, $type = 'info', $showNow = false)
    {
        return $this->addMessage($message, $type, $showNow);
    }

    /**
     * Add a message
     *
     * @param  string $message - message text
     * @param  string $type - type of message
     * @param  string $showNow  - true: show in this response; false: show on the next page (or after redirect)
     * @return ZFEngine_Controller_Action_Helper_FlashMessenger Provides a fluent interface
     */
    public function addMessage($message, $type = 'info', $showNow = false)
    {
        switch ($type) {
            case self::INFO:
            case self::ERROR:
            case self::SUCCESS:
            case self::WARNING:
                break;
            default:
                $type = self::INFO;
        }

        if ($showNow) {
            self::$_currentlyMessages[] = array($type => $message);
        } else {
            parent::addMessage(array($type => $message));
        }
        return $this;
    }

    /**
     * Get messages
     * 
     * @return array
     */
    public function getMessages()
    {
        return array_merge(self::$_currentlyMessages, parent::getMessages());
    }

    /**
     * Add messages
     *
     * @param array $messages
     * @return ZFEngine_Controller_Action_Helper_FlashMessenger
     */
    public function addMessages($messages, $showNow = false)
    {
        if ($showNow) {
            self::$_currentlyMessages = $messages;
        } else {
            if (self::$_messageAdded === false) {
                self::$_session->setExpirationHops(1, null, true);
            }

            self::$_session->{$this->_namespace} = array_merge($messages, parent::getMessages());
        }

        return $this;
    }
}