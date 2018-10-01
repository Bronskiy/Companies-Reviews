<?php
class Main_Service_File_Factory
{
    /**
     * Check Writer class existance from type
     * 
     * @param type $type
     * @return boolean 
     */
    public function isWriterExists($type)
    {
        $type = $this->_formatType($type);
        $file = __DIR__ . '/Writer/' . $type . '.php';
        
        if(!is_file($file)) return false;

        try {
            $refClass = new ReflectionClass($this->_getWriterClassNameFromType($type));
            
            if($refClass->isAbstract()
               || ! $refClass->isSubclassOf('Main_Service_File_Writer_Abstract'))
            {
                return false;
            }
        }catch(Exception $e) {
            return false;
        }       
        
        return true;
    }
    
    /**
     * Instantinating Writer class
     * 
     * @param string $type Writer class type
     * @param string $path
     * @param string $fileName
     * @return Main_Service_File_Writer_Abstract 
     */
    public function getWriter($type, $path = '', $fileName = '', $data = null)
    {
        if($this->isWriterExists($type)) {
            $writerClass = $this->_getWriterClassNameFromType($this->_formatType($type));
            return new $writerClass($path, $fileName, $data);
        }
        throw new Exception('Writer whith type ' . $type . ' Does not exists');
    }
    
    /**
     * Get writer class name
     * 
     * @param string $type 
     */
    protected function _getWriterClassNameFromType($type)
    {
        return 'Main_Service_File_Writer_' . $type;
    }
    
    /**
     * Formatting type name string
     * 
     * @param string $type
     * @return string 
     */
    protected function _formatType($type)
    {
        return ucfirst(mb_strtolower($type, 'utf-8'));
    }
}