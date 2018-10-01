<?php

class Upload_Form_Image_Images extends Zend_Form
{
    protected $_serviceLayer;
    /**
     * @param object $_serviceLayer
     * @param array $options
     */
    function  __construct($_serviceLayer, $options = null) {
        $this->_serviceLayer = $_serviceLayer;
        parent::__construct($options);
    }

    /**
     * Form initialization
     *
     * @return void
     */
    public function init()
    {
        $this->setName(strtolower(__CLASS__));

        $id = new Zend_Form_Element_Hidden('id');
        $this->addElement($id);
        
        $imageFilename = new Zend_Form_Element_File('image_filename');
        $imageFilename->setAttrib('style', 'top: 0pt; left: 0pt;')
            ->addValidator('IsImage')
            ->setDestination($this->_serviceLayer->getImageAbsoluteUploadPath())
            ->addValidator('Size', false, 1048576)
            ->addValidator('Extension', false, 'jpg,jpeg,png,gif');
        $uniqOpt = array('targetDir' => $this->_serviceLayer->getImageAbsoluteUploadPath(),
                         'nameLength' => 10);
        $imageFilename->addFilter(new ZFEngine_Filter_File_SetUniqueName($uniqOpt));
        $imageFilename->addFilter(new ZFEngine_Filter_File_ImageResize(array(
            'width' => $this->_serviceLayer->getOriginalSize('width'),
            'height' =>$this->_serviceLayer->getOriginalSize('height'),
            'saveProportions' =>true )
        ));
        $this->addElement($imageFilename);


        $this->setEnctype('multipart/form-data');
        

        return $this;
    }



}