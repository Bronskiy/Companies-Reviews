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
 * @subpackage    Action_Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Paginator helper
 *
 * @category   ZFEngine
 * @package    ZFEngine_Controller
 * @subpackage    Action_Helper
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Controller_Action_Helper_Paginator extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Returns Pagination object
     *
     * @return Zend_Paginator
     **/
    public function getPaginator($query, $page = null, $count = null)
    {
        $paginator = new Zend_Paginator(
            new ZFEngine_Paginator_Adapter_Doctrine($query)
        );

        $request = $this->getRequest();

        if (!$page) {
            $page = $request->getParam('page');
        }
        
        if (!$count) {
            $count = $request->getParam('count');
        }
        
        $paginator->setCurrentPageNumber($page);

        $allowedCounts = array(5, 10, 25);
        if (in_array($count, $allowedCounts)) {
            $paginator->setItemCountPerPage($count);
        }

        return $paginator;
    }

}