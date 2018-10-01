<?php
abstract class Main_Service_Company_Rating_Abstract
{
    /**
     * Rating value for conversion
     * 
     * @var float 
     */
    protected $_rating = 0;
    
    const MAX_RATING_VAL = 1;
    const MIN_RATING_VAL = 0;
    /**
     * Method must return converting rating value
     *  
     */
    abstract public function getRating();
    
    public function __construct($rating) {
        $this->setRatingVal($rating);
    }
    
    /**
     * Sets rating value which will be coverted by specific class
     * 
     * @param float $val
     * @return boolean 
     */
    public function setRatingVal($val)
    {
        if(is_numeric($val)) {
            if($val > self::MAX_RATING_VAL) {
                $val = self::MAX_RATING_VAL;
            }
            if($val < self::MIN_RATING_VAL) {
                $val = self::MIN_RATING_VAL;
            }
            $this->_rating = (float)$val;
            return TRUE;
        }
        if($val === null) {
            $this->_rating = null;
        }
        return FALSE;
    }
}