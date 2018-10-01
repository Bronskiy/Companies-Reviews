<?php

/**
 * Main sender
 */
abstract class Main_Mail_Abstract {
    protected $_fromMail;
    protected $_toMail;
    protected $_fromText;
    protected $_subject;
    protected $_body = '';
    protected $_mailer = null;
    protected $_config = null;

    /**
     * Constructor
     * @param array $data
     */
    public final function __construct(array $data) {
        $this->_mailer = new Zend_Mail('UTF-8');
        $this->_config = Main_Service_ConfigsLoader::getConfig();
        $this->_prepareMail($data);
        $this->_setTransport($data);
    }

    /**
     * Send mail
     */
    public function send() {
        $this->_mailer->setFrom($this->_fromMail, $this->_fromText)
            ->addTo($this->_toMail)
            ->setBodyHtml($this->_body)
            ->setHeaderEncoding(Zend_Mime::ENCODING_BASE64)
            ->setSubject($this->_subject)
            ->setDate(time())
            ->setMessageId(true)
            ->send();
    }
    
    /**
     * Set mail body
     * @param string $body
     * @return Main_Mail_Abstract provides fluent interface
     */
    public function setBody($body) {
        if (is_string($body)) {
            $this->_body = $body;
        }

        return $this;
    }

    /**
     * Prepare mail
     * @param array $data
     * @throws Exception
     */
    protected function _prepareMail(array $data) {
        if (empty($data["toMail"])) {
            throw new Exception("Destination email is not set");
        }

        $this->_toMail = $data["toMail"];
        $this->_body = !empty($data["body"]) ? $data["body"] : "";
        $this->_fromMail = !empty($data["fromMail"]) ? $data["fromMail"] : $this->_config->email->fromMail;
        $this->_fromText = !empty($data["fromText"]) ? $data["fromText"] : "";
        $this->_subject = !empty($data["subject"]) ? $data["subject"] : "";
    }

    /**
     * Set transport
     * @param array $data
     * @return mixed
     */
    abstract protected function _setTransport(array $data);
}
