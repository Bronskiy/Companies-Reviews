<?php 
/**
 * Ratings generation helper
 *  
 */
class Zend_View_Helper_Ratings extends Zend_View_Helper_Abstract
{
    
    /**
     * Main method, returns self object
     * 
     * @return Zend_View_Helper_Ratings 
     */
    public function ratings() {
        return $this;
    }
    
    /**
     * Returns rating objects array 
     * 
     * @param mixed $rating 
     */
    public function getAllRatings($rating)
    {
        return Main_Service_Company_Rating_Loader::getAllRatings($rating);
    }
}