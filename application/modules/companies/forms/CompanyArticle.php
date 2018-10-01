<?php

/**
 * Article form
 */
class Companies_Form_CompanyArticle extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();

        $this->addElement("text", "title", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(1, 1000, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Title*",
            "maxlength" => "1000",
        ));
        
        $this->addElement("textarea", "intro", array(
             "filters" => array(
                "StringTrim",
                new Zend_Filter_StripTags()
             ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "Introduction*",
            "rows" => 10,
            "class" => "full-width",
        ));

        $this->addElement("textarea", "content", array(
             "filters" => array(
                "StringTrim",
             ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "Content*",
            "rows" => 10,
            "class" => "full-width",
        ));
    }
}