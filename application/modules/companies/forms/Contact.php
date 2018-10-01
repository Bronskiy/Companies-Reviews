<?php

/**
 * Company contact form
 */
class Companies_Form_Contact extends Main_Forms_ZForm
{
    /**
     * Init form
     */
    public function init()
    {
        parent::init();
        $this->addPrefixPath('Cgsmith\\Form\\Element', APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/Form/Element', Zend_Form::ELEMENT);
        $this->addElementPrefixPath('Cgsmith\\Validate\\', APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/Validate/', Zend_Form_Element::VALIDATE);

        $this->addElement('text', 'name', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 1, 100, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'Name:*',
            'maxlength'  => '100',
        ));

    	$this->addElement('text', 'mail', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', true, array( 4, 255 ) ),
                array( 'EmailAddress', true )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'E-mail:*',
            'maxlength'  => '255',
        ));

        $this->addElement('text', 'phone', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 1, 100, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Phone:',
            'maxlength'  => '100',
        ));

        $this->addElement('textarea', 'message', array(
             'filters' => array(
                 'StringTrim',
                 new Zend_Filter_StripTags(),
                 new Zend_Filter_StripNewlines()
             ),
            'validators' => array(
                 array( 'StringLength', false, array( 1, 65536, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'Message:*',
            'rows'       => 5,
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
    }
}
