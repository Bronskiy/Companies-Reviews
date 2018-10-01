<?php

/**
 * Make correct float value from rating
 */
class Main_Service_Filter_StarToFloat implements Zend_Filter_Interface
{
    /**
     * Filter
     * @param float $value
     * @return float
     */
    public function filter($value)
    {
        return (float) $value / 5;
    }
}