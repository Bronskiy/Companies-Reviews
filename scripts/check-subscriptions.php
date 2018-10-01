<?php

require_once 'common.php';

/**
 * Check subscriptions
 */
class CheckSubscriptionsTask extends Task {
    const LOCK_FILE_NAME = 'check-subscriptions.lock';

    /**
     * Execute task
     */
    public function exec() {
        try {
            parent::exec();
            
            $admins = Users_Model_UserTable::getInstance()->getAdmins();
            
            $this->_processExpiredCompanies($admins);
            $this->_processSuspendedCompanies($admins);
        } catch (Exception $e) {
            echo $e->getMessage();
        }        
    }
    
    /**
     * Process expired companies
     */
    private function _processExpiredCompanies(&$admins) {
        $companies = Companies_Model_CompanyTable::getInstance()->getExpiringCompanies();
        $mailer = Main_Service_Models::getMailer();

        foreach ($companies as $company) {
            $company->status = Companies_Model_Company::STATUS_EXPIRED;
            $company->save();

            try {
                $mailer->notifyBusinessOwnerCompanyExpired($company);

                foreach ($admins as $admin) {
                    $mailer->notifyAdminCompanyExpired($admin, $company);
                }
            } catch (Exception $e) {
                echo 'can not send notify mail ' . $e->getMessage();
            }            
        }
    }
    
    /**
     * Process suspended companies
     */
    private function _processSuspendedCompanies(&$admins) {
        $companies = Companies_Model_CompanyTable::getInstance()->getCompaniesToSuspend();
        $mailer = Main_Service_Models::getMailer();
        Main_Service_Models::configureBrainTree();

        foreach ($companies as $company) {
            $company->status = Companies_Model_Company::STATUS_SUSPENDED;
            $company->save();
            
            try {
                $mailer->notifyBusinessOwnerCompanySuspended($company);

                foreach ($admins as $admin) {
                    $mailer->notifyAdminCompanySuspended($admin, $company);
                }
            } catch (Exception $e) {
                echo 'can not send notify mail ' . $e->getMessage();
            }
        }
    }
    
    /**
     * Return full locking file name path
     * @return string 
     */
    protected function _getLockedFileName() {
        return realpath(APPLICATION_PATH . '/../tmp') . '/' . self::LOCK_FILE_NAME;
    }
}

$task = new CheckSubscriptionsTask();
$task->exec();