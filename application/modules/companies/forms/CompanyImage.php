<?php

/**
 * Company gallery image form
 */
class Companies_Form_CompanyImage extends Zend_Form
{
    /**
     * Company image form
     */
    public function init()
    {
        parent::init();    	

        $this->addElement('file', 'image', array(
            'validators' => array(
                array( 'File_Count', true, 1 ),
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
            'required'    => true,
            'label'       => 'Image:*',
            'description' => 'JPEG/PNG image file up to 1 Mb in size.'
        ));
    }
}