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
 * @package    ZFEngine
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Navigation
 *
 * @category   ZFEngine
 * @package    ZFEngine
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Navigation
{

    /**
     * Container for menu array
     * @var array
     */
    private $_menu = null;

    /**
     * Class for active menu element
     *
     * @var string
     */
    private $_currentActiveClass = 'current';

    /**
     * Class for menu block
     *
     * @var string
     */
    private $_navigationMenuClass = 'navigation-menu';

    /**
     * Class for chilld block
     *
     * @var string
     */
    private $_navigationChildClass = 'navigation-child';


    /**
     * Constructor
     * 
     * @param array $container
     */
    public function  __construct($container = array())
    {
        $this->_menu = $container;
    }


    /**
     * Set conteiner menu
     *
     * @param array $container
     * @return ZFEngine_Navigation
     */
    public function setMenu($container = array())
    {
        $this->_menu = $container;
        return $this;
    }

    /**
     * Set conteiner menu
     *
     * @param array $container
     * @return array
     */
    public function getMenu()
    {
        return $this->_menu;
    }

//    /**
//     * direct call
//     *
//     * @param array $container
//     * @return ZFEngine_View_Helper_ZFENavigation
//     */
//    public function mergeConteiner($container = array())
//    {
//        $this->_container = $container;
//        return $this;
//    }


    /**
     *
     * @param string $class
     */
    public function setNavigationChildClass($class)
    {
        $this->$_navigationChildClass = $class;
    }

    /**
     *
     * @return string
     */
    public function getNavigationChildClass()
    {
        return $this->_navigationChildClass;
    }

    /**
     *
     * @param string $class
     */
    public function setCurrentActiveClass($class)
    {
        $this->_currentActiveClass = $class;
    }

    /**
     *
     * @return string
     */
    public function getCurrentActiveClass()
    {
        return $this->_currentActiveClass;
    }

    /**
     *
     * @param string $class
     */
    public function setNavigationMenuClass($class)
    {
        $this->_navigationMenuClass = $class;
    }
    
    /**
     *
     * @param string $class
     */
    public function setDefaultNavigationMenuClass()
    {
        $this->_navigationMenuClass = 'navigation-menu';
    }

    /**
     *
     * @return string
     */
    public function getNavigationMenuClass()
    {
        return $this->_navigationMenuClass;
    }

}