<?php
class Main_Service_Company_Rating_Letter extends Main_Service_Company_Rating_Abstract
{
    /*protected $_rule = array(
      'F' => array('min'=>0, 'max'=>0.18), 'E' => array('min'=>0.18, 'max'=>0.36),
      'D' => array('min'=>0.36, 'max'=>0.54),'C' => array('min'=>0.54, 'max'=>0.72),
      'B' => array('min'=>0.72, 'max'=>0.9), 'A' => array('min'=>0.9, 'max'=>1));*/

    protected $_rule = array(
        'F' => array('min'=>0, 'max'=>0.36),
        'D' => array('min'=>0.36, 'max'=>0.54),'C' => array('min'=>0.54, 'max'=>0.72),
        'B' => array('min'=>0.72, 'max'=>0.9), 'A' => array('min'=>0.9, 'max'=>1));
    
    const MAX_RESULT = 'A';
    
    public function getRating()
    {
        foreach ($this->_rule as $letter => $compare) {
            if($this->_rating >= $compare['min'] && $this->_rating < $compare['max'])
            {
                return $letter;
            }
        }
        return self::MAX_RESULT;
    }
}