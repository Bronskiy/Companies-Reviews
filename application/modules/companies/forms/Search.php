<?php

/**
 * Company search form
 */
class Companies_Form_Search extends Zend_Form {
    /**
     * Init form
     */
    public function init() {
        parent::init();

        $this->addElement("text", "search", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("NotEmpty")
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Search*",
            "maxlength" => "255",
            "class" => "input-medium",
        ));
    }
}