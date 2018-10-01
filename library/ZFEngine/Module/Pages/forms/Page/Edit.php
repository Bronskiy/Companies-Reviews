<?php

class ZFEngine_Module_Pages_Form_Page_Edit extends ZFEngine_Module_Pages_Form_Page_Abstract
{
    /**
     * Form initialization
     *
     * @return void
     */
    public function init()
    {
        $this->setName('form_page_edit')
             ->setMethod('post');
        parent::init();

        $id = new Zend_Form_Element_Hidden('id');
        $this->addElement($id);

        $this->submit->setLabel(_('Сохранить'));
        return $this;

        $this->addElements(array($this->_submit()));
    
    }

    /**
     * Validate the form
     *
     * @param  array $data
     * @return boolean
     */
    public function isValid($data)
    {
        $page = new Pages_Model_PageService();
        $page->findPageById($data['id']);
        if ($data['alias'] == $page->getModel()->alias) {
            $this->alias->removeValidator('ZFEngine_Validate_Doctrine_NoRecordExist');
        }

        return parent::isValid($data);
    }

    public function populate($values)
    {
        foreach ($values['Translation'] as $lang => $translate)
        {
            $values['title'][$lang] = $translate['title'];
            $values['content'][$lang] = $translate['content'];
        }

        parent::populate($values);
    }
}