<?php

/**
 * SMTP settings
 */
class Main_Mail_Smtp extends Main_Mail_Abstract {
    /**
     * Set transport
     * @param array $data
     */
    protected function _setTransport(array $data) {
        $smtpData = $this->_config->email->smtp->toArray();

        if (!empty($data['smtp']) && is_array($data['smtp'])) {
            $smtpData = array_merge($smtpData, $data['smtp']);
        }

        $tr = new Zend_Mail_Transport_Smtp($smtpData['server'], $smtpData);

        Zend_Mail::setDefaultTransport($tr);
    }

}