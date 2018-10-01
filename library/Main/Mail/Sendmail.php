<?php
class Main_Mail_Sendmail extends Main_Mail_Abstract
{
    protected function _setTransport(array $data)
    {
        $transport = new Zend_Mail_Transport_Sendmail();
        Zend_Mail::setDefaultTransport($transport);
    }
}