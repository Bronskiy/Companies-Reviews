<?php

require_once 'common.php';

/**
 * Rating goal detection
 */
class Rating extends Task
{
    /**
     * Start checking day  
     */
    const START_DATE = '01-01-2013';
    
    /**
     * Num days after wich need to check rating
     */
    const CHECK_PERIOD_DAYS = 14;
    
    /**
     * Num days after wich system should send notification to the company owner 
     */
    const NOTIFY_PERIOD_DAYS = 10;
    
    /**
     * locked file name 
     */
    const LOCK_FILE_NAME = 'locked_rating_file.tmp';
    
    /**
     * Main execution method 
     */
    public function exec()
    {
        try {
            parent::exec();
            $companies = array();
            
            if($this->_checkPeriodExpired()) {
                $companies = $this->_getCompanies($this->_getPeriodStartDateFromDaysNum());
                $period = self::CHECK_PERIOD_DAYS;
            }elseif($this->_notifyPeriodExpired()) {
                $companies = $this->_getCompanies($this->_getPeriodStartDateFromDaysNum());
                $period = self::NOTIFY_PERIOD_DAYS;
            }
            
            foreach ($companies as $aCompany) {
                $this->_notifyOwner($aCompany, $period);
            }
        }catch(Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * Return full locking file name path
     * 
     * @return string 
     */
    protected function _getLockedFileName()
    {
        return realpath(APPLICATION_PATH . '/../tmp') . '/' . self::LOCK_FILE_NAME;
    }
    
    /**
     * Mailing
     * 
     * @param array $aCompany
     * @param int $period 
     */
    private function _notifyOwner(array $aCompany, $period)
    {
        $templateVars = array(
            'userName' => !empty($aCompany['user_name']) ? $aCompany['user_name'] : $aCompany['mail'],
            'companyName' => $aCompany['company_name'],
            'ratingGoal' => $aCompany['rating_goal'],
            'reviewsCount' => $aCompany['reviews_count'],
            'period' => $period
        );

        $this->getView()->assign($templateVars);
        $body = $this->getView()->render('cron-rating.phtml');

        $mailConfig = array(
            'toMail' => $aCompany['mail'],
            'body' => $body,
            'fromText' => 'Revudio',
            'subject' => 'Rating Notification');

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }

    /**
     * If check period expired
     * 
     * @return type 
     */
    private function _checkPeriodExpired()
    {
        $startDate = new DateTime($this->_getStartDate());
        $endDate = new DateTime();
        $interval = $endDate->diff($startDate);
        return $interval->days % self::CHECK_PERIOD_DAYS === 0;
    }
    
    /**
     * If notify period expired
     * 
     * @return type 
     */
    private function _notifyPeriodExpired()
    {
        $startDate = new DateTime($this->_getStartDate());
        $endDate = new DateTime();
        $interval = $endDate->diff($startDate);
        //echo $interval->days % self::CHECK_PERIOD_DAYS;
        return $interval->days % self::CHECK_PERIOD_DAYS === self::NOTIFY_PERIOD_DAYS;
    }
    
    
    
    /**
     * Find companies with setted rating goal less than reviews count for current
     * period
     * 
     * @param string $dateStart 
     *  mysql (Y-m-d) formated date string from which reviews count check begining
     * 
     * @return array 
     */
    private function _getCompanies($dateStart)
    {
        $statusActive  = Companies_Model_Company::STATUS_ACTIVE;
        $statusExpired = Companies_Model_Company::STATUS_EXPIRED;
        
        $sql = "SELECT c.id, c.name AS company_name, c.rating_goal, 
                 u.id AS userId, u.mail, u.name AS user_name,
                 COUNT(r.company_id) AS reviews_count
                 FROM revudio.companies c
                 JOIN revudio.users u ON u.company_id = c.id
                 LEFT JOIN revudio.reviews r ON r.company_id = c.id
                 WHERE (r.company_id IS NULL 
                        OR r.created_at >= ? AND c.rating_goal IS NOT NULL)
                 AND c.status IN (?, ?) 
                 GROUP BY c.id
                 HAVING reviews_count < c.rating_goal";

        return $this->_db->fetchAll($sql, array($dateStart, $statusActive, $statusExpired));
    }
    
      
    /**
     * Returns mysql formated ('Y-m-d') date string 
     * 
     * @param int $days num days for subtract from current date
     */
    private function _getPeriodStartDateFromDaysNum($days = self::CHECK_PERIOD_DAYS)
    {
        $days = (int)$days;
        $sModify = '-' . $days . 'day';
        $now = new DateTime();
        return $now->modify($sModify)->format('Y-m-d');
    }
    
    /**
     * Get start checking date
     * 
     * @return type 
     */
    private function _getStartDate()
    {
        return self::START_DATE;
    }
}

$rating = new Rating();
$rating->exec();