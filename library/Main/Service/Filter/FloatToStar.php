<?php

/**
 * Make correct star value from float
 */
class Main_Service_Filter_FloatToStar extends Main_Service_Filter_StarToFloat
{
    /**
     * Float to star
     * @param float $value
     * @return float
     */
    public function filter($value)
    {
        return (float) $value * 5;
    }
}