<?php

/**
 * Companies_Model_Company
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Companies_Model_Company extends Companies_Model_Base_Company {
    const STATUS_NOT_ACTIVATED = "not_activated";
    const STATUS_ACTIVE = "active";
    const STATUS_DELETED = "deleted";
    const STATUS_EXPIRED = "expired";
    const STATUS_SUSPENDED = "suspended";
    const STATUS_CANCELLED = "cancelled";
    const STATUS_UNOWNED = "unowned";
    const STATUS_TAKEN = "taken";

    /**
     * Get statuses for showing the company on the website
     * @return array
     */
    public static function getActiveStatuses() {
        return array(self::STATUS_ACTIVE, self::STATUS_EXPIRED, self::STATUS_UNOWNED, self::STATUS_TAKEN);
    }

    /**
     * Get statuses when company's users are allowed to login
     * @return array
     */
    public static function getStatusesAvailableForLogin() {
        return array(self::STATUS_NOT_ACTIVATED, self::STATUS_ACTIVE, self::STATUS_EXPIRED, self::STATUS_TAKEN);
    }
    
    public function setAddress($value)
    {
        return $this->_set('address', empty($value) ? null : $value);
    }
    
    public function setCity($value)
    {
        return $this->_set('city', empty($value) ? null : $value);
    }
    
    public function setState($value)
    {
        return $this->_set('state', empty($value) ? null : $value);
    }
    
    public function setZip($value)
    {
        return $this->_set('zip', empty($value) ? null : $value);
    }
    
    public function setPhone($value)
    {
        return $this->_set('phone', empty($value) ? null : $value);
    }
    
    public function setWebsite($value)
    {
        return $this->_set('website', empty($value) ? null : $value);
    }
    
    public function setMail($value)
    {
        return $this->_set('mail', empty($value) ? null : $value);
    }
    
    public function setBusinessSince($value)
    {
        return $this->_set('business_since', empty($value) ? null : $value);
    }
    
    public function setOwner($value)
    {
        return $this->_set('owner', empty($value) ? null : $value);
    }
    
    public function setAboutUs($value)
    {
        return $this->_set('about_us', empty($value) ? null : $value);
    }
    
    public function setLatitude($value)
    {
        return $this->_set('latitude', empty($value) ? null : $value);
    }
    
    public function setLongitude($value)
    {
        return $this->_set('longitude', empty($value) ? null : $value);
    }
    
    public function setFacebookLink($value)
    {
        return $this->_set('facebook_link', empty($value) ? null : $value);
    }
    
    public function setTwitterLink($value)
    {
        return $this->_set('twitter_link', empty($value) ? null : $value);
    }
    
    public function setLinkedinLink($value)
    {
        return $this->_set('linkedin_link', empty($value) ? null : $value);
    }
    
    public function setGoogleLink($value)
    {
        return $this->_set('google_link', empty($value) ? null : $value);
    }
    
    public function setOfferedServices($value)
    {
        return $this->_set('offered_services', empty($value) ? null : $value);
    }
    public function setCodeLetter($value)
    {
        return $this->_set('code_letter', empty($value) ? null : $value);
    }
    
    public function setCodeNum($value)
    {
        return $this->_set('code_num', empty($value) ? null : $value);
    }
    
    public function setCategoryId($value)
    {
        return $this->_set('category_id', empty($value) ? null : $value);
    }
    
    public function setRatingGoal($value)
    {
        return $this->_set('rating_goal', empty($value) ? null : $value);
    }
    
}