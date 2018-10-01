<?php
class Main_Service_File_Writer_Stream 
{
    /**
     * Full file name with extension
     * @var string 
     */
    private $_fileName;
    
    
    public function __construct($fileName = '') 
    {
        $this->setFullFileName($fileName);
    }
    
    /**
     * Writing file into output buffer 
     */
    public function write()
    {
        $this->_setHeaders();
        readfile($this->_fileName);
    }
    
    /**
     * Headers for writing into ouput buffer
     *  
     */
    protected function _setHeaders()
    {
        $info = pathinfo($this->_fileName);
        header ("Content-Type: application/octet-stream");
        header ("Accept-Ranges: bytes");
        header ("Content-Length: " . filesize($this->_fileName));
        header ("Content-Disposition: attachment; filename=" . $info['basename']);  
    }
    
    
    public function setFullFileName($name)
    {
        $this->_fileName = $name;
        return $this;
    }
}