<?php

/**
 * Leave review form
 */
class Companies_Form_LeaveReview extends Main_Forms_ZForm {
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
            "label" => "Employee*",
            "required" => true,
            "value" => 0,
        ));

        $this->addElement("text", "name", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(1, 100, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Name*:",
            "maxlength" => "100",
        ));

        $this->addElement("text", "from", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(1, 100, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "What City Are You From*:",
            "maxlength" => "100",
        ));

    	$this->addElement("text", "mail", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(4, 100)),
                array("EmailAddress", true)
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "E-mail*:",
            "maxlength" => "100",
        ));

        $this->addElement("hidden", "rating", array(
            "validators" => array(
                array("Float", true),
                array("Between", true, array(0, 5, true)),
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Rating*:",
            "value" => 0,
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
            "required" => true,
            "label" => "Review*:",
            "rows" => 5,
        ));

        $this->addElement("file", "avatar", array(
            "validators" => array(
                array("File_Size", true, 10 * 1024 * 1024),
                array("File_Extension", true, array(
                    "jpg",
                    "jpeg",
                )),
                array( "File_MimeType", true, array(
                    "image/jpeg",
                )),
            ),
            "decorators" => array("File"),
            "required" => false,
            "label" => "Photo:",
            "description" => "JPEG image file up to 10 Mb in size."
        ));

        $this->addElement("file", "video", array(
            "validators" => array(
                array("File_Size", true, 100 * 1024 * 1024),
                array("File_Extension", true, array(
                    "mov",
                    "3gp",
                )),
                array("File_MimeType", true, array(
                    "video/quicktime",
                    "video/3gpp",
                )),
            ),
            "decorators" => array("File"),
            "required" => false,
            "label" => "Video:",
            "description" => "Android/iOS video file (3gp/mov) up to 100 Mb in size."
        ));
    }
}