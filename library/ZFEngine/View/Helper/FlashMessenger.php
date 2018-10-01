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
 * @package    ZFEngine_View
 * @subpackage    Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Render flash messages
 *
 * @category   ZFEngine
 * @package    ZFEngine_View
 * @subpackage    Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_View_Helper_FlashMessenger extends Zend_View_Helper_Abstract
{
    /**
     * @var ZFEngine_Controller_Action_Helper_FlashMessenger
     */
    private $_flashMessenger = null;

    /**
     * Render flash messages
     *
     * @return string Flash messages formatted for output
     */
    public function flashMessenger()
    {
        $flashMessenger = $this->_getFlashMessenger();

        $messages = $flashMessenger->getMessages();
        if (count($messages) == 0) {
            return;
        }
        $output ='<ul class="flash-messages">';
        
        //process messages
        foreach ($messages as $type=>$message){
            if (is_array($message)) {
                list($type, $message) = each($message);
            }
            $output .= sprintf('<li class="%s">%s</li>', $type, $message);
        }
        
        return $output . '</ul>';
    }

    /**
     * Lazily fetches FlashMessenger Instance.
     *
     * @return ZFEngine_Controller_Action_Helper_FlashMessenger
     */
    public function _getFlashMessenger()
    {
        if (null === $this->_flashMessenger) {
            $this->_flashMessenger =
                Zend_Controller_Action_HelperBroker::getStaticHelper(
                    'FlashMessenger');
        }
        return $this->_flashMessenger;
    }
}