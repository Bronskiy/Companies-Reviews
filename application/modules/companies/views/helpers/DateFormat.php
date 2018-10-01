<?php 
/**
 * Date format helper
 *  
 */


class Zend_View_Helper_DateFormat extends Zend_View_Helper_Abstract
{
    const DEFAULT_DATE_FORMAT = 'm/d/Y';
    
    public function dateFormat()
    {
        return $this;
    }
    
    /**
     * Formatting date from string
     * 
     * @param type $sDate
     * @param type $format 
     */
    public function formatFromString($sDate, $format = '')
    {
        if(empty($sDate)) return;
        $format = empty($format) ? self::DEFAULT_DATE_FORMAT : $format;
        return date($format, strtotime($sDate));
    }
}