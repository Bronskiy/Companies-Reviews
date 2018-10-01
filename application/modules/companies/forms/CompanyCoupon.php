<?php

/**
 * Company coupon form
 */
class Companies_Form_CompanyCoupon extends Main_Forms_ZForm {
    /**
     * Init form
     */
    public function init() {
        parent::init();
        
        // coupon
        $this->addElement('file', 'coupon', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('File_Size', true, 1024 * 1024),
                array('File_Extension', true, array(
                    'jpg',
                    'jpeg',
                    'png'
                )),
                array('File_MimeType', true, array(
                    'image/jpeg',
                    'image/png',
                )),
            ),
            'decorators' => array('File'),
            'required' => false,
            'label' => 'Coupon:',
            'description' => 'JPEG/PNG image file up to 1 Mb in size. We recommend to use a 620 x 200 image.'
        ));
    }
}