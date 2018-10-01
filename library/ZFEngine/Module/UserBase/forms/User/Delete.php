<?php

/**
 * Form for delete user
 */
class ZFEngine_Module_UserBase_Form_User_Delete extends Default_Form_Delete
{

    public function init()
    {
        parent::init();

        $this->setName(strtolower(__CLASS__));

        return $this;
    }
}
