<?php

/**
 * Contact form
 */
class Default_Form_Contactus extends Main_Forms_ZForm
{
    /**
     * Init form
     */
    public function init()
    {
        parent::init();
        $this->addPrefixPath('Cgsmith\\Form\\Element', APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/Form/Element', Zend_Form::ELEMENT);
        $this->addElementPrefixPath('Cgsmith\\Validate\\', APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/Validate/', Zend_Form_Element::VALIDATE);

        $this->setMethod('post');
        $this->setName('contact-form');

        $this->addElement('text', 'name', array(
            'label' => 'Name',
            'required' => true,
        ));

        $this->addElement('text', 'business_name', array(
            'label' => 'Business Name:*',
            'required' => true,
        ));
        $this->addElement('text', 'phone', array(
            'label' => 'Phone Number:*',
            'required' => true,
        ));
        $this->addElement('text', 'email', array(
            'label' => 'E-Mail:*',
            'required' => true,
        ));

        $this->addElement('textarea', 'comment', array(
            'label' => 'Comments:*',
            'required' => true,
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

        $captcha->setLabel("Enter CAPTCHA");
        $this->addElement($captcha);
        */
        $this->addElement('submit', 'submit-contact', array(
            'label' => 'Send',
            'ignore' => true,
            'class' => 'btn'
        ));
    }
}
