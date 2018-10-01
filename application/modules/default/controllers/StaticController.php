<?php

/**
 * Static controller
 */
class StaticController extends Main_Controller_Action {
    /**
     * Privacy policy page
     */
    public function privacyPolicyAction() {
        $this->view->title = 'Privacy Policy';
    }

    /**
     * Terms page
     */
    public function termsAction() {
        $this->view->title = 'Terms & Conditions';
    }

    /**
     * Maintenance page
     */
    public function maintenanceAction() {
        $this->_response->setHttpResponseCode(503);
        $this->_response->setHeader("Retry-After", 6 * 3600, true);
        $this->view->title = 'Maintenance';
    }

    /**
     * How it works page
     */
    public function howItWorksAction() {
        $this->view->sentemail = false;
        $request = $this->getRequest();
        $post = $request->getPost();

        $form = new Default_Form_Contactus();
        $form->removeElement("business_name");

        if ($request->isPost()) {
            if ($form->isValid($post)) {
                $message = 'From: ' . $post['name'] . chr(10) . 'Email: ' . $post['email'] . chr(10);
                $message .= 'Message: ' . $post['comment'] . chr(10).'Phone Number:'.$post['phone'].chr(10).'How did you hear about us?:'.$post['hear'];

                $mailConfig = array(
                    'toMail' => 'info@revudio.com',
                    'body' => $message,
                    'from' => $post['email'],
                    'subject' => 'Contact Us (How It Works)'
                );

                $mail = new Main_Mail_Smtp($mailConfig);
                $this->view->sentemail = true;
                $mail->send();
            }
        }

        $this->view->form = $form;
        $this->_helper->layout->setLayout('wide');
    }

    /**
     * Pricing page
     */
    public function pricingAction() {
        $this->view->sentemail = false;
        $request = $this->getRequest();
        $post = $request->getPost();

        $form = new Default_Form_Contactus();
        $form->removeElement("business_name");

        if ($request->isPost()) {
            if ($form->isValid($post)) {
                $message = 'From: ' . $post['name'] . chr(10) . 'Email: ' . $post['email'] . chr(10);
                $message .= 'Message: ' . $post['comment'] . chr(10).'Phone Number:'.$post['phone'].chr(10).'How did you hear about us?:'.$post['hear'];

                $mailConfig = array(
                    'toMail' => 'info@revudio.com',
                    'body' => $message,
                    'from' => $post['email'],
                    'subject' => 'Contact Us (Pricing)'
                );

                $mail = new Main_Mail_Smtp($mailConfig);
                $mail->send();
                $this->view->sentemail = true;
            }
        }

        $this->view->form = $form;
        $this->_helper->layout->setLayout('wide');
    }

    /**
     * Contact Us
     */
    public function contactusAction() {
        $this->view->sentemail = false;
        $this->view->title = 'Contact Us';
    
        $form = new Default_Form_Contactus();

        $request = $this->getRequest();
        $post = $request->getPost();

        if ($request->isPost()) {
            if ($form->isValid($post)) {
                $message = 'From: ' . $post['name'] . chr(10) . 'Email: ' . $post['email'] . chr(10);
                $message .= 'Message: ' . $post['comment'] . chr(10).'Phone Number:'.$post['phone'];

                $mailConfig = array(
                    'toMail' =>'info@revudio.com',
                    'body' => $message,
                    'from' => $post['email'],
                    'subject'  => $post['business_name']
                );

                $mail = new Main_Mail_Smtp($mailConfig);
                $mail->send();
                $this->view->sentemail = true;
            }
        }

        $this->view->form = $form;
    }

    public function aboutUsAction(){
        $this->view->sentemail = false;
        $this->view->title = 'About Us';
    }
}
