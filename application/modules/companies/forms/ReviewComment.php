<?php

/**
 * Review comment form
 */
class Companies_Form_ReviewComment extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();

        $this->addElement("textarea", "comment", array(
             "filters" => array(
                 "StringTrim",
                 new Zend_Filter_StripTags(),
                 new Zend_Filter_StripNewlines()
             ),
            "validators" => array(
                 array("StringLength", false, array(1, 65536, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Comment*:",
            "rows" => 5,
        ));
    }
}