<?php

/**
* Sign up form
*/
class Users_Form_Register extends Main_Forms_ZForm {
  /**
  * Init form
  */
  public function checkCodeNum()
   {
     //$this->addElementPrefixPath("Zend_Validate_Db", "Zend/Validate/Db", "validate");
     $codeNum = str_pad(rand(0, pow(10, 5)-1), 5, '0', STR_PAD_LEFT);
     //$test = $codeNum->addValidator('NoRecordExists', true, array('table' => 'companies', 'field' => 'code_num'));
     //$row = $this->fetchRow($where);   //If no row is found then $row is null .

      $validator = new Zend_Validate_Db_RecordExists(
             array(
                 'table' => "companies",
                 'field' => 'code_num'
                 )
         );

        $test = $validator->isValid(00001);

  //$test=   array(new Zend_Validate_Db_RecordExists(array("table" => "companies", "field" => "code_num")));

    /*
    if(!$row)
    {
    $row = $dbTb->createNew($insert); //$insert an associative array where it keys map cols of table
    $row->save();
     $this->view->row_not_found = true;
    }
    return $row;
    */
     return $test;
   }

  public function init() {
    parent::init();
    $this->addPrefixPath('Cgsmith\\Form\\Element', APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/Form/Element', Zend_Form::ELEMENT);
    $this->addElementPrefixPath('Cgsmith\\Validate\\', APPLICATION_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src/Cgsmith/Validate/', Zend_Form_Element::VALIDATE);
    $this->addElementPrefixPath("Zend_Validate_Db", "Zend/Validate/Db", "validate");

    $this->addElement("select", "plan_id", array(
      "validators" => array(
        array(new Zend_Validate_Db_RecordExists(array("table" => "plans", "field" => "id")))
      ),
      "decorators" => array("ViewHelper"),
      "label" => "Plan*",
      "multioptions" => $this->_getPlans(),
      "required" => true,
      /*"description" => "Business Class includes:<ul><li>Business profile setup</li><li>Revudio app</li>" .
      "<li>Free up search analysis</li><li>Tablet secure case with keypad cable locking system</li>" .
      "<li>Tablet stand for display</li><li>10ft USB power extender for iPad</li><li>Display Instructions</li></ul>",*/
      "description" => "<div style='margin-bottom: 6px;'><strong>Business Class:</strong> Refers to the majority of businesses who are brick and mortar.</div>".
      "<div><strong>Service Class:</strong> Refers to companies who provide services outside there place of business. example: carpet cleaners, plumbers</div>"
    ));

    $this->addElement("text", "business_name", array(
      "filters" => array("StringTrim"),
      "validators" => array(
        array("StringLength", false, array(1, 255, "UTF-8"))
      ),
      "decorators" => array("ViewHelper"),
      "required" => true,
      "label" => "Business Name*",
      "maxlength" => "255",
    ));

    $this->addElement("checkbox", "local_business", array(
      "decorators" => array("ViewHelper"),
      "required" => true,
      "label" => "Local Business*",
      "onchange" => "onLocalBusinessCheckboxChange();",
      "description" => "If you perform business at a local level and do not sell or service customers on a national platform across the united states or world, please check local business.",
      "checked" => true
    ));

    $this->addElement("checkbox", "show_address", array(
      "decorators" => array("ViewHelper"),
      "required" => true,
      "label" => "Show Address*",
      "description" => "If you are a corporate or national company and want to display your address please click here. Note: for best results we recommend you always show a legitimate business address.",
      "checked" => true
    ));

    $this->addElement("select", "state", array(
      "validators" => array(
        array("StringLength", false, array(2, 2, "UTF-8"))
      ),
      "decorators" => array("ViewHelper"),
      "label" => "State*",
      "required" => true,
      "multioptions" => $this->_getStatesMultioptions()
    ));

    $this->addElement("text", "business_city", array(
      "filters" => array("StringTrim"),
      "validators" => array(
        array("StringLength", false, array(1, 255, "UTF-8"))
      ),
      "decorators" => array("ViewHelper"),
      "required" => true,
      "label" => "Business City*",
      "maxlength" => "255",
    ));

    $this->addElement("select", "category_id", array(
      "validators" => array(
        array("RecordExists", true, array(
          "categories",
          "id"
        )),
      ),
      "decorators" => array("ViewHelper"),
      "required" => false,
      "label" => "Category",
      "multioptions" => $this->_getCategoriesMultioptions()
    ));
    var_dump($this->checkCodeNum());

    $this->addElement("text", "code_num", array(
      "filters" => array("StringTrim"),
      "validators" => array(
        array("Digits", true),
        array("StringLength", true, array(5, 5)),
        array("NoRecordExists", true, array(
          "companies",
          "code_num",
          "messages" => array(
            Zend_Validate_Db_Abstract::ERROR_RECORD_FOUND => "Company with this code already exists"
          )
        ))
      ),
      "decorators" => array("ViewHelper"),
      "required" => true,
      "description" => "Unique 5-digit code",
      "label" => "Company Code*",
      "disabled" => "disabled",
      "maxlength" => "5",
      "value" => str_pad(rand(0, pow(10, 5)-1), 5, '0', STR_PAD_LEFT),
    ));

    $this->addElement("text", "name", array(
      "filters" => array("StringTrim"),
      "validators" => array(
        array("StringLength", false, array(1, 100, "UTF-8"))
      ),
      "decorators" => array("ViewHelper"),
      "required" => false,
      "label" => "Name",
      "maxlength" => "100",
    ));

    $this->addElement("text", "mail", array(
      "filters" => array("StringTrim"),
      "validators" => array(
        array("StringLength", true, array(4, 255)),
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
          "token" => "mail",
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

    $this->addElement("password", "password", array(
      "filters" => array("StringTrim"),
      "validators" => array(
        array("StringLength", false, array(1, 255, "UTF-8")),
        array("alnum", true, "allowWhiteSpace" => false),
      ),
      "decorators" => array("ViewHelper"),
      "required" => true,
      "autocomplete" => "off",
      "label" => "Password*",
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
            Zend_Validate_Identical::NOT_SAME => "Passwords should match"
          )
        ))
      ),
      "decorators" => array("ViewHelper"),
      "required" => true,
      "autocomplete" => "off",
      "label" => "Confirm Password*",
      "maxlength" => "255",
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

/**
* Get categories
* @return array
*/
protected function _getCategoriesMultioptions() {
  $table = Doctrine_Core::getTable("Companies_Model_Category");
  $categories = $table->findAll();
  $options = array();
  $options[""] = "Uncategorized";

  foreach ($categories as $category)
  $options[$category->id] = $category->name;

  asort($options);

  return $options;
}

/**
* Get states
* @return mixed
*/
protected function _getStatesMultioptions() {
  return $this->getView()->states()->getStatesArray();
}

/**
* Get plans
* @return array
*/
protected function _getPlans() {
  $table = Doctrine_Core::getTable("Companies_Model_Plan");
  $plans = $table->findByStatus(Companies_Model_Plan::STATUS_ACTIVE);

  $options = array();

  foreach ($plans as $plan) {
    $options[$plan->id] = $plan->name;
  }

  return $options;
}
}
