<?php

/**
 * Form for Csrf attacks prevention
 * used for post request updating and deleting data from db
 */
class Main_Forms_Csrf extends Main_Forms_Abstract {
    /**
     * posfix for all form elements names
     * @var string | int 
     */
    private $_postfix = false;
    
    /**
     * Label for submit button
     * @var string 
     */
    private $_submitLabel = false;

    /**
     * Constructor
     * @param null $options
     */
    public function __construct($options = null) {
        $this->_setInnerOptions(array('postfix', 'submitLabel'), $options);
        parent::__construct($options);
    }
    
    /**
     *  Sets values for private variables only in this class
     * 
     *  parent class variables are not involved
     */
    private function _setInnerOptions($setOpts, array &$options = null) {
        if(empty($options) || empty($setOpts)) return;
        // searching current class private vars
        $allVars = get_class_vars(__CLASS__);
        $parentVars = get_class_vars(get_parent_class());
        $thisVars = @array_diff_assoc($allVars, $parentVars);
        
        if(!is_array($setOpts)) {
            $setOpts = array($setOpts);
        }

        foreach($setOpts as $optName) {
            $pvtVarName = '_' . $optName;

            if(array_key_exists($optName, $options) 
               && array_key_exists($pvtVarName, $thisVars)) 
            {
                $this->{$pvtVarName} = $options[$optName];
                unset($options[$optName]);
            }
        }
    }
    
    public function init()
    {
        // hash hidden element
        $csrfToken = new Zend_Form_Element_Hash('csrf_token' . $this->_postfix);
        $csrfToken->setDecorators(array('ViewHelper'));
        $csrfToken->setSalt($this->_getHashItemSalt());
        $this->addElement($csrfToken);
        
        // hidden elem, required for generate correct form at validation stage
        $hidden = new Zend_Form_Element_Hidden('postfix');
        $hidden->setValue($this->_postfix);
        $hidden->setDecorators(array('ViewHelper'));
        $this->addElement($hidden);
        
        // button submit
        $this->addElement('submit', 'scrf_submit' . $this->_postfix, array(
            'class' => 'btn',
            'decorators' => array('ViewHelper'),
            'label' => !empty($this->_submitLabel) ? $this->_submitLabel : 'Submit'
        ));
    }
}