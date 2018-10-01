<?php
abstract class Main_Service_Dir_Generator_Abstract 
{
    protected $_mode = 0755;
    
    protected $_foldersPathsFromRule = array();
    
    /**
     *  Get rule for dir structure
     * 
     */
    abstract public function getGenerationRule();
    
    /**
     * Function must return path to cur dir parent directory 
     * @return string  
     */
    abstract public function getMainDir($isAbs = true);
    
    /**
     * Main method for recursive dirs generation
     * 
     */
    public function generateFoldersByRule()
    {
        $rule = $this->getGenerationRule();
        foreach ($rule as $folderName => $data) {
            $this->_generateFolders($folderName, $data);
        }
        return true;
    }
    
    /**
     * Return path to folder by folder name
     * 
     *    If folder has abs path function will try to generate only folder name
     *    before serach
     * 
     * @param type $name
     * @param type $isAbs 
     */
    public function getFolderPath($name, $isAbs = true)
    {
        $rule = $this->getGenerationRule();
        $path = false;
        foreach ($rule as $folderName => $data) {
            // get only folder name from full path for main dirs
            if(basename($folderName) == $name) {
                $path = $folderName;
                break;
            }else {
                $path = $this->_getFolderPath($name, $folderName, $data);
                if($path != false) break;
            }
        }
        
        if($path != false && $isAbs == false) {
            $path = str_replace($this->getRestrictedPath(), '', $path);
        }
        return $path;
    }
    
    /**
     * Public main path where all dirs sholud be placed
     * 
     * @return string 
     */
    public final function getRestrictedPath()
    {
        // WARNING do not use realpath here
        return APPLICATION_PATH . '/../public';
    }
    
    /**
     * Generation paths info from rule
     * 
     * returned result: array('dirName'=>'path_to_folder')
     *      path_to_folder - absolute or relative path to dir, depends on $isAbs
     * 
     * @param bool $isAbs 
     * @return array 
     */
    public function getFoldersPathsFromRule($isAbs = true)
    {
        $this->generateFoldersByRule();
        $rule = $this->getGenerationRule();
        
        foreach ($rule as $folderName => $data) {
            if($isAbs === false) {
                $folderName = str_replace($this->getRestrictedPath(), '', $folderName);
            }
            $this->_foldersPathsFromRule[basename($folderName)] = $folderName;
            $this->_getFoldersPathsFromRule($folderName,$data);
        }
        return $this->_foldersPathsFromRule;
    }
    
    /**
     * Recursive dirs path generation
     * 
     * @param string $folderName
     * @param array $data 
     */
    private function _getFoldersPathsFromRule($folderName, $data)
    {
        if(!empty($data['folders']) && is_array($data['folders'])) {
            foreach($data['folders'] as $folderNameChild => $dataChild) {
                $folderNameNew = $folderName . '/' .$folderNameChild;
                $this->_foldersPathsFromRule[$folderNameChild] = $folderNameNew;
                $this->_getFoldersPathsFromRule($folderNameNew, $dataChild);
            }
        }
    }
    
    /**
     * Recursive dir generator
     * 
     *  Apache process must have appropriate rights
     * 
     * @param string $folderName (abs path!!!)
     * @param array $data 
     */
    protected function _generateFolders($folderName, $data)
    {
        // path to folder must contained in restricted main path 
        if(strpos($folderName,  $this->getRestrictedPath()) === FALSE) {
            return FALSE;
        }
        $mode = isset($data['mode']) ? $data['mode'] : $this->_mode;
        $res = true;
        if(!is_dir($folderName)) {
            $res = @mkdir($folderName, $mode);
        }
        
        if($res && !empty($data['folders']) && is_array($data['folders'])) {
            foreach($data['folders'] as $folderNameChild => $dataChild) {
                $folderNameChild = $folderName . '/' .$folderNameChild;
                unset($data);
                $this->_generateFolders($folderNameChild, $dataChild);
            }
        }
        return $res;
    }
    
    /**
     * Recursive search for folder name
     *
     * Returns abs path to the first finded folder by name
     * 
     * @param string $name
     * @param string $folderName
     * @param array $data
     * 
     * @return boolean | string
     */
    protected function _getFolderPath($name, $folderName, $data)
    {
        if(basename($folderName) == $name) {
            return $folderName;
        }
        if(!empty($data['folders']) && is_array($data['folders'])) {
            foreach($data['folders'] as $folderNameChild => $dataChild) {
                $folderNameNew = $folderName . '/' .$folderNameChild;
                unset($data);
                $path = $this->_getFolderPath($name, $folderNameNew, $dataChild);
                if($path !== false) return $path;
            }
        }
        return false;
    }
    
}