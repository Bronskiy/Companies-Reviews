<?php

/**
 * Mailer service
 */
class Main_Service_Mailer extends Main_Service_Models {
    /**
     * Notify user about charged
     * @param Companies_Model_Company $company
     * @param $amount charged amount
     */
    public function notifySubscriptionCharged(Companies_Model_Company $company, $amount) {
        $user = $company->Users->get(0);

        if ($company->payment_date) {
            $next = new DateTime($company->payment_date);
            $next = $next->format("m/d/Y");
        } else {
            $next = "N/A";
        }

        $templateVars = array(
            'userName' => empty($user->name) ? 'Client' : $user->name,
            'amount' => sprintf("$%.2f", (float)$amount),
            'company' => $company->name,
            'plan' => $company->Plan->name,
            'next' => $next,
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('payment/subscription-charged.phtml');

        $mailConfig = array(
            'toMail' => $user->mail,
            'body' => $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Payment Approved',
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }

    /**
     * Notify admin about charged subscription payment
     * @param Users_Model_User $admin
     * @param Companies_Model_Company $company
     * @param $amount charged amount
     */
    public function notifyAdminSubscriptionCharged(Users_Model_User $admin, Companies_Model_Company $company, $amount) {
        if ($company->payment_date) {
            $next = new DateTime($company->payment_date);
            $next = $next->format("m/d/Y");
        } else {
            $next = "N/A";
        }

        $templateVars = array(
            'userName' => empty($admin->name) ? $admin->mail : $admin->name,
            'amount' => sprintf("$%.2f", (float)$amount),
            'companyLink' =>
                $this->getView()->serverUrl() .
                $this->getView()->urlGenerator()->companyUrl($company),
            'company' => $company,
            'plan' => $company->Plan->name,
            'next' => $next,
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('admin/subscription-charged.phtml');

        $mailConfig = array(
            'toMail' => $admin->mail,
            'body' => $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Payment Approved',
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }

    /**
     * Notify user about errors with subscription
     * @param Companies_Model_Company $company
     */
    public function notifySubscriptionNotCharged(Companies_Model_Company $company) {
        $user = $company->Users->get(0);
        
        $templateVars = array(
            'userName' => empty($user->name) ? 'Client' : $user->name,
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('payment/subscription-not-charged.phtml');

        $mailConfig = array(
            'toMail' => $user->mail,
            'body' => $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Payment Failed'
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }

    /**
     * Notify admin about errors with subscription
     * @param Users_Model_User $admin
     * @param Companies_Model_Company $company
     */
    public function notifyAdminSubscriptionNotCharged(Users_Model_User $admin, Companies_Model_Company $company) {
        $templateVars = array(
            'userName' => empty($admin->name) ? $admin->mail : $admin->name,
            'companyLink' =>
                $this->getView()->serverUrl() .
                $this->getView()->urlGenerator()->companyUrl($company),
            'company' => $company,
            'plan' => $company->Plan->name,
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('admin/subscription-not-charged.phtml');

        $mailConfig = array(
            'toMail' => $admin->mail,
            'body' => $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Payment Failed'
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }
    
    /**
     * Notify the business owner that company's account has expired
     * @param Companies_Model_Company $company 
     */
    public function notifyBusinessOwnerCompanyExpired(Companies_Model_Company $company) {
        $user = $company->Users->get(0);
        
        $templateVars = array(
            'userName' => empty($user->name) ? 'Client' : $user->name,
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('business-owner/company-expired.phtml');

        $mailConfig = array(
            'toMail' => $user->mail,
            'body' => $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Subscription Expired'
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }
    
    /**
     * Notify the admin that company's account has expired
     * @param Users_Model_User $admin
     * @param Companies_Model_Company $company 
     */
    public function notifyAdminCompanyExpired(Users_Model_User $admin, Companies_Model_Company $company) {
        $templateVars = array(
            'userName' => empty($admin->name) ? $admin->mail : $admin->name,
            'companyLink' => 
                $this->getView()->serverUrl() .
                $this->getView()->urlGenerator()->companyUrl($company),
            'company' => $company
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('admin/company-expired.phtml');

        $mailConfig = array(
            'toMail' => $admin->mail,
            'body' => $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Company Subscription Expired'
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }
    
    /**
     * Notify the business owner that company's account has been suspended
     * @param Companies_Model_Company $company 
     */
    public function notifyBusinessOwnerCompanySuspended(Companies_Model_Company $company) {
        $user = $company->Users->get(0);
        
        $templateVars = array(
            'userName' => empty($user->name) ? 'Client' : $user->name,
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('business-owner/company-suspended.phtml');

        $mailConfig = array(
            'toMail' => $user->mail,
            'body' => $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Account Suspended');

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }
    
    /**
     * Notify the admin that company's account has been suspended
     * @param User_Models_User $admin
     * @param Companies_Model_Company $company 
     */
    public function notifyAdminCompanySuspended(Users_Model_User $admin, Companies_Model_Company $company) {
        $templateVars = array(
            'userName' => empty($admin->name) ? $admin->mail : $admin->name,
            'companyLink' => 
                $this->getView()->serverUrl() .
                $this->getView()->urlGenerator()->companyUrl($company),
            'company' => $company
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('admin/company-suspended.phtml');

        $mailConfig = array(
            'toMail' => $admin->mail,
            'body' => $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Company Account Suspended');

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }

    /**
     * Notify the business owner that company's card will expire soon
     * @param Companies_Model_Company $company
     */
    public function notifyBusinessOwnerCardExpiring(Companies_Model_Company $company) {
        $user = $company->Users->get(0);

        $templateVars = array(
            'userName' => empty($user->name) ? 'Client' : $user->name,
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('business-owner/card-expiring.phtml');

        $mailConfig = array(
            'toMail' => $user->mail,
            'body'=> $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Subscription Alert',
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }

    /**
     * Notify the business owner that company's card will expire soon
     * @param Users_Model_User $admin
     * @param Companies_Model_Company $company
     */
    public function notifyAdminCardExpiring(Users_Model_User $admin, Companies_Model_Company $company) {
        $templateVars = array(
            'userName' => empty($admin->name) ? $admin->mail : $admin->name,
            'companyLink' =>
                $this->getView()->serverUrl() .
                $this->getView()->urlGenerator()->companyUrl($company),
            'company' => $company
        );

        $this->getView()->assign($templateVars);
        $mailBody = $this->getView()->render('admin/card-expiring.phtml');

        $mailConfig = array(
            'toMail' => $admin->mail,
            'body'=> $mailBody,
            'fromText' => 'Revudio',
            'subject' => 'Subscription Alert',
        );

        $mail = new Main_Mail_Smtp($mailConfig);
        $mail->send();
    }
}