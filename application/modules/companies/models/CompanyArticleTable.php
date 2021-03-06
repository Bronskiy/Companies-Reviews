<?php

/**
 * Companies_Model_CompanyArticleTable
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Companies_Model_CompanyArticleTable extends Doctrine_Table {
    /**
     * Returns an instance of this class.
     * @return object Companies_Model_CompanyArticleTable
     */
    public static function getInstance() {
        return Doctrine_Core::getTable('Companies_Model_CompanyArticle');
    }

    /**
     * Get query for company articles
     * @param null $companyId
     * @return Doctrine_Query
     */
    public function getQueryToFetchAll($companyId = null) {
        $query = $this->createQuery("a")->select("a.*");

        if ($companyId) {
            $query->where("a.company_id = ?", (int) $companyId);
        }

        $query->orderBy("a.created_at DESC");

        return $query;
    }

    /**
     * Get latest articles
     * @param int $companyId
     * @param int $count
     * @return Doctrine_Collection
     */
    public function getLatest($companyId=null, $count=3) {
        $query = $this->createQuery("a");

        if ($companyId) {
            $query
                ->leftJoin("a.Company c")
                ->where("a.company_id = ?", (int) $companyId);
        }

        return $query
            ->orderBy("a.created_at DESC")
            ->limit($count)
            ->execute();
    }

    /**
     * Get latest articles by city and category
     * @param string $city
     * @param string $state
     * @param integer $catId
     * @return Doctrine_Collection
     */
    public function getLatestByCityCategory($city, $state, $catId = null, $count = 3) {
        $city = str_replace("-", "_", $city);

        $q = $this->createQuery("a")
            ->select("a.*")
            ->addSelect("c.*")
            ->innerJoin("a.Company c");

        if ($catId === null) {
            $q->where("ISNULL(c.category_id)");
        } else {
            $q->where("c.category_id = ?", $catId);
        }

        $q->andWhereIn("c.status", Companies_Model_Company::getActiveStatuses())
            ->addWhere("c.local_business = 1")
            ->addWhere("c.city LIKE ? AND c.state = ?", array($city, mb_strtoupper($state, "UTF-8")))
            ->orderBy("a.created_at DESC")
            ->limit($count);

        return $q->execute();
    }

    /**
     * Get latest articles by category
     * @param integer $catId
     * @return Doctrine_Collection
     */
    public function getLatestByCategory($catId = null, $count = 3) {
        $q = $this->createQuery("a")
            ->select("a.*")
            ->addSelect("c.*")
            ->innerJoin("a.Company c");

        if ($catId === null) {
            $q->where("ISNULL(c.category_id)");
        } else {
            $q->where("c.category_id = ?", $catId);
        }

        $q->andWhereIn("c.status", Companies_Model_Company::getActiveStatuses())
            ->addWhere("c.local_business != 1")
            ->orderBy("a.created_at DESC")
            ->limit($count);

        return $q->execute();
    }

    /**
     * Get article count
     * @param int $companyId
     * @return int
     */
    public function getArticleCount($companyId) {
        $res = $this->createQuery("a")
            ->select("COUNT(a.id) AS count")
            ->where("a.company_id = ?", (int) $companyId)
            ->execute();

        if ($res) {
            $res = (int) $res[0]->count;
        } else {
            $res = 0;
        }

        return $res;
    }

    /**
     * Get articles
     * @param int $companyId
     * @return Doctrine_Collection
     */
    public function getCompanyArticles($companyId) {
        return $this->createQuery("a")
            ->select("a.*")
            ->where("a.company_id = ?", (int) $companyId)
            ->execute();
    }
}