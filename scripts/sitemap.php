<?php

require_once "common.php";

/**
 * Sitemap generator task
 */
class SitemapGenerator extends Task {
    const LOCK_FILE_NAME = "sitemap.lock";
    const SITEMAP_START = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
    const SITEMAP_END = "</urlset>";

    private $_fh = null;
    private $_fileNumber = 0;
    private $_urlNumber = 0;

    /**
     * Get path of sitemaps
     */
    private function _getSitemapsPath() {
        $path = APPLICATION_PATH . "/../public/sitemaps";

        if (!file_exists($path)) {
            @mkdir($path);
        }

        return $path;
    }

    /**
     * Get sitemap path
     * @param $number
     */
    private function _getSitemapPath($number) {
        $path = "sitemap.xml";

        if ($number > 0) {
            $path = "sitemap$number.xml";
        }

        return $this->_getSitemapsPath() . "/" . $path;
    }

    /**
     * Remove old sitemaps
     */
    private function _removeSitemaps() {
        $path = $this->_getSitemapsPath();

        foreach (scandir($path) as $file) {
            if ($file == "." || $file == "..") {
                continue;
            }

            @unlink($path . "/" . $file);
        }
    }

    /**
     * Open sitemap file
     */
    private function _openFile() {
        $this->_fh = fopen($this->_getSitemapPath($this->_fileNumber), "wt");
        @fwrite($this->_fh, self::SITEMAP_START . "\n");
    }

    /**
     * Close sitemap file
     */
    private function _closeFile() {
        @fwrite($this->_fh, self::SITEMAP_END . "\n");
        @fclose($this->_fh);
    }

    /**
     * Add url
     * @param $url
     */
    private function _addUrl($url) {
        if (!$this->_fh) {
            $this->_openFile();
        }

        if (!$url) {
            return;
        }

        $url = "http://" . Main_Service_ConfigsLoader::getConfig()->domain . $url;

        @fwrite($this->_fh, "<url><loc>$url</loc></url>\n");
        @fflush($this->_fh);
        $this->_urlNumber++;

        if ($this->_urlNumber % 50000 == 0) {
            $this->_closeFile();
            $this->_fileNumber++;
            $this->_openFile();
        }
    }

    /**
     * Add static urls
     */
    private function _addStaticUrls() {
        echo "Static...\n";
        
        $urls = array(
            "static_privacy",
            "static_terms",
            "static_contactus",
            "static_how_it_works",
            "static_pricing",
            "signup",
            "login",
            "restore",
        );

        foreach ($urls as $url) {
            $this->_addUrl($this->url($url));
        }
    }

    /**
     * Add category URLs
     */
    private function _addCategoryUrls() {
        echo "Categories...\n";

        $companyService = new Companies_Model_CompanyService();
        $categoryService = new Companies_Model_CategoryService();

        $this->_addUrl($this->url("companies"));

        for ($i = 2; $i <= $categoryService->getCategoryPageCount(); $i++) {
            $this->_addUrl($this->url("companies_page", array("page" => $i)));
        }

        // categories
        foreach ($categoryService->getAll() as $category) {
            $this->_addUrl($this->url("category", array("category" => $category->uri)));

            for ($i = 2; $i <= $companyService->getCompanyPageCountByCategory($category->id); $i++) {
                $this->_addUrl($this->url("category_page", array("category" => $category->uri, "page" => $i)));
            }
        }

        // categories by letter
        foreach (str_split(strtolower(Main_Const::ALPHABET)) as $letter) {
            $this->_addUrl($this->url("categories", array("letter" => $letter)));

            for ($i = 2; $i <= $categoryService->getCategoryPageCountByLetter($letter); $i++) {
                $this->_addUrl($this->url("categories_page", array("letter" => $letter, "page" => $i)));
            }
        }

        // companies by letter
        foreach (str_split(strtolower(Main_Const::ALPHABET)) as $letter) {
            $this->_addUrl($this->url("companies_letter", array("letter" => $letter)));

            for ($i = 2; $i <= $companyService->getCompanyPageCountByLetter($letter); $i++) {
                $this->_addUrl($this->url("companies_letter_page", array("letter" => $letter, "page" => $i)));
            }
        }

        // states
        foreach ($companyService->getTable()->getStates() as $state) {
            $state = strtolower(array_shift($state));
            $this->_addUrl($this->url("state", array("state" => $state)));

            foreach ($companyService->getTable()->getCitiesInState($state) as $city) {
                $city = array_shift($city);
                $city = $this->getView()->stringToUri($city);

                $this->_addUrl($this->url("state_city", array(
                    "state" => $state,
                    "city" => $city,
                )));

                foreach ($categoryService->getTable()->getCategoriesWithCompaniesCount($state, $city) as $category) {
                    $this->_addUrl($this->url("state_city_category", array(
                        "state" => $state,
                        "city" => $city,
                        "category" => $category->uri
                    )));

                    for ($i = 2; $i <= $companyService->getCompanyPageCountByCategoryStateCity($category->id, $state, $city); $i++) {
                        $this->_addUrl($this->url("state_city_category_page", array(
                            "state" => $state,
                            "city" => $city,
                            "category" => $category->uri,
                            "page" => $i,
                        )));
                    }
                }
            }
        }
    }

    /**
     * Add top rated URLs
     */
    private function _addTopRatedUrls() {
        echo "Top Rated...\n";

        $companyService = new Companies_Model_CompanyService();
        $categoryService = new Companies_Model_CategoryService();

        // top city category
        foreach ($companyService->getTable()->getStates() as $state) {
            $state = strtolower(array_shift($state));

            foreach ($companyService->getTable()->getCitiesInState($state) as $city) {
                $city = array_shift($city);
                $city = $this->getView()->stringToUri($city);

                $this->_addUrl($this->url("top_city_category", array(
                    "state" => $state,
                    "city" => $city,
                    "category" => Companies_Model_Category::UNCATEGORIZED,
                )));

                foreach ($categoryService->getTable()->getCategoriesWithCompaniesCount($state, $city) as $category) {
                    $this->_addUrl($this->url("top_city_category", array(
                        "state" => $state,
                        "city" => $city,
                        "category" => $category->uri,
                    )));

                    for ($i = 2; $i <= $companyService->getCompanyPageCountByCategoryStateCity($category->id, $state, $city); $i++) {
                        $this->_addUrl($this->url("top_city_category_page", array(
                            "state" => $state,
                            "city" => $city,
                            "category" => $category->uri,
                            "page" => $i,
                        )));
                    }
                }
            }
        }

        // top categories
        $this->_addUrl($this->url("top_category", array("category" => Companies_Model_Category::UNCATEGORIZED)));

        foreach ($categoryService->getTable()->getNationalCompanyCategories() as $category) {
            $this->_addUrl($this->url("top_category", array("category" => $category->uri)));

            for ($i = 2; $i <= $companyService->getNationalCompanyPageCountByCategory($category->id); $i++) {
                $this->_addUrl($this->url("top_category_page", array("category" => $category->uri, "page" => $i)));
            }
        }
    }

    /**
     * Add national company URLs
     */
    private function _addNationalCompanyUrls() {
        echo "National Companies...\n";

        $companyService = new Companies_Model_CompanyService();
        $reviewService = new Companies_Model_ReviewService();
        $articleService = new Companies_Model_CompanyArticleService();
        $generator = $this->getView()->urlGenerator();

        foreach ($companyService->getTable()->getNationalCompanies() as $company) {
            $this->_addUrl($this->url("base_action", array("name" => $this->getView()->stringToUri($company->name))));

            for ($i = 2; $i <= $reviewService->getReviewPageCountForCompany($company->id); $i++) {
                $this->_addUrl($this->url("base_action_page", array(
                    "name" => $this->getView()->stringToUri($company->name),
                    "page" => $i,
                )));
            }

            $this->_addUrl($generator->companyReviewAddUrl($company));

            if ($company->status == Companies_Model_Company::STATUS_UNOWNED) {
                $this->_addUrl($generator->companyListingVerificationUrl($company));
            } else {
                $this->_addUrl($generator->companyContactUrl($company));
            }

            $this->_addUrl($generator->companyArticlesUrl($company));

            for ($i = 2; $i <= $articleService->getArticlePageCountForCompany($company->id); $i++) {
                $this->_addUrl($this->url("company_national_articles_page", array(
                    "name" => $this->getView()->stringToUri($company->name),
                    "page" => $i,
                )));
            }

            foreach ($articleService->getTable()->getCompanyArticles($company->id) as $article) {
                $this->_addUrl($generator->companyArticleUrl($company, $article));
            }
        }
    }

    /**
     * Add company URLs
     */
    private function _addCompanyUrls() {
        echo "Companies...\n";

        $companyService = new Companies_Model_CompanyService();
        $reviewService = new Companies_Model_ReviewService();
        $articleService = new Companies_Model_CompanyArticleService();
        $generator = $this->getView()->urlGenerator();

        foreach ($companyService->getTable()->getLocalCompanies() as $company) {
            $this->_addUrl($this->url("company", array(
                "name" => $this->getView()->stringToUri($company->name),
                "city" => $this->getView()->stringToUri($company->city),
                "state" => $this->getView()->stringToUri($company->state)
            )));

            for ($i = 2; $i <= $reviewService->getReviewPageCountForCompany($company->id); $i++) {
                $this->_addUrl($this->url("company_page", array(
                    "name" => $this->getView()->stringToUri($company->name),
                    "city" => $this->getView()->stringToUri($company->city),
                    "state" => $this->getView()->stringToUri($company->state),
                    "page" => $i,
                )));
            }

            $this->_addUrl($generator->companyReviewAddUrl($company));

            if ($company->status == Companies_Model_Company::STATUS_UNOWNED) {
                $this->_addUrl($generator->companyListingVerificationUrl($company));
            } else {
                $this->_addUrl($generator->companyContactUrl($company));
            }

            $this->_addUrl($generator->companyArticlesUrl($company));

            for ($i = 2; $i <= $articleService->getArticlePageCountForCompany($company->id); $i++) {
                $this->_addUrl($this->url("company_articles_page", array(
                    "name" => $this->getView()->stringToUri($company->name),
                    "city" => $this->getView()->stringToUri($company->city),
                    "state" => $this->getView()->stringToUri($company->state),
                    "page" => $i,
                )));
            }

            foreach ($articleService->getTable()->getCompanyArticles($company->id) as $article) {
                $this->_addUrl($generator->companyArticleUrl($company, $article));
            }
        }
    }

    /**
     * Add national company URLs
     */
    private function _addEmployeeUrls() {
        echo "Employees...\n";

        $employeeService = new Companies_Model_EmployeeService();
        $reviewService = new Companies_Model_ReviewService();

        foreach ($employeeService->getTable()->getPublicEmployees() as $employee) {
            $this->_addUrl($this->url("base_action", array(
                "name" => $this->getView()->stringToUri($employee->name) . "-" . $employee->id
            )));

            for ($i = 2; $i <= $reviewService->getReviewPageCountForEmployee($employee->id); $i++) {
                $this->_addUrl($this->url("base_action_page", array(
                    "name" => $this->getView()->stringToUri($employee->name) . "-" . $employee->id,
                    "page" => $i,
                )));
            }
        }
    }

    private function _ping($file) {
        $curl = curl_init();
        $url = sprintf("http://%s/sitemaps/%s", Main_Service_ConfigsLoader::getConfig()->domain, $file);
        $googleUrl = "https://www.google.com/webmasters/tools/ping?sitemap=" . urlencode($url);

        echo "Ping $url...\n";

        $options = array(
            CURLOPT_URL => $googleUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        );

        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);

        if ($result === false) {
            throw new Exception("Error connecting to server: " . curl_error($curl));
        }

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($code != 200) {
            throw new Exception("Ping error: " . $code);
        }
    }

    /**
     * Ping sitemaps
     */
    private function _pingSitemaps() {
        $path = $this->_getSitemapsPath();

        foreach (scandir($path) as $file) {
            if ($file == "." || $file == "..") {
                continue;
            }

            $this->_ping($file);
        }
    }

    /**
     * Execute command
     */
    public function exec() {
        try {
            parent::exec();
            $this->_removeSitemaps();

            // urls
            $this->_addUrl("/");
            $this->_addStaticUrls();
            $this->_addCategoryUrls();
            $this->_addTopRatedUrls();
            $this->_addNationalCompanyUrls();
            $this->_addCompanyUrls();
            $this->_addEmployeeUrls();

            $this->_closeFile();
            $this->_pingSitemaps();
        } catch (Exception $e) {
            echo $e->getMessage();
        }        
    }

    /**
     * Return full locking file name path
     */
    protected function _getLockedFileName() {
        return realpath(APPLICATION_PATH . '/../tmp') . '/' . self::LOCK_FILE_NAME;
    }
}

$task = new SitemapGenerator();
$task->exec();