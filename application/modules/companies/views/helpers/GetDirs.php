<?php 
/**
 * Generate dirs array structure from current dirGenerator
 *  
 */
class Zend_View_Helper_GetDirs extends Zend_View_Helper_Abstract
{
    /**
     * Main method for dirs generation
     * 
     * @param Main_Service_Dir_Generator_Abstract $generator
     * @param bool $isAbs 
     */
    public function getDirs(Main_Service_Dir_Generator_Abstract $generator, $isAbs = true)
    {
        return $generator->getFoldersPathsFromRule((bool)$isAbs);
    }
}