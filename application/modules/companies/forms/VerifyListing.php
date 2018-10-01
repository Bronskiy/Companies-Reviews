<?php

/**
 * Verify listing form
 */
class Companies_Form_VerifyListing extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();
        $this->addPrefixPath('Cgsmith\\Form\\Element', APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/Form/Element', Zend_Form::ELEMENT);
        $this->addElementPrefixPath('Cgsmith\\Validate\\', APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/Validate/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath("Zend_Validate_Db", "Zend/Validate/Db", "validate");

        $this->addElement("text", "name", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(1, 1000)),
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Name*",
            "maxlength" => "1000",
        ));

        $this->addElement("text", "email", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(1, 255)),
                array("EmailAddress", true),
                array("NoRecordExists", true, array(
                    "users",
                    "mail",
                    "messages" => array(
                        Zend_Validate_Db_Abstract::ERROR_RECORD_FOUND => "User with this e-mail address already exists"
                    )
                ))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "E-mail*",
            "maxlength" => "255",
        ));

        $this->addElement("text", "email_confirm", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(1, 255)),
                array("EmailAddress", true),
                array("identical", false, array(
                    "token" => "email",
                    "messages" => array(
                        Zend_Validate_Identical::NOT_SAME => "E-mails should match"
                    )
                ))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Confirm E-mail*",
            "maxlength" => "255",
        ));

        $this->addElement("text", "phone", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(1, 1000)),
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Phone*",
            "maxlength" => "phone",
        ));

        $this->addElement("password", "password", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(1, 1000, "UTF-8")),
                array("alnum", true, "allowWhiteSpace" => false),
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "autocomplete" => "off",
            "label" => "Password*",
            "maxlength" => "1000",
        ));

        $this->addElement("password", "password_confirm", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", true, array(1, 1000, "UTF-8")),
                array("alnum", true, "allowWhiteSpace" => false),
                array("identical", false, array(
                    "token" => "password",
                    "messages" => array(
                        Zend_Validate_Identical::NOT_SAME => "Passwords should match"
                    )
                ))
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "autocomplete"=> "off",
            "label" => "Confirm Password*",
            "maxlength" => "1000",
        ));

        $this->addElement('Recaptcha', 'g-recaptcha-response', [
          'siteKey'   => "6LcoJGgUAAAAAEDcapUS2a-1QopE87ClZVyNRDDG",
          'secretKey' => "6LcoJGgUAAAAACv6-Vp8dERxzVjulVAggvJeGzN0",
        ]);
        /*
        $recaptcha = new Zend_Service_ReCaptcha(
            "6LfBgeISAAAAAHaZ06LzbINud4kxP7OJuX-Y7TbN",
            "6LfBgeISAAAAACftipr1NaOjB71jejo98gbM0_QH"
        );

        $captcha = $this->createElement("Captcha", "ReCaptcha", array(
            "captcha" => array(
                "captcha" => "ReCaptcha",
                "service" => $recaptcha
            )
        ));

        $captcha->setLabel("Enter CAPTCHA*");
        $this->addElement($captcha);
        */
    }
}
