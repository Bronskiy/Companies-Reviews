<?php

/**
 * Employee form
 */
class Companies_Form_Employee extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();    	

        $this->addElement("select", "sorting_position", array(
            "decorators" => array("ViewHelper"),
            "label" => "Sorting Position*",
            "multioptions" => $this->_getSortingPositions(),
            "required" => true,
        ));

        $this->addElement('checkbox', 'public_profile', array(
            'decorators' => array('ViewHelper'),
            'required' => true,
            'label' => 'Public Profile*',
        ));

        $this->addElement("text", "name", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(1, 1000, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Name*",
            "maxlength" => "1000",
        ));

        $this->addElement("text", "position", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(1, 1000, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "Position in Company",
            "maxlength" => "1000",
        ));

        $this->addElement("file", "photo", array(
            "validators" => array(
                array("File_Size", true, 10 * 1024 * 1024),
                array("File_Extension", true, array(
                    "jpg",
                    "jpeg",
                )),
                array("File_MimeType", true, array(
                    "image/jpeg",
                )),
            ),
            "decorators" => array("File"),
            "required" => false,
            "label" => "Photo",
            "description" => "JPEG image file up to 10 Mb in size."
        ));

        $this->addElement("text", "year_started", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                new Zend_Validate_Digits(),
                new Zend_Validate_Date(array("format" => "yyyy")),
                new Zend_Validate_Between(array("min" => 1930, "max" => date("Y")))
            ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "Year Started With Company",
            "maxlength" => "4",
        ));
        
        $this->addElement("textarea", "about", array(
             "filters" => array(
                "StringTrim",
                new Zend_Filter_StripTags()
             ),
            "validators" => array(
                array("StringLength", false, array(1, 65536, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "About",
            "rows" => 5,
        ));
        
        $this->addElement("text", "facebook_link", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(2, 100, "UTF-8"))
           ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "Facebook Link",
            "maxlength" => "100",
        ));

        $this->addElement("text", "twitter_link", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(2, 100, "UTF-8"))
           ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "Twitter Link",
            "maxlength" => "100",
        ));

        $this->addElement("text", "linkedin_link", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(2, 100, "UTF-8"))
           ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "Linkedin Link",
            "maxlength" => "100",
        ));
        
        $this->addElement("text", "google_link", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(2, 100, "UTF-8"))
           ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "Google+ Link",
            "maxlength" => "100",
        ));
    }

    /**
     * Get sorting positions
     * @return array
     */
    protected function _getSortingPositions() {
        return array(
            10 => "Owner",
            100 => "Upper Management",
            200 => "Middle Management",
            300 => "Other"
        );
    }
}