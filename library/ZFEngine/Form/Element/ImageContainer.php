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
/** Zend_Form_Element_Select */
require_once 'Zend/Form/Element/Hidden.php';

/**
 * Image container for form
 *
 * @category   ZFEngine
 * @package    ZFEngine_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Multiselect.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class ZFEngine_Form_Element_ImageContainer extends Zend_Form_Element_Xhtml
{
    /**
     * Use formImageContainer view helper by default
     * @var string
     */
    public $helper = 'formImageContainer';
}
