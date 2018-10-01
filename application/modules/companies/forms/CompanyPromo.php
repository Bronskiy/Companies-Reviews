<?php

/**
 * Company promo form
 */
class Companies_Form_CompanyPromo extends Main_Forms_ZForm
{
    /**
     * Init form
     */
    public function init()
    {
        $this->setMethod('post');

        // after setName for csrf token unique name based on form name
        parent::init();  
        
        // promo title
        $this->addElement('text', 'title', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(1, 255, 'UTF-8'))
            ),
            'decorators' => array( 'ViewHelper' ),
            'description'=> 'Not less than 1 symbol',
            'required'   => true,
            'label'      => 'Title:*',
            'maxlength'  => '255',
            'class'      => 'zf',
            'onclick'    => 'return { oRequired : { iMin : 1 } }'
        ));
        
        // State select
        $this->addElement('select', 'status', array(
            'decorators' => array( 'ViewHelper' ),
            'label'      => 'Mailing status:',
            'required'   => true,
            'multioptions' => $this->_getStatusesMultioptions()
        ));
        
        $cke = new Main_Form_Element_CKEditor('content');
        $cke->setName('content');
        $cke->setRequired();
        $cke->setDecorators(array('ViewHelper'));
        $cke->setLabel('Content:');
        $cke->setAttrib('id', $cke->getName());        
        $cke->setAttrib('class', 'ckeditor');
        $cke->setAttrib('rows', '6');
        $cke->setAttrib('cols', '10');
        $cke->setAttrib('editorOptions', new Zend_Config_Ini(APPLICATION_PATH . '/modules/companies/configs/ckeditor.ini', 'moderator'));
        
        $this->addElement($cke);
        
    }
    
    /**
     * Setting session options for kcfinder
     * 
     *  For example here we can set option for current user upload direcory
     * 
     * @param array | string $options 
     */
    public function setKCFinderOptions(array $options = null)
    {
        if (!is_array($options) && $options !== null) {
            throw new Exception('Pass only array or null in param');
        }
        
        $kcFinderSess = new Zend_Session_Namespace('KCFINDER');
        // allow files uploading
        $kcFinderSess->disabled = false;
        
        if(!empty($options)) {
            foreach($options as $name => $val) {
                if(is_bool($val) || is_string($val)) {
                    $kcFinderSess->$name = $val;
                }
            }
        }        
    }
    
    /**
     * Mailing multoption statuses
     * 
     * @return array
     */
    protected function _getStatusesMultioptions()
    {
        return array('idle' => 'Idle', 
                     'all' => 'Mailing to all', 
                     'new' => 'Mailing to the new users');
    }
}