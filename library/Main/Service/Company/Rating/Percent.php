<?php
class Main_Service_Company_Rating_Percent extends Main_Service_Company_Rating_Abstract
{
    public function getRating()
    {
        return (int)($this->_rating * 100);
    }
}