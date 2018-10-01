<?php

require_once 'common.php';

/**
 * Check expired cards
 */
class CheckExpiredCardsTask extends Task {
    /**
     * Lock file name
     */
    const LOCK_FILE_NAME = 'check_expired_cards.lock';

    /**
     * Execute script
     */
    public function exec() {
        try {
            parent::exec();

            $cards = Companies_Model_CompanyCardTable::getInstance()->getExpiring();
            $mailer = Main_Service_Models::getMailer();
            $admins = Users_Model_UserTable::getInstance()->getAdmins();

            foreach ($cards as $card) {
                try {
                    $mailer->notifyBusinessOwnerCardExpiring($card->Company);

                    foreach ($admins as $admin) {
                        $mailer->notifyAdminCardExpiring($admin, $card->Company);
                    }
                } catch(Exception $e) {
                    echo "Can't send notification e-mail: " . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }        
    }

    /**
     * Return full lock file name path
     * @return string 
     */
    protected function _getLockedFileName() {
        return realpath(APPLICATION_PATH . '/../tmp') . '/' . self::LOCK_FILE_NAME;
    }
}

$task = new CheckExpiredCardsTask();
$task->exec();
