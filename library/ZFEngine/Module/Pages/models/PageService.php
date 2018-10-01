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
 * Service layer for pages
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
class ZFEngine_Module_Pages_Model_PageService extends ZFEngine_Model_Service_Database_Abstract
{
    /**
     * Set default model class name
     */
    public function init()
    {
        $this->_modelName = 'ZFEngine_Module_Pages_Model_Page';
    }

    /**
     * Process creation new page
     *
     * @param   array $data
     * @return  boolean
     */
    public function processNew(array $data)
    {
        $form = $this->formNew;
        if ($form->isValid($data)) {
            try {
                $formValues = $form->getValues();
                $this->_setFormDataToModel($formValues);
                $this->getModel()->save();
                return true;
            } catch (Exception $e) {
                $view = $this->getView();
                $form->addError($view->translate('An error occurred when adding page:') . $e->getMessage());
            }
        }
        $form->populate($data);
        return false;
    }

    /**
     * Processing edit page form
     *
     * @param array $postData
     * @return boolean
     */
    public function processFormEdit($postData)
    {
        $form = $this->formEdit;
        if ($form->isValid($postData)) {
            try {
                $formValues = $form->getValues();
                $this->_setFormDataToModel($formValues);
                $this->getModel()->save();
                return true;
            } catch (Exception $e) {
                $form->addError($this->getView()->translate('An error occurred when changing page:') . $e->getMessage());
                $form->populate($postData);
                return false;
            }
        } else {
            $form->populate($postData);
            return false;
        }
    }

    /**
     * Processing delete page form
     */
    public function processFormDelete($postData)
    {
        if (array_key_exists('submit_ok',$postData)) {
            $this->getModel()->delete();
            return true;
        }
        return false;
    }

    /**
     * Get page by id
     *
     * @param integer $id
     * @return Pages_Model_Page
     */
    public function getPageById($id)
    {
        $page = $this->getMapper()->find($id);

        if (!$page) {
            throw new Exception($this->getView()->translate('Page not found.'));
        }

        return $page;
    }

    /**
     * Get page by id
     *
     * @param string $alias
     * @return Pages_Model_Page
     */
    public function getPageByAlias($alias)
    {
        $page = $this->getMapper()->findOneByAlias($alias);

        if (!$page) {
            throw new Exception($this->getView()->translate('Page not found.'));
        }

        return $page;
    }

    /**
     * Find page by id
     *
     * @param integer $id
     * @return Pages_Model_PageService
     */
    public function findPageById($id)
    {
        $this->setModel($this->getPageById($id));
        return $this;
    }

    /**
     * Find page by id
     *
     * @param integer $alias
     * @return Pages_Model_PageService
     */
    public function findPageByAlias($alias)
    {
        $this->setModel($this->getPageByAlias($alias));
        return $this;
    }

    /**
     *  Put data from array into model object
     *  @param  array $data
     *  @return void
     */
    protected function _setFormDataToModel($data)
    {
        $this->getModel()->alias = $data['alias'];
        foreach ($data['title'] as $lang => $title) {
            $this->getModel()->setTitle($title, $lang);
        }
        foreach ($data['content'] as $lang => $content) {
            $this->getModel()->setContent($content, $lang);
        }
    }
}