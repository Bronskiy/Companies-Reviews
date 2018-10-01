<?php

/**
 * Leave review form
 */
class Api_Form_CreateReview extends Zend_Form {
    private $_companyId = null;

    /**
     * Constructor
     * @param null $companyId
     */
    public function __construct($companyId = null) {
        $this->_companyId = $companyId;
        parent::__construct();
    }

    /**
     * Init form
     */
    public function init() {
        parent::init();

        $this->addElement("hidden", "employee_id", array(
            "validators" => array(
                array(new Main_Validate_RecordBelongsToParent("company_employees", "id", "company_id", $this->_companyId, false))
            ),
            "decorators" => array("ViewHelper"),
            "required" => false,
        ));

        $this->addElement("text", "name", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(1, 100, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "maxlength" => "100",
        ));

        $this->addElement("text", "from", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(1, 100, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "maxlength" => "100",
        ));

    	$this->addElement("text", "email", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(4, 100 )),
                array("EmailAddress", true, array("domain" => false))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "maxlength" => "100",
        ));

        $this->addElement("textarea", "review", array(
             "filters" => array(
                 "StringTrim",
                 new Zend_Filter_StripTags(),
                 new Zend_Filter_StripNewlines()
             ),
            "validators" => array(
                 array("StringLength", false, array(1, 65536, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => false,
        ));

        $this->addElement("hidden", "rating", array(
            "validators" => array(
                array("Float", true ),
                array("Between", true, array(0, 5, true)),
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "value" => 0,
        ));
    }
}