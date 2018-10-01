<?php
/**
 * Filter for rating
 * 
 * Makig correct float value from percent 
 * 
 */
class Main_Service_Filter_PercentToFloat implements Zend_Filter_Interface
{
    
    public function filter($value)
    {
        $value = (int)$value;
        
        if($value <= 0)  $value = 0;
        if($value > 100) $value = 100;
        
        return (float)($value / 100);
    }
}