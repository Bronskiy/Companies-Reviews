<?php

/**
 * Form for delete page
 */
class ZFEngine_Module_Pages_Form_Page_Delete extends Default_Form_Delete
{

    public function init()
    {
        parent::init();

        $this->setName(strtolower(__CLASS__));

        return $this;
    }
}