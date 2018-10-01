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
 * Set META-title of page and create variable 'title' in the view
 *
 * @category   ZFEngine
 * @package    ZFEngine_View
 * @subpackage    Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_View_Helper_SetTitle extends Zend_View_Helper_Abstract
{

    /**
     * Set META-title of page and create variable 'title' in the view
     *
     * @param string $formatedString
     * @param array|NULL $variables
     * @return string
     */
    public function setTitle($toFormatString, $variables = NULL)
    {
        $title = $this->view->translate($toFormatString);
        if ($variables) {
            $title = vsprintf($title, $variables);
        }
        $this->view->headTitle($title, Zend_View_Helper_Placeholder_Container_Abstract::SET);
        $this->view->title = $title;

        return $title;
    }
}