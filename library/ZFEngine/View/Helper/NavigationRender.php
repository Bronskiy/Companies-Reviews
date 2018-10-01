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
 * Navigation helper
 *
 * @category   ZFEngine
 * @package    ZFEngine_View
 * @subpackage    Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_View_Helper_NavigationRender extends ZFEngine_View_Helper_Abstract
{

    /**
     * Navigation object
     * @var ZFEngine_Navigation
     */
     protected $_container;

    /**
     * request params
     * @var array
     */
     protected $_requestParams = null;


    /**
     * direct call
     *
     * @param array $container
     * @return ZFEngine_View_Helper_NavigationRender
     */
    public function navigationRender($container = array())
    {
        $this->_container = $container;
        return $this;
    }


    /**
     * Set conteiner menu
     *
     * @param array $container
     * @return ZFEngine_View_Helper_NavigationRender
     */
    public function setConteiner($container = array())
    {
        $this->_container = $container;
        return $this;
    }

    /**
     * Возвращает отрендереное меню пользователя
     *
     * @return string
     */
    public function renderMenu($childMenu = array())
    {
        if (!empty($childMenu)) {
            $menu = $childMenu;
            $class = $this->_container->getNavigationChildClass();
        } else {
            $menu = $this->_container->getMenu() ;
            $class = $this->_container->getNavigationMenuClass();
        }
        $content = '<ul class="' . $class . '">';
        foreach ($menu as $uri => $item) {
            if ($this->_isAllowed($item)) {
                $content .= $this->_renderItem($uri, $item);
            }
        }
        $content .= '</ul>';
        return $content;
    }

    /**
     * Проверка доступа к пунктам меню
     *
     * @return boolean
     */
    public function _isAllowed($item)
    {
        // Если не заданы условия для проверки - значит просто возвращаем true
        if (!array_key_exists('resource', $item) &&
            !array_key_exists('privilege', $item)) {
            return true;
        }
        return $this->view->isAllowed($item['resource'], $item['privilege']);
    }


    /**
     *
     * @param string $uri
     * @param array $item
     * @return string
     */
    protected function _renderItem($uri, $item)
    {
        $content = '';

        $isActive = $this->_isCurrentActive($uri, $item);

        if ($isActive && array_key_exists('is-active', $item)) {
            $temp = $item['is-active'];
            unset($item['is-active']);
            $item = array_merge_recursive($item, $temp);
        }

        if (array_key_exists('content-before', $item)) {
            $content .= $item['content-before'];
        }

        switch($uri[0]) {
            case '@':
                $content .= '<li class="separator">&nbsp;</li>' . "\n";
                break;
            case '/':
                if ($isActive) {
                    $classes[] = $this->_container->getCurrentActiveClass();
                }
            case '#':
            default:
                if (isset($item['class'])) {
                    $classes[] = $item['class'];
                }
                $class = (!empty($classes)) ? 'class="' . implode(' ', $classes) . '"' : '';
                $content .= "<li $class >";
                $content .= $this->_renderLink($uri, $item);

                // Если есть вложенные - рендерим и добавляем в текущий елемент
                if (array_key_exists('pages', $item) && !empty($item['pages'])) {
                    $content .= $this->renderMenu($item['pages']);
                }

                $content .= "</li>\n";
        }

        if (array_key_exists('content-after', $item)) {
            $content .= $item['content-after'];
        }

        return $content;
    }

    /**
     *
     * @param string $uri
     * @param array $item
     * @return string
     */
    protected function _renderLink($uri, $item)
    {

        switch($uri[0]) {
            case '#':
                $url = $uri;
                break;
            case '/':
            default:
                $parsedUri = $this->_parseUri($uri, $item['route']);

                $url = $this->view->url($parsedUri,
                    $item['route'], true
                );
        }

        $content = '';

        if (isset($item['label-params']) && !empty($item['label-params'])) {
            $title = vsprintf($this->view->translate($item['label']), $item['label-params']);
        } else {
            $title = $this->view->translate($item['label']);
        }

        if (array_key_exists('content-prepend', $item)) {
            $content .= $item['content-prepend'];
        }

        $content .= '<a href="' . $url . '">' . $title . '</a>';

        if (array_key_exists('content-append', $item)) {
            $content .= $item['content-append'];
        }

        return $content;
    }

    /**
     *
     * @param string $uri
     * @param array $item
     * @return string
     */
    protected function _getRequestParams()
    {
        if (!$this->_requestParams) {
            $requestParams = $this->_getRequest()->getParams();
            foreach ($this->_getRequest()->getPost() as $key=>$value) {
                unset($requestParams[$key]);
            }
            if (array_key_exists('error_handler', $requestParams)) {
                unset($requestParams['error_handler']);
            }
            if (array_key_exists('lang', $requestParams)) {
                unset($requestParams['lang']);
            }
            $this->_requestParams = $requestParams;
        }
        return $this->_requestParams;
    }

    /**
     *
     * @param string $uri
     * @param array $item
     * @return string
     */
    protected function _isCurrentActive($uri, $item)
    {
        if ($uri[0] != '/') {
            return false;
        }
        $requestParams = $this->_getRequestParams();

        $currentActiveRules = array();
        if (array_key_exists('active', $item)) {
            if (is_array($item['active'])) {
                foreach ($item['active'] as $url) {
                    $currentActiveRules[] = $this->_parseUri($url['url'], $url['route']);
                }
            } else {
                $currentActiveRules[] = $this->_parseUri($item['active'], $item['route']);
            }
        } else {
            $currentActiveRules[] = $this->_parseUri($uri, $item['route']);
        }

        $result = false;
        foreach ($currentActiveRules as $currentActiveRule) {
            $resultOneLink = true;
            $continue = false;
            foreach ($currentActiveRule as $key=>$value) {
                if ($continue) {
                    continue;
                }
                switch ($key) {
                    case 'module':
                    case 'controller':
                    case 'action':
                        if ($requestParams[$key] != $value && $value != '*') {
                            $resultOneLink = $resultOneLink & false;
                        }
                        break;
                    case '*':
                        $continue = true;
                        break;
                    default :
                        if ( !array_key_exists($key, $requestParams) || ($requestParams[$key] != $value && $value != '*')) {
                            $resultOneLink = $resultOneLink & false;
                        }
                }
            }

            if (count($currentActiveRule)  != count($requestParams) && !$continue) {
                $resultOneLink = $resultOneLink & false;
            }

            $result = $result | $resultOneLink;
        }

        return $result;
    }

    /**
     *
     * @param string $uri
     * @return array
     */
    protected function _parseUri($uri, $route)
    {
        $uri = trim($uri, '/');
        $partedUri = explode('/', $uri);

        $router = Zend_Controller_Front::getInstance()->getRouter();
        $defaults = $router->getRoute($route)->getDefaults();

        $params = $defaults;

        if (!isset($defaults['module']) || $defaults['module'] != $partedUri[0]){
            $params['module'] = $partedUri[0];
        }
        if (!isset($defaults['controller']) || $defaults['controller'] != $partedUri[1]){
            $params['controller'] = $partedUri[1];
        }
        if (!isset($defaults['action']) || $defaults['action'] != $partedUri[2]){
            $params['action'] = $partedUri[2];
        }
        
        for ($index = 3; $index < count($partedUri); $index ++) {
            if (strlen($partedUri[$index])) {
                $params[$partedUri[$index]] = @$partedUri[++$index];
            }
        }
        return $params;
    }

}