<?php
/**
 *
 */
class ZFEngine_Module_UserBase_Model_MailerTable extends Doctrine_Table
{
    /**
     * Выборка писем, сначала - самые старые
     *
     * @return Doctrine_Collection
     */
    public function fetchOldMails($limit = 10)
    {
        $query = $this->createQuery()
                    ->orderBy('created_at DESC')
                    ->limit($limit);

        return $query->execute();
    }
}