<?php
class Main_Service_Company_Rating_Loader 
{
    protected static  $_ratingClassNames = array('letter', 'percent', 'star');
    
    public static function getRatingInstance($name, $rating = 0)
    {
        $className = 'Main_Service_Company_Rating_';
        $newclassName = $className . ucfirst(strtolower($name));
        if(self::classExists($name, $newclassName)) {
            return new $newclassName($rating);
        }
        return null;
    }
    
    public static function getAllRatings($rating)
    {
        $ratingClasses = array();
        foreach(self::$_ratingClassNames as $name) {
            $oRating = self::getRatingInstance($name, $rating);
            if($oRating !== null && $oRating instanceof Main_Service_Company_Rating_Abstract) {
                $ratingClasses[$name] = $oRating;
            }
        }
        return $ratingClasses;
    }
    
    public static function classExists($name, $className)
    {
        $curDir = dirname(__FILE__);
        $file = $curDir . '/' . ucfirst(strtolower($name)) . '.php';
        if(is_file($file)) {
            require_once $file;
            $reflect = new Zend_Reflection_File($file);
            $classes = $reflect->getClasses();
            if(empty($classes)) return FALSE;
            return (strcmp($className, $classes[0]->getName()) === 0) ? TRUE : FALSE;
        }
        return FALSE;
    }
}