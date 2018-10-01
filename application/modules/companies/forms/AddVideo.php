<?php

/**
 * Add video to review form
 */
class Companies_Form_AddVideo extends Main_Forms_ZForm
{
    /**
     * Form initialization
     */
    public function init()
    {
        parent::init();    	

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
            'required'    => true,
            'label'       => 'Video*:',
            'description' => 'Android/iOS video file (3gp/mov) up to 100 Mb in size.'
        ));
    }
}