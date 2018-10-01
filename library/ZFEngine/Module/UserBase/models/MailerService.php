<?php


/**
 * @property ZFEngine_Module_UserBase_Form_Mailer_New $formNew
 */
class ZFEngine_Module_UserBase_Model_MailerService extends ZFEngine_Model_Service_Database_Abstract
{

    public function init()
    {
        $this->_modelName = 'Users_Model_Mailer';
    }


    /**
     * Send e-mail
     *
     * @param string $email
     * @param string $subject
     * @param string $body
     */
    public function sendmail($email, $subject, $body, $from = null)
    {
        return;
    	$mail = new Zend_Mail('UTF-8');

        $mail->addTo($email);
        $mail->setSubject($subject);
        $mail->setBodyHtml($body, 'UTF-8', Zend_Mime::ENCODING_BASE64);        
        if($from != null){
            $mail->setFrom($from);
        } else {
            $config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('Config');
            $mail->setFrom($config->email->noreply);
        }        
        $mail->send();
    }


    /**
     *  Processing new mailer form
     *  @param  array   $postData
     *  @param  Users_Model_UserService $user
     *  @return boolean
     */
    public function processNew($postData, $user)
    {
        $form = $this->formNew;
        if ($form->isValid($postData)) {

            switch ($postData['mode']) {
                case 'users':
                    $recipients = $user->getMapper()
                                    ->findAll();
                    break;
                case 'groups':
                    $recipients = $user->getMapper()
                                    ->getAllUsersAssociateWithRoles($postData['user_role']);
                    break;
                default:
                    break;
            }
            
            if ($recipients) {
                $this->getView()->body = $postData['message'];
                $text = $this->getView()->render('/mails/standart.phtml');

                foreach ($recipients as $recipient)
                {
                    $body = preg_replace('/\%username\%/', $recipient->login, $text);
                    $this->addMail($recipient->email, $postData['subject'], $body);
                }
                return count($recipients);
            }
        } else {
            $this->formNew->populate();
            return false;
        }
    }

    /**
     *  Saving new mail
     * 
     *  @param  string   $email
     *  @param  string   $subject
     *  @param  string   $body
     *  @return boolean
     */
    public function addMail($email, $subject, $body)
    {
        $mailer = $this->getModel(true);
        $mailer->subject = $subject;
        $mailer->body = $body;
        $mailer->email = $email;
        $mailer->save();
        return true;
    }
}
