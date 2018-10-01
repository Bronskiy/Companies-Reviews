<?php

/**
 * Edit user form
 */
class Users_Form_EditUser extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();

        $this->addElement("text", "name", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(0, 100)),
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Name*",
            "maxlength" => "100",
        ));

        $this->addElement("text", "mail", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(4, 255)),
                array("EmailAddress", true),
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "E-mail*",
            "maxlength" => "255",
        ));

        $this->addElement("text", "phone", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(0, 1000)),
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Phone",
            "maxlength" => "1000",
        ));

        $this->addElement("password", "password", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(1, 255, "UTF-8")),
                array("alnum", true, "allowWhiteSpace" => false),
            ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "autocomplete" =>"off",
            "label" => "Password",
            "maxlength" => "255",
        ));

        $this->addElement("password", "password_confirm", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(1, 255, "UTF-8")),
                array("alnum", true, "allowWhiteSpace" => false),
                array("identical", false, array(
                    "token" => "password",
                    "messages" => array(
                        Zend_Validate_Identical::NOT_SAME => "Passwords should be the same"
                    )
                ))
            ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "autocomplete"=>"off",
            "label" => "Confirm",
            "maxlength" => "255",
        ));
    }
}