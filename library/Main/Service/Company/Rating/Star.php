<?php

/**
 * Star rating class
 */
class Main_Service_Company_Rating_Star extends Main_Service_Company_Rating_Abstract
{
    protected $_rule = array(
        "0.0" => array(-0.5, 0.0),
        "0.5" => array(0.0,  0.1),
        "1.0" => array(0.1,  0.2),
        "1.5" => array(0.2,  0.3),
        "2.0" => array(0.3,  0.4),
        "2.5" => array(0.4,  0.5),
        "3.0" => array(0.5,  0.6),
        "3.5" => array(0.6,  0.7),
        "4.0" => array(0.7,  0.8),
        "4.5" => array(0.8,  0.9),
        "5.0" => array(0.9,  1.0)
    );

    const MAX_RESULT = 5;
    
    public function getRating()
    {
        if ($this->_rating === null)
            return 0;
               
        foreach ($this->_rule as $val => $compare) {
            if ($this->_rating > $compare[0] && $this->_rating <= $compare[1]) {
                return (float) $val;
            }
        }

        return self::MAX_RESULT;
    }
}