<?php

require_once 'common.php';

/**
 * Review processor task
 */
class ReviewProcessor extends Task {
    const LOCK_FILE_NAME = 'process-reviews.lock';
    const RECONCILIATION_RATING = 0.6;

    /**
     * Execute command
     */
    public function exec() {
        try {
            parent::exec();

            $reviews = Companies_Model_ReviewTable::getInstance()->getUnprocessed();

            foreach ($reviews as $review) {
                $review->status = Companies_Model_Review::STATUS_PROCESSING;
                $review->save();

                $allProcessed = true;
                $finished = true;

                foreach ($review->Videos as $video) {
                    if ($video->status != Companies_Model_CompanyVideo::STATUS_PROCESSED) {
                        $allProcessed = false;
                        $finished = false;

                        if ($video->status == Companies_Model_CompanyVideo::STATUS_ERROR) {
                            $review->status = Companies_Model_Review::STATUS_ERROR;
                            $finished = true;

                            break;
                        }
                    }
                }

                if ($allProcessed) {
                    $review->status = $review->rating < self::RECONCILIATION_RATING ?
                        Companies_Model_Review::STATUS_RECONCILIATION :
                        Companies_Model_Review::STATUS_PUBLISHED;
                }

                $review->save();

                if ($finished) {
                    $this->_notifyOwner($review);
                    $this->_notifyAdmin($review);

                    if ($review->status != Companies_Model_Review::STATUS_ERROR) {
                        $companyService = new Companies_Model_CompanyService();
                        $companyService->notifyReviewer($review);
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }        
    }
    
    /**
     * Owner notification by review status
     */
    private function _notifyOwner(Companies_Model_Review $review) {
        if ($review->Company->status == Companies_Model_Company::STATUS_DELETED) {
            return;
        }
        
        $user = $review->Company->Users->get(0);
        
        $templateVars = array(
            'userName' => !empty($user->name) ? $user->name : $user->mail,
            'review' => $review,
            'companyUrl' => "http://" . Main_Service_ConfigsLoader::getConfig()->domain .
                $this->getView()->urlGenerator()->companyUrl($review->Company)
        );

        $this->getView()->assign($templateVars);
        $templateName = $review->status . '.phtml';
        
        $mailBody = $this->getView()->render('converter/' . $templateName);

        $mailConfig = array(
            'toMail' => $user->mail,
            'body' => $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Review Processed'
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }

    /**
     * Admin notification
     */
    private function _notifyAdmin(Companies_Model_Review $review) {
        if ($review->Company->status == Companies_Model_Company::STATUS_DELETED) {
            return;
        }

        $admins = Users_Model_UserTable::getInstance()->getAdmins();

        foreach ($admins as $admin) {
            $templateVars = array(
                'userName' => empty($admin->name) ? $admin->mail : $admin->name,
                'review' => $review,
                'companyUrl' => "http://" . Main_Service_ConfigsLoader::getConfig()->domain .
                    $this->getView()->urlGenerator()->companyUrl($review->Company),
                'companyName' => $review->Company->name,
            );

            $this->getView()->assign($templateVars);
            $mailBody = $this->getView()->render('converter/admin.phtml');

            $mailConfig = array(
                'toMail' => $admin->mail,
                'body' => $mailBody,
                'fromText' => 'Revudio',
                'subject' => 'Review Processed'
            );

            $mail = new Main_Mail_Smtp($mailConfig);
            $mail->send();
        }
    }
    
    /**
     * Return full locking file name path
     */
    protected function _getLockedFileName() {
        return realpath(APPLICATION_PATH . '/../tmp') . '/' . self::LOCK_FILE_NAME;
    }
}

$task = new ReviewProcessor();
$task->exec();