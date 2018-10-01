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
 * 
 *
 * @category   ZFEngine
 * @package    ZFEngine_Controller
 * @subpackage Action_Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Controller_Action_Helper_IfModifiedSince extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Проверяет значение переменной сервера HTTP_IF_MODIFIED_SINCE, и если были изменения - возращает true, иначе - false
     *
     * @return boolean
     **/
    public function check($last_modified)
    {
        $last_modified = gmdate('D, d M Y H:i:s', strtotime($last_modified)) . ' GMT';
        // отправил ли браузер запрос if-modified-since request?
        if ($this->getRequest()->getServer('HTTP_IF_MODIFIED_SINCE')) {
            // разобрать заголовок
            $if_modified_since = preg_replace('/;.*$/', '', $this->getRequest()->getServer('HTTP_IF_MODIFIED_SINCE'));

            if ($if_modified_since == $last_modified) {
                // кэш браузера до сих пор актуален, прекращаем генерацию фида
                $this->getResponse()->setRawHeader('HTTP/1.0 304 Not Modified');
                $this->getResponse()->setHeader('Cache-Control', 'max-age=86400, must-revalidate');
                $this->getResponse()->sendHeaders();
                return false;
            }
        }
        // устанавливаем время жизни кеша для браузера
        $this->getResponse()->setHeader('Cache-Control', 'max-age=86400, must-revalidate');
        $this->getResponse()->setHeader('Last-Modified', $last_modified);
        return true;
    }

}