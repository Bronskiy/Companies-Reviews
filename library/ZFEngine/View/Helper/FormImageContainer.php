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
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';


/**
 * Helper to generate a image container element
 *
 * @category   ZFEngine
 * @package    ZFEngine_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Multiselect.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class ZFEngine_View_Helper_FormImageContainer extends Zend_View_Helper_FormElement
{
    /**
     * Generates a 'image container' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formImageContainer($name, $value = null, $attribs = null)
    {
        $info    = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // build the element

        $xhtml = '<img';

        // add a value if one is given
        if (!empty($value)) {
            $xhtml .= ' src="' . $this->view->escape($value) . '"';
        }

        // add attributes and close start tag
        $xhtml .= $this->_htmlAttribs($attribs) . '/>';

        return $xhtml;
    }
}
