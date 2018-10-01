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
 * @package    ZFEngine_Module
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 *  Index Controller for module Pages
 *
 * @category   ZFEngine
 * @package    ZFEngine_Module
 * @subpackage Pages
 * @author     Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 *
 * @property   ZFEngine_Module_Pages_Form_Page_Delete $formDelete
 * @property   ZFEngine_Module_Pages_Form_Page_Edit $formEdit
 * @property   ZFEngine_Module_Pages_Form_Page_New $formNew
 */
abstract class ZFEngine_Module_Pages_Controller_Index extends Zend_Controller_Action
{

    /**
     * Get page service layer
     *
     * @return ZFEngine_Module_Pages_Model_PageService
     */
    protected function _getPageService()
    {
        return new ZFEngine_Module_Pages_Model_PageService();
    }

    /**
     * New page
     *
     * @return void
     */
    public function newAction()
    {
        $pageService = $this->_getPageService();
        if ($this->_request->isPost()) {
            if ($pageService->processNew($this->getRequest()->getParams())) {
                $page = $pageService->getModel();
                $this->_helper->redirector->gotoRoute(
                    array('alias' => $page->alias),'pages-view', true
                );
            }
        }
        $this->view->form = $pageService->formNew;
    }

    /**
     * View page
     *
     * @return void
     */
    public function viewAction()
    {
        $pageService = $this->_getPageService();
        $pageService->findPageByAlias($this->_request->getParam('alias'));

        $page = $this->_helper->getServiceLayer('pages','page');
        $pageAlias = $this->_request->getParam('alias');
        $page->findPageByAlias($pageAlias);
        $title = $page->getModel()->title;
        $this->view->title = $title;
        $this->view->headTitle($title);

        $this->view->page = $page->getModel();
    }

    /**
     * Edit page
     *
     * @return void
     */
    public function editAction()
    {
        $title = $this->view->translate('Редактировать страницу');
        $this->view->headTitle($title);
        $this->view->title = $title;

        $pageId = (int)$this->_request->getParam('id');
        $page = $this->_helper->getServiceLayer('pages','page');
        $page->findPageById($pageId);

        $form = $page->formEdit;
        $form->getElement('id')->setValue($pageId);
        if ($this->_request->isPost()) {
            $postData = $this->_request->getPost();
            $formResult = $page->processFormEdit($postData);
            $this->_helper->redirector->gotoRoute(array('alias' => $page->getModel()->alias),
                                                  'pages-view', true);
         } else {
            $page->getModel()->Translation;
            $form->populate($page->getModel()->toArray());
         }
         $this->view->form = $form;
    }

    /**
     * Delete page
     *
     * @return void
     */
    public function deleteAction()
    {
        $title = $this->view->translate('Удалить старинцу');
        $this->view->headTitle($title);
        $this->view->title = $title;

        $page = $this->_helper->getServiceLayer('pages','page');
        $pageId = (int)$this->_request->getParam('id');
        $page->findPageById($pageId);

        $title = sprintf($this->view->translate('Вы хотите удалить страницу "%s"?'),
                $page->getModel()->title);
        $this->view->title = $title;
        $this->view->form = $page->formDelete;

        if ($this->_request->isPost()) {

            $postData = $this->_request->getPost();
            $page->processFormDelete($postData);

            $this->_helper->redirector->gotoRoute(array(
                                                    'module' => 'pages',
                                                    'controller'=>'index',
                                                    'action'=>'list'),
                                              'default', true);

        }
    }

    /**
     * View page
     *
     * @return void
     */
    public function listAction()
    {
        $this->view->setTitle('Страницы');

        $page = $this->_helper->getServiceLayer('pages','page');
        $query = $page->getMapper()->findAllAsQuery();

        $paginator = new Zend_Paginator(new ZFEngine_Paginator_Adapter_Doctrine($query));

        $this->view->paginator = $paginator;
//        $this->view->lang = $this->_lang;
//        $this->view->pages = $paginator->getCurrentItems();
    }
}