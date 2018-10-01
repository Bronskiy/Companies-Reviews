<?php

/**
 * Company form
 */
class Companies_Form_Company extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();

        $this->addElementPrefixPath('Zend_Validate_Db', 'Zend/Validate/Db', 'validate');

        // category
        $this->addElement('select', 'category_id', array(
            'validators' => array(
                array(new Main_Validate_DbRecordExistsExt(array('table' => 'categories', 'field' => 'id'), 0))
            ),
            'decorators' => array( 'ViewHelper' ),
            'label'      => 'Category*',
            'multioptions' => $this->_getCategoriesMultioptions()
        ));

        // code num
        $this->addElement('text', 'code_num', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('Digits', true, array(
                    'messages' => array(
                        'notDigits' => 'Company code should contain 5 digits (0-9)'
                    )
                )),
                array('StringLength', true, array(5, 5))
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'description'=> 'Unique company code (5 digits)',
            'label'      => 'Code*',
            'maxlength'  => '5',
        ));

        // code letter
        $this->addElement('text', 'code_letter', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'Regex', true, array(
                    'pattern' => '#^[A-Z]{4}#',
                    'messages' => array(
                        'regexNotMatch' => 'Letter code should contain 4 letters in upper case (A-Z)'
                    )
                ))
            ),
            'decorators'  => array( 'ViewHelper' ),
            'required'    => false,
            'label'       => 'Letter Code',
            'maxlength'   => '4',
        ));

        $this->addElement('text', 'name', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 1, 255, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'Business Name*',
            'maxlength'  => '255',
        ));

        $this->addElement('checkbox', 'local_business', array(
            'decorators'  => array( 'ViewHelper' ),
            'required'    => true,
            'label'       => 'Local Business*',
            'onchange'    => "onLocalBusinessCheckboxChange();",
            'description' => "If you perform business at a local level and do not sell or service customers on a national platform across the united states or world, please check local business."
        ));

        $this->addElement('checkbox', 'show_address', array(
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'Show Address*',
            'description' => 'If you are a corporate or national company and want to display your address please click here. Note: for best results we recommend you always show a legitimate business address.'
        ));

        $this->addElement('text', 'address', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 2, 255, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Address',
            'maxlength'  => '255',
        ));

        $this->addElement('text', 'city', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 2, 255, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'City*',
            'maxlength'  => '255',
        ));

        $this->addElement('select', 'state', array(
            'validators' => array(
                array( 'StringLength', false, array( 2, 2, 'UTF-8' ) )
            ),
            'decorators'   => array( 'ViewHelper' ),
            'label'        => 'State',
            'required'     => false,
            'multioptions' => $this->_getStatesMultioptions()
        ));

        $this->addElement('text', 'zip', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                'Digits',
                array( 'StringLength', false, array( 5, 5, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'ZIP',
            'maxlength'  => '5',
        ));

        $this->addElement('text', 'phone', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 2, 100, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Phone',
            'maxlength'  => '100',
        ));

        $this->addElement('text', 'website', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 2, 255, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Website',
            'maxlength'  => '100',
        ));

    	$this->addElement('text', 'mail', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', true, array( 4, 255 ) ),
                array( 'EmailAddress', true )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'E-mail',
            'maxlength'  => '255',
        ));

        $this->addElement('text', 'business_since', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                new Zend_Validate_Digits(),
                new Zend_Validate_Date(array( 'format' => 'yyyy' )),
                new Zend_Validate_Between(array( 'min' => 1930, 'max' => date('Y') ))
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Business Since',
            'maxlength'  => '4',
        ));

        $this->addElement('text', 'owner', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 2, 255, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Owner',
            'maxlength'  => '255',
        ));

        $this->addElement('textarea', 'about_us', array(
             'filters' => array(
                'StringTrim',
                new Zend_Filter_StripTags()
             ),
            'validators' => array(
                array( 'StringLength', false, array( 1, 65536, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'About Us',
            'rows'       => 5,
        ));
        
        $this->addElement('file', 'video', array(
            'validators' => array(
                array( 'File_Size', true, 100 * 1024 * 1024 ),
                array( 'File_Extension', true, array(
                    'mp4'
                )),
                array( 'File_MimeType', true, array(
                    'application/mp4',
                    'video/mp4',
                    'video/x-flv'
                )),
            ),
            'decorators'  => array( 'File' ),
            'required'    => false,
            'label'       => 'Video',
            'description' => 'This video is used to show users and or video explaining your services and what you do as a business. Please attach MP4 video file up to 100 Mb in size.'
        ));
        
        $this->addElement('text', 'facebook_link', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 2, 100, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Facebook Link',
            'maxlength'  => '100',
        ));

        $this->addElement('text', 'twitter_link', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 2, 100, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Twitter Link',
            'maxlength'  => '100',
        ));

        $this->addElement('text', 'linkedin_link', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 2, 100, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Linkedin Link',
            'maxlength'  => '100',
        ));
        
        $this->addElement('text', 'google_link', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 2, 100, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Google+ Link',
            'maxlength'  => '100',
        ));

        $this->addElement("text", "yelp_link", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array( "StringLength", false, array(2, 100, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "Yelp Link",
            "maxlength" => "100",
        ));
        
        $this->addElement('textarea', 'offered_services', array(
            'filters'    => array( 'StringTrim' ),
            'validators' => array(
                array( 'StringLength', false, array( 2, 65536, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'label'      => 'Offered Services',
            'rows'       => 5,
        ));

        $this->addElement("textarea", "review_email_text", array(
            "filters" => array("StringTrim"),
            "validators" => array(
                array( "StringLength", false, array(0, 65536, "UTF-8"))
            ),
            "decorators" => array("ViewHelper"),
            "required" => false,
            "label" => "Review Email Additional Text",
            "rows" => 5,
        ));
        
        $this->addElement('hidden', 'latitude', array(
            'validators' => array(
                array( 'StringLength', false, array( 1, 20, 'UTF-8' ) )
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'maxlength'  => '20',
        ));

        $this->addElement('hidden', 'longitude', array(
            'validators' => array(
                array( 'StringLength', false, array( 1, 20, 'UTF-8' ))
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => false,
            'maxlength'  => '20',
        ));

        $this->addElement('file', 'logo', array(
            'validators' => array(
                array( 'File_Size', true, 1024 * 1024 ),
                array( 'File_Extension', true, array(
                    'jpg',
                    'jpeg',
                    'png'
                )),
                array( 'File_MimeType', true, array(
                    'image/jpeg',
                    'image/png',
                )),
            ),
            'decorators'  => array( 'File' ),
            'required'    => false,
            'label'       => 'Company Logo',
            'description' => 'JPEG/PNG image file up to 1 Mb in size.'
        ));
    }

    /**
     * Get states
     * @return array
     */
    protected function _getStatesMultioptions() {
        return $this->getView()->states()->getStatesArray();
    }

    /**
     * Get available categories
     * @return array
     */
    protected function _getCategoriesMultioptions() {
        $table = Doctrine_Core::getTable('Companies_Model_Category');
        $categories = $table->findAll();
        $options = array();
        //$options[0] = 'Uncategorized';

        foreach ($categories as $category) {
            $options[$category->id] = $category->name;
        }

        asort($options);

        return $options;
    }
}