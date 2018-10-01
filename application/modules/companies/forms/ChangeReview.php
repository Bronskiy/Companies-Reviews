<?php

/**
 * Change review form
 */
class Companies_Form_ChangeReview extends Main_Forms_ZForm
{
    /**
     * Init form
     */
    public function init()
    {
        parent::init();    	

        $this->addElement('textarea', 'review', array(
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
            'label'      => 'Review:*',
            'rows'       => 5,
        ));

        $this->addElement('hidden', 'rating', array(
            'validators' => array(
                array( 'Float', true ),
                array( 'Between', true, array( 0, 5, true ) ),
            ),
            'decorators' => array( 'ViewHelper' ),
            'required'   => true,
            'label'      => 'Rating*:',
            'value'      => 0,
        ));

        $this->addElement('file', 'video', array(
            'validators' => array(
                array( 'File_Size', true, 100 * 1024 * 1024 ),
                array( 'File_Extension', true, array(
                    'mov',
                    '3gp',
                )),
                array( 'File_MimeType', true, array(
                    'video/quicktime',
                    'video/3gpp',
                )),
            ),
            'decorators'  => array( 'File' ),
            'required'    => false,
            'label'       => 'Video:',
            'description' => 'Android/iOS video file (3gp/mov) up to 100 Mb in size.'
        ));
    }
}