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
 * Truncate teaser
 *
 * @category   ZFEngine
 * @package    ZFEngine_View
 * @subpackage    Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_View_Helper_Truncate extends Zend_View_Helper_Abstract
{
    /**
     * Truncate teaser
     *
     * @param string $string input string
     * @param integer $length lenght of truncated text
     * @param string $postfix end string
     * @param boolean $break_words truncate at word boundary
     * @param boolean $middle truncate in the middle of text
     * @return string truncated string
     */
    public function truncate($string, $length = 300, $postfix = '...',  $breakWords = false, $middle = false)
    {
        $matches = array();
        if (mb_strlen($string, 'UTF-8') > $length) {
            $length -= min($length, strlen($postfix));
            if (!$breakWords && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/Ui', '', mb_substr($string, 0, $length+1,'UTF-8'));
            }
            if(!$middle) {
                return mb_substr($string, 0, $length, 'UTF-8') . $postfix;
            } else {
                return mb_substr($string, 0, $length/2, 'UTF-8') . $postfix . mb_substr($string, -$length/2, 'UTF-8');
            }
        } else {
            return $string;
        }
    }
}