<?php
/**
 * Company image downloader 
 *  
 */
class Main_Service_Download_Company_Image extends Zend_File_Transfer
{
    public function __construct($adapter = 'Http', $direction = false, 
                                $options = array()) 
    {
        parent::__construct($adapter, $direction, $options);
        $this->_setOptions();
    }
    
    /**
     * Setting default options for downloading
     *  
     */
    protected function _setOptions()
    {
        $this->addValidator('Extension',false, array('jpeg', 'jpg', 'png', 'case' => FALSE));
        
        $this->addValidator('MimeType', false, 
                            array('application/octet-stream', 
                                  'image/jpeg', 'image/png', 'headerCheck'=>TRUE));
        
    }
    
    
}