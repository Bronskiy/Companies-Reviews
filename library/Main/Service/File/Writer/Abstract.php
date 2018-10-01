<?php
abstract class Main_Service_File_Writer_Abstract
{
    /**
     * File name without extension
     * 
     * @var string 
     */
    protected $_fileName;
    
    /**
     * Path to file (absolute)
     * 
     * @var type 
     */
    protected $_path;
    
    /**
     * File open mode
     * 
     * @var type 
     */
    protected $_mode = 'w';
    
    /**
     * Data to write
     * 
     * mixed
     */
    protected $_data;
    
    /**
     * Write data into stream
     *  
     */
    abstract public function write(); 
    
    /**
     * Constructor
     * 
     * @param string $path
     * @param string $fileName 
     */
    public function __construct($path = '', $fileName = '', $data = null) 
    {
        $this->setPath($path)->setFileName($fileName)->setData($data);
    }
    
    /**
     * Set file absolute path
     * 
     * @param string $path 
     * @return \Main_Service_File_Writer_Abstract 
     */
    public function setPath($path)
    {
        if($path && is_string($path)) {
            $this->_path = $this->_normalizePathInfo($path);
        }
        return $this;
    }
    
    /**
     * Set file name
     * 
     * @param string $fileName
     * @return \Main_Service_File_Writer_Abstract 
     */
    public function setFileName($fileName)
    {
        if($fileName && is_string($fileName)) {
            $this->_fileName = $this->_normalizeFileName($fileName);
        }
        return $this;
    }
    
    /**
     * Set data to write
     * 
     * @param mixed $data 
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }
    
    /**
     * File open mode
     * 
     * @param string $mode 
     */
    public function setMode($mode)
    {
        if($mode && is_string($mode)) {
            $this->_mode = $mode;
        }
        return $this;
    }
    
    /**
     * Returns write file name
     * 
     * @return string | null; 
     */
    public function getFileName()
    {
        return $this->_fileName;
    }
    
    /**
     * Returns abs path to file directory
     * 
     * @return string | null;
     */
    public function getPath()
    {
        return $this->_path;
    }
    
    /**
     * Full file path with file name
     * 
     * @return string; 
     */
    public function getFullFileName()
    {
        return $this->getPath() . $this->getFileName();
    }
    
    /**
     * Normailizing
     *
     * @param string $path
     */
    protected function _normalizePathInfo($path)
    {
        $path = str_replace('\\', '/', $path);
        
        if(mb_substr($path, -1) != '/') {
            $path .= '/';
        }
        return $path;
    }
    
    /**
     * Normalizing file name
     * 
     * @param string $name 
     */
    protected function _normalizeFileName($name)
    {
        return str_replace(array('.', '/'), '', $name);
    }
    
}