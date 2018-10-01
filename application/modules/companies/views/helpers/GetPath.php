<?php 
/**
 * Return path to the folders by name
 *  
 */
class Zend_View_Helper_GetPath extends Zend_View_Helper_Abstract
{
    const DIR_SEPARATOR = '/';
    
    /**
     * Generate correct path to object (image, video, file, etc...)
     * 
     * @param array $data
     * @param string $dirName
     * @param string $name
     * 
     * @return string | false 
     */
    public function getPath(array $data, $dirName, $objName = '')
    {
        if(isset($data[$dirName])) {
            $separator = '';
            
            if(substr($data[$dirName], -1) != self::DIR_SEPARATOR) {
                $separator = self::DIR_SEPARATOR;
            }  
            return $data[$dirName] . $separator . $objName;
        }
        
        return false;
    }
}