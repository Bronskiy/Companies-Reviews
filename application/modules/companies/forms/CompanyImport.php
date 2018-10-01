<?php

/**
 * Company import form
 */
class Companies_Form_CompanyImport extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();
        
        $this->addElement("file", "csv", array(
            "validators" => array(
                array("File_Size", true, 10 * 1024 * 1024),
                array("File_Extension", true, array("csv")),
                array("File_MimeType", true, array(
                    "text/csv",
                    "text/plain"
                )),
            ),
            "decorators" => array("File"),
            "required" => true,
            "label" => "CSV*",
            "description" => "CSV file with companies to import. It should contain the following columns: name, category, address, city, state, zip, phone, e-mail, website, description. Please attach only CVS file up to 10 Mb in size."
        ));

        $this->addElement("checkbox", "create_categories", array(
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Create Categories*",
            "description" => "Create categories if they do not exist.",
            "checked" => "checked",
        ));

        $this->addElement("checkbox", "no_equal_names", array(
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "No Equal Names*",
            "description" => "Skip company if there is already a company with the same name in database.",
            "checked" => "checked",
        ));

        $this->addElement("checkbox", "continue_on_error", array(
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Continue On Error*",
            "description" => "If this checkbox is set and an error occurs during some company import, the system will continue importing the remaining companies.",
        ));
    }
}