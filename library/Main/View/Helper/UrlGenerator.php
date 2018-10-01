<?php 
/**
 * Url Generate helper
 *  
 */
class Zend_View_Helper_UrlGenerator extends Zend_View_Helper_Abstract
{
    protected $_filters = array();    
    
    public function urlGenerator()
    {
        return $this;
    }
    
    /**
     * Url for company
     * 
     * @param Companies_Model_Company $company
     * @return string 
     */
    public function companyUrl(Companies_Model_Company $company)
    {
        if ($company->local_business) {
            return $this->_getCompanyDependentUrl('company', $company);
        }

        return $this->_getCompanyDependentUrl('base_action', $company);
    }

    /**
     * Url for employee
     * @param Companies_Model_Employee $employee
     * @return string
     */
    public function employeeUrl(Companies_Model_Employee $employee) {
        return $this->view->url("base_action", array(
            "name" => $this->_filterStringToUrl($employee->name) . "-" . $employee->id
        ));
    }
    
    /**
     * Url for company reviews with range
     * 
     * @param Companies_Model_Company $company
     * @param array $range 
     */
    public function companyReviewsRangeUrl(Companies_Model_Company $company, array $range)
    {
        $name = $this->_filterStringToUrl($company->name);
        $city = $this->_filterStringToUrl($company->city);
        $state = $this->_filterStringToUrl($company->state);

        if ($company->local_business) {
            return $this->view->url('company_reviews_range', array(
                'city' => $city,
                'name' => $name,
                'state' => $state,
                'range_min' =>  $range['range'][0],
                'range_max' =>  $range['range'][1]
            ));
        }

        return $this->view->url('company_national_reviews_range', array(
            'name' => $name,
            'range_min' =>  $range['range'][0],
            'range_max' =>  $range['range'][1]
        ));
    }
    
    /**
     * Top rated companies (in city and category) url
     * @param $company
     */
    public function topRatedUrl($company)
    {
        if (!$company)
            return Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();

        $city = $this->_filterStringToUrl($company->city);
        $state = $this->_filterStringToUrl($company->state);

        $category = $company->category_id ? $this->_filterStringToUrl($company->Category->name) : Companies_Model_Category::UNCATEGORIZED;

        if ($company->local_business) {
            $url =  $this->view->url('top_city_category', array('city' => $city, 'state' => $state, 'category' => $category));
        } else {
            $url =  $this->view->url('top_category', array('category' => $category));
        }

        return $url;
    }
    
    /**
     * 
     * @param Companies_Model_Company $company
     * @return type 
     */
    public function companyCategoryUrl(Companies_Model_Company $company)
    {
        $state = $this->_filterStringToUrl($company->state);
        $city = $this->_filterStringToUrl($company->city);
        $category = $company->Category->exists() 
                    ? $company->Category->id : Companies_Model_Category::UNCATEGORIZED;
        
        return $this->view->url(
                'state_city_category', 
                array('state' => $state, 'city' => $city, 'category' => $category));
    }
    
    /**
     * Url for company contact page
     * 
     * @param Companies_Model_Company $company 
     * @return string 
     */
    public function companyContactUrl(Companies_Model_Company $company)
    {
        if ($company->local_business) {
            return $this->_getCompanyDependentUrl('company_contact', $company);
        }

        return $this->_getCompanyDependentUrl('company_national_contact', $company);
    }
    
    /**
     * Url for adding review for company
     * 
     * @param Companies_Model_Company $company
     * @return string 
     */
    public function companyReviewAddUrl(Companies_Model_Company $company)
    {
        if ($company->local_business) {
            return $this->_getCompanyDependentUrl('company_review_add', $company);
        }

        return $this->_getCompanyDependentUrl('company_national_review_add', $company);
    }

    /**
     * Url for company listing verification
     * @param Companies_Model_Company $company
     * @return string
     */
    public function companyListingVerificationUrl(Companies_Model_Company $company) {
        if ($company->local_business) {
            return $this->_getCompanyDependentUrl("company_verify", $company);
        }

        return $this->_getCompanyDependentUrl("company_national_verify", $company);
    }

    /**
     * Url for confirming review for company
     * @param Companies_Model_Company $company
     * @param $review
     * @return string
     */
    public function companyReviewConfirmUrl(Companies_Model_Company $company, $review)
    {
        if ($company->local_business) {
            return $this->_getCompanyDependentUrl(
                'company_review_confirm',
                $company,
                array('hash' => $review->confirm_hash)
            );
        }

        return $this->_getCompanyDependentUrl(
            'company_national_review_confirm',
            $company,
            array('hash' => $review->confirm_hash)
        );
    }

    /**
     * Url for adding video to review for company
     * @param Companies_Model_Company $company
     * @param $review
     * @return string
     */
    public function companyReviewAddVideoUrl(Companies_Model_Company $company, $review)
    {
        if ($company->local_business) {
            return $this->_getCompanyDependentUrl(
                'company_review_add_video',
                $company,
                array('hash' => $review->video_attach_hash)
            );
        }

        return $this->_getCompanyDependentUrl(
            'company_national_review_add_video',
            $company,
            array('hash' => $review->video_attach_hash)
        );
    }

    /**
     * Url for changing review for company
     * @param Companies_Model_Company $company
     * @param $review
     * @return string
     */
    public function companyReviewChangeUrl(Companies_Model_Company $company, $review)
    {
        if ($company->local_business) {
            return $this->_getCompanyDependentUrl(
                'company_review_change',
                $company,
                array('hash' => $review->reconcile_hash)
            );
        }

        return $this->_getCompanyDependentUrl(
            'company_national_review_change',
            $company,
            array('hash' => $review->reconcile_hash)
        );
    }
    
    /**
     * Construct company dependent url
     * @param string $routeName
     * @param Companies_Model_Company $company
     * @param array $options
     * @return string 
     */
    protected function _getCompanyDependentUrl($routeName, Companies_Model_Company $company, $options=array()) {
        $name = $this->_filterStringToUrl($company->name);
        $city = $this->_filterStringToUrl($company->city);
        $state = $this->_filterStringToUrl($company->state);

        $options = array_merge(array("city" => $city, "name" => $name, "state" => $state), $options);

        return $this->view->url($routeName, $options);
    }

    /**
     * Filtering string for correct representation in url
     * 
     * @param string $string
     * @return string
     */
    protected function _filterStringToUrl($value)
    {
        return $this->_getFilter('Main_Service_Filter_StringToUri')->filter($value);
    }
    
    /**
     * Filters lazy loading
     * 
     * @param string $name
     * @param mixed $params
     * @param bool $replace 
     * 
     * @return Zend_Filter_Interface
     */
    protected function _getFilter($name, $params = null, $replace = false)
    {
        if(!array_key_exists($name, $this->_filters) || $replace === true) {
            $this->_filters[$name] = new $name($params);
        }
        return $this->_filters[$name];
    }

    /**
     * Articles URL
     * @param Companies_Model_Company $company
     * @return string
     */
    public function companyArticlesUrl(Companies_Model_Company $company) {
        if ($company->local_business) {
            return $this->_getCompanyDependentUrl("company_articles", $company);
        }

        return $this->_getCompanyDependentUrl("company_national_articles", $company);
    }

    /**
     * Article URL
     * @param Companies_Model_Company $company
     * @param Companies_Model_CompanyArticle $article
     * @return string
     */
    public function companyArticleUrl(Companies_Model_Company $company, Companies_Model_CompanyArticle $article) {
        if ($company->local_business) {
            return $this->_getCompanyDependentUrl("company_article", $company, array("id" => $article->id));
        }

        return $this->_getCompanyDependentUrl("company_national_article", $company, array("id" => $article->id));
    }
}