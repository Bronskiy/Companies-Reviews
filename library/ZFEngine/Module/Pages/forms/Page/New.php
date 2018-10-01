<?php

class ZFEngine_Module_Pages_Form_Page_New extends ZFEngine_Module_Pages_Form_Page_Abstract
{
    /**
     * Form initialization
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->setName('form_page_new')
             ->setMethod('post');

        $this->submit->setLabel(_('Сохранить'));

        return $this;
    }

}