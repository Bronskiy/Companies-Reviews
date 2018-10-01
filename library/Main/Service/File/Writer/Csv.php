<?php
class Main_Service_File_Writer_Csv extends Main_Service_File_Writer_Abstract
{
    const EXTENSION = '.csv';
    const MIME = 'text/csv';
    const SEPARATOR = ',';
    
    private $_handle;
    
    /**
     * Write file in csv format
     * 
     * @return boolean
     * @throws Exception 
     */
    public function write()
    {
        if(!$this->_openFile()){
            throw new Exception('Can not open file for writing');
        }
        $sData = '';
        if(is_string($this->_data)) {
            $sData = $this->_data;
        }elseif(is_array($this->_data)) {
            $sData = '';
            foreach($this->_data as $row) {
                if(is_string($row)) {
                    $sData .= $row . PHP_EOL;
                }elseif(is_array($row)) {
                    $this->_filterArrayData($row);
                    $sData .= implode(self::SEPARATOR, $row) . PHP_EOL;
                }                
            }            
        }
        @fwrite($this->_handle, $sData);
        fflush($this->_handle);
        fclose($this->_handle);
        return true;
    }
    
    /**
     * Filtering data in array
     * 
     * @param array $row
     */
    protected function _filterArrayData(array &$row)
    {
        $row = array_filter($row, function($item){
            return !(is_array($item) || is_object($item)); 
        });
        
        array_walk($row, function(&$item, $key){
            $item = str_replace('"', '""', $item);
            $item = '"' . $item . '"';
        });
    }
    
    protected function _openFile()
    {
        $file = $this->getFullFileName();
        $this->_handle = @fopen($file, $this->_mode);
        return is_resource($this->_handle);
    }
    
    /**
     * File name with extension
     * 
     * @return string
     */
    public function getFullFileName() 
    {
        return parent::getFullFileName() . self::EXTENSION;
    }
}