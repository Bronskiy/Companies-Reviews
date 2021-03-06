<?php

/**
 * Companies_Model_CompanyPromoTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Companies_Model_CompanyPromoTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object Companies_Model_CompanyPromoTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Companies_Model_CompanyPromo');
    }

    /**
     * Get query for all company promos
     * @param $id
     * @return Doctrine_Query
     */
    public function getQueryToFetchCompanyPromos($id)
    {
        return $this->createQuery('p')
                    ->where('p.company_id = ?', array((int)$id))
                    ->orderBy('p.id DESC');
                    
    }

    /**
     * Find by company and id
     */
    public function findByCompanyAndId($id, $promoId)
    {
        $res = $this->createQuery('p')
            ->where('p.company_id = ? AND p.id = ?', array($id, $promoId))
            ->orderBy('p.id DESC')
            ->limit(1)
            ->execute();

        if (count($res) > 0)
            return $res[0];

        return null;
    }
}