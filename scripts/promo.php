<?php

require_once 'common.php';

/**
 * Promo task
 */
class Promo extends Task {

    /**
     * locked file name
     */
    const LOCK_FILE_NAME = 'locked_promo_file.tmp';

    public function exec() {
        try {
            parent::exec();
            $promos = $this->getAllPromos();

            foreach ($promos as $promo) {
                $this->processPromo($promo);
            }

            echo "Promos processed\n";
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Return full locking file name path
     *
     * @return string
     */
    protected function _getLockedFileName() {
        return realpath(APPLICATION_PATH . '/../tmp') . '/' . self::LOCK_FILE_NAME;
    }

    /**
     * Process a single promo
     * @param array $promo
     */
    public function processPromo(array $promo) {
        $methodName = 'find' . ucfirst($promo['status']) . 'PromoReviewMails';

        if (!method_exists($this, $methodName)) {
            return;
        }

        $mails = $this->$methodName($promo);

        foreach ($mails as $mail) {
            try {
                $this->sendMail($mail, $promo);
            } catch (Exception $e) {
                echo "Send mail error: " . $e->getMessage() . "\n";
            }
        }

        if ($mails) {
            $this->insertNewMails((int) $promo['id'], (int) $promo['company_id']);
        }

        $this->_db->query("UPDATE company_promo SET status = 'idle' WHERE id = ?", (int) $promo['id']);
    }

    /**
     * Sending mail
     *
     * @param array $message
     * @param array $promo
     */
    public function sendMail(array $message, array $promo) {
        $templateVars = array(
            'userName' => 'client',
            'content' => $promo['content']
        );

        $this->getView()->assign($templateVars);
        $body = $this->getView()->render('promo.phtml');

        $mail = new Main_Mail_Smtp(array(
            'toMail' => $message['mail'],
            'body' => $body,
            'fromText' => 'Revudio',
            'subject' => $promo['title']
        ));
        $mail->send();
    }

    /**
     * Get all active promos
     *
     * @return array
     */
    public function getAllPromos() {
        $sql = "SELECT * FROM company_promo WHERE `status` != 'idle'";
        return $this->_db->fetchAll($sql);
    }

    /**
     * Get all company reviews
     *
     * @param array $aPromo
     * @return array
     */
    public function findAllPromoReviewMails(array $aPromo) {
        $companyId = $aPromo['company_id'];

        $sql = "SELECT DISTINCT `mail` FROM `reviews`
                 WHERE `mail` != '' 
                 AND mail IS NOT NULL
                 AND company_id = ?";

        return $this->_db->fetchAll($sql, $companyId);
    }

    /**
     * Get new company review mails
     * @param array $aPromo
     * @return array
     */
    public function findNewPromoReviewMails(array $aPromo) {
        $sql = "SELECT DISTINCT `mail` FROM `reviews`
                 WHERE `mail` != '' 
                 AND mail IS NOT NULL
                 AND company_id = ?
                 AND mail NOT IN(SELECT mail 
                                     FROM company_promo_emails 
                                     WHERE company_promo_id = ?)";

        $promoId = (int)$aPromo['id'];
        $companyId = (int)$aPromo['company_id'];

        return $this->_db->fetchAll($sql, array($companyId, $promoId));
    }

    /**
     * Inserting new mails for promos after mail sending
     *
     * @param int $promoId
     * @param int $companyId
     * @return type
     */
    public function insertNewMails($promoId, $companyId) {
        $query = "INSERT INTO company_promo_emails(mail, company_promo_id)
                   SELECT DISTINCT r.mail, ?
                   FROM reviews r WHERE company_id = ?
                   AND r.mail != ''
                   AND r.mail IS NOT NULL
                   AND r.mail NOT IN(SELECT mail 
                                     FROM company_promo_emails 
                                     WHERE company_promo_id = ?)";

        $promoId = (int) $promoId;
        $companyId = (int) $companyId;

        $this->_db->query($query, array($promoId, $companyId, $promoId));
    }

}

$promo = new Promo();
$promo->exec();