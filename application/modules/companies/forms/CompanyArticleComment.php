<?php

/**
 * Article comment form
 */
class Companies_Form_CompanyArticleComment extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();
        $this->addPrefixPath('Cgsmith\\Form\\Element', APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/Form/Element', Zend_Form::ELEMENT);
        $this->addElementPrefixPath('Cgsmith\\Validate\\', APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/Validate/', Zend_Form_Element::VALIDATE);

        $this->addElement("text", "email", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array("StringLength", false, array(1, 1000, "UTF-8")),
                array("EmailAddress", true),
            ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "E-mail*",
            "maxlength" => "1000",
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

        $this->addElement("textarea", "comment", array(
             "filters" => array(
                "StringTrim",
                new Zend_Filter_StripTags()
             ),
            "decorators" => array("ViewHelper"),
            "required" => true,
            "label" => "Comment*",
            "rows" => 10,
            "class" => "full-width",
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

        $captcha->setLabel("Security Code");
        $this->addElement($captcha);
        */
    }
}
