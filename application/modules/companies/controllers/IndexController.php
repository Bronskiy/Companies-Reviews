<?php

/**
 * Index controller
 */
class Companies_IndexController extends Main_Controller_Action {
    /**
     * List of companies
     */
    
    public function companiesAction() {
        $page = $this->_request->getParam("page", 1);
        $this->view->states = $this->getService("company")->getTable()->getStates();
        $this->view->title = "Companies";
        $this->view->customTitle = "Companies";

        if ($page < 1) {
            $this->_redirectNotFoundPage();
        }

        if ($page == 1) {
            $this->view->uncategorizedCompanies = $this->getService("company")->getTable()->getUncategorizedCompanies();
        } else {
            $this->view->uncategorizedCompanies = array();
        }

        $this->view->paginator = $this->getService("company")->getCompanyAndCategoryPaginator($page);
        $this->_helper->layout->setLayout("extended");
    }

    /**
     * List of categories by letter
     */
    public function categoriesAction() {
        $letter = $this->_request->getParam("letter");
        $page = $this->_request->getParam("page", 1);

        if (!$letter || $page < 1) {
            $this->_redirectNotFoundPage();
        }

        $this->view->letter = $letter;
        $this->view->title = "Categories " . strtoupper($letter);
        $this->view->paginator = $this->getService("category")->getCategoryPaginatorByLetter($letter, $page);
    }

    /**
     * Companies in category
     */
    public function categoryAction() {
        $page = $this->_request->getParam("page", 1);
        $categoryUri = $this->_request->getParam("category");
        $catTable = $this->getService("category")->getTable();

        if (!$catTable->isCategoryExists($categoryUri) || $page < 1) {
            $this->_redirectNotFoundPage();
        }

        $category = $catTable->getByUri($categoryUri);

        $this->view->category = $category;
        $this->view->paginator = $this->getService("company")->getCompanyPaginatorByCategory($page, $category->id);
        $this->view->categoryName = $category->name;
        $this->view->title = $category->name;
        $this->view->description = "Customer reviews and videos of the " . $category->name .
            " companies based on reviews and videos for " . $category->name . " Companies.";
    }

    /**
     * Companies by letter
     */
    public function companiesLetterAction() {
        $letter = $this->_request->getParam("letter");
        $page = $this->_request->getParam("page", 1);

        if (!$letter || $page < 1) {
            $this->_redirectNotFoundPage();
        }

        $this->view->letter = $letter;
        $this->view->paginator = $this->getService("company")->getCompanyPaginatorByLetter($page, $letter);
        $this->view->title = "Companies " . strtoupper($letter);
    }

    /**
     * List of cities in state
     */
    public function stateAction()
    {
        $state = $this->_request->getParam('state');

        if (!$this->getService('company')->getTable()->isStateExists($state))
            $this->_redirectNotFoundPage();

        $this->view->cities = $this->getService('company')->getTable()->getCitiesInState($state);
        $this->view->state = $state;
        $this->view->title = $this->view->states()->getStateNameByKey(strtoupper($state));
    }

    /**
     * List of categories in current state and city
     */
    public function stateCityAction()
    {
        $state = $this->_request->getParam('state');
        $city = $this->_request->getParam('city');
        $table = $this->getService('company')->getTable();

        if (!$table->isStateExists($state) || !$table->isCityExists($city) || !$table->isCityInState($state, $city))
            $this->_redirectNotFoundPage();

        $this->view->state = $state;
        $this->view->city  = $city;

        $this->view->categories = $this->getService('category')->getTable()->getCategoriesWithCompaniesCount($state, $city);
        $this->view->uncategorized = $this->getService('company')->getTable()->getUncategorizedCompaniesCount($state, $city);

        $this->view->title = $table->getCityName($city) . " " . mb_strtoupper($state, 'UTF-8');;
    }

    /**
     * Top rated companies in city
     */
    public function topCityCatAction() {
        $page = $this->_request->getParam("page", 1);
        $categoryUri = $this->_request->getParam("category");
        $city = $this->_request->getParam("city");
        $state = $this->_request->getParam("state");
        $catTable = $this->getService("category")->getTable();
        $comTable = $this->getService("company")->getTable();

        if (!$comTable->isCityExists($city) ||
            !$comTable->isStateExists($state) ||
            !$comTable->isCityInState($state, $city) ||
            ($categoryUri != Companies_Model_Category::UNCATEGORIZED && !$catTable->isCategoryExists($categoryUri)) ||
            $page < 1
        ) {
            $this->_redirectNotFoundPage();
        }

        $catId = null;
        $category = null;

        if ($categoryUri != Companies_Model_Category::UNCATEGORIZED) {
            $category = $catTable->getByUri($categoryUri);
        }

        $categoryUri = $category ? $category->name : "Uncategorized";
        $this->view->paginator = $this->getService("company")->
            getCompaniesPaginatorByCityCategory($page, $city, $state, $category ? $category->id : null);

        $cityName = $comTable->getCityName($city);
        $stateName = $this->view->states()->getStateNameByKey(strtoupper($state));

        $this->view->state = $state;
        $this->view->city = $city;
        $this->view->category = $category;
        $this->view->cityName = $cityName;
        $this->view->categoryName = $categoryUri;
        $this->view->stateName = $stateName;

        if ($category) {
            $this->view->articles = $this->getService("company")->getLatestArticlesByCityCategory($city, $state, $category->id, 2);
        }

        $this->view->title = "Top Rated " . $categoryUri . " in " . $cityName . ", " . mb_strtoupper($state, "UTF-8");
        $this->view->description = "Customer reviews and videos of the best " . $categoryUri . " companies in " . $cityName . ", " . $stateName . " area based on reviews and videos for top rated " . $categoryUri . " companies.";
    }

    /**
     * Top rated companies in category
     */
    public function topCatAction() {
        $page = $this->_request->getParam("page", 1);
        $categoryUri = $this->_request->getParam("category");
        $catTable = $this->getService("category")->getTable();

        if (($categoryUri != Companies_Model_Category::UNCATEGORIZED && !$catTable->isCategoryExists($categoryUri)) ||
            $page < 1
        ) {
            $this->_redirectNotFoundPage();
        }

        $catId = null;
        $category = null;

        if ($categoryUri != Companies_Model_Category::UNCATEGORIZED) {
            $category = $catTable->getByUri($categoryUri);
        }

        $categoryUri = $category ? $category->name : "Uncategorized";
        $this->view->paginator = $this->getService("company")->
            getNationalCompaniesPaginatorByCategory($page, $category ? $category->id : null);

        $this->view->categoryName = $categoryUri;
        $this->view->category = $category;

        if ($category) {
            $this->view->articles = $this->getService("company")->getLatestArticlesByCategory($category->id, 2);
        }

        $this->view->title = "Top Rated " . $categoryUri . " Companies";
        $this->view->description = "Customer reviews and videos of the best " . $categoryUri . " companies based on reviews and videos for top rated " . $categoryUri . " Companies.";
    }

    /**
     * List companies from category state and city
     */
    public function stateCityCategoryAction()
    {
        $page        = $this->_request->getParam('page', 1);
        $categoryUri = $this->_request->getParam('category');
        $state       = $this->_request->getParam('state');
        $city        = $this->_request->getParam('city');
        $table       = $this->getService('company')->getTable();
        $catTable    = $this->getService('category')->getTable();

        if (
            !$table->isStateExists($state) ||
            !$table->isCityExists($city) ||
            !$table->isCityInState($state, $city) ||
            ($categoryUri != Companies_Model_Category::UNCATEGORIZED && !$catTable->isCategoryExists($categoryUri)) ||
            $page < 1
        ) {
            $this->_redirectNotFoundPage();
        }

        $res = $this->getService('company')->getCompaniesPaginatorByCategory($categoryUri, $page, $state, $city);

        if (!$res) $this->_redirectNotFoundPage();

        $categoryName = 'Uncategorized';

        if ($categoryUri != Companies_Model_Category::UNCATEGORIZED) {
            $categoryName = $this->getService('company')->getTable('category')->getByUri($categoryUri)->name;
        }

        $cityName = $table->getCityName($city);

        $this->view->state = $state;
        $this->view->city  = $city;
        $this->view->paginator = $res;
        $this->view->categoryName = $categoryName;
        $this->view->categoryUri = $categoryUri;
        $stateName = $this->view->states()->getStateNameByKey(strtoupper($state));

        $cnt = count($this->view->paginator);

        if ($cnt !== 0 && $cnt < $page)
            $this->_redirectNotFoundPage();

        $this->view->title = $categoryName . ' Business Reviews ' . $cityName . ' ' . mb_strtoupper($state, 'UTF-8');
        $this->view->description = "$categoryName Company Reviews in $cityName, $stateName. Revudio offers reviews and videos " .
            "of businesses in real time.";
    }

    /**
     * Company page
     */
    private function _showCompany() {
        $this->checkCompanyByUri();
        $company = $this->getService("company")->getCompanyByUri();

        // full company info
        $companyCollection = $this->getService("company")->getTable("company")->findCompany($company->id);
        $company = $companyCollection->get(0);

        $dirsGenerator = new Main_Service_Dir_Generator_Company($company);

        $this->view->companyDirsAbs = $dirsGenerator->getFoldersPathsFromRule();
        $this->view->companyDirsRel = $dirsGenerator->getFoldersPathsFromRule(false);
        $this->view->company = $company;
        $this->view->ratings = Main_Service_Company_Rating_Loader::getAllRatings($company->rating);

        $this->view->reviewStats = $this->getService("review")
            ->getTable()
            ->getCompanyReviewRatingStats($company->id)
            ->toArray();

        $page = $this->_request->getParam("page", 0);

        if ($page) {
            $this->_helper->viewRenderer("company-inner");
        } else {
            $this->_helper->layout->setLayout("extended");
            $this->_helper->viewRenderer("company");
        }

        if ($page == 1) {
            $this->redirect(
                $this->view->urlGenerator()->companyUrl($company),
                array( "exit" => true )
            );

            return;
        }

        $this->view->company = $company;
        $this->view->paginator = $this->getService("review")->getCompanyReviewsPaginator(
            $company,
            Companies_Model_Review::STATUS_PUBLISHED,
            null,
            50
        );

        $cnt = $this->view->paginator->getTotalItemCount();

        if (($cnt == 0 && $page > 1) || $page > $this->view->paginator->count()) {
            $this->_redirectNotFoundPage();
        }

        $this->view->paginatorParams = $this->_request->getParams();

        if ($company->local_business) {
            $this->view->customTitle = $company->name;
            $this->view->title = $company->name . " " . $company->city . " " . $company->state;
        } else {
            $this->view->customTitle = $company->name;
            $this->view->title = $company->name;
        }

        $this->view->description = "$cnt Reviews For " . $company->name . ".";

        $latestReviews = $this->getService("review")
            ->getTable("review")
            ->getCompanyLastReviews($company->id, 1);

        if ($latestReviews && count($latestReviews) == 1) {
            $review = $latestReviews[0];

            if ($review && $review->review) {
                $text = str_replace("\"", "", $review->review);
                $this->view->description .= " &#34;" . $text . "&#34;.";
            }
        }

        $reviewTable = $this->getService("review")->getTable();
        $this->view->textReviewCount = $reviewTable->getTextReviewCount($company->id);
        $this->view->videoReviewCount = $reviewTable->getVideoReviewCount($company->id);
        $this->view->employees = $this->getService("employee")->getAll($company->id);
        $this->view->articles = $this->getService("company-article")->getTable()->getLatest($company->id, 2);
    }

    /**
     * Verify listing
     */
    public function verifyAction() {
        $this->checkCompanyByUri();
        $company = $this->getService("company")->getCompanyByUri();

        if ($company->status != Companies_Model_Company::STATUS_UNOWNED) {
            $this->_redirectNotFoundPage();
        }

        if ($this->_request->isPost()) {
            try {
                $this->getService("company")->verify($company);
                $this->redirect($this->url("signup"), array("exit" => true));
            } catch (Exception $e) {
                // pass
            }
        }

        $this->view->company = $company;
        $this->view->title = $company->name . " Listing Verification";
        $this->view->form = $this->getService("company")->getForm("verify-listing");
    }

    /**
     * Add review
     */
    public function addReviewAction() {
        $this->checkCompanyByUri();
        $company = $this->getService('company')->getCompanyByUri();

        if ($this->_request->isPost()) {
            $review = $this->getService('review')->leaveReview($company);

            if ($review)  {
                $this->redirect(
                    $this->view->urlGenerator()->companyReviewConfirmUrl($review->Company, $review),
                    array('exit' => true)
                );
            }
        }

        $this->view->company = $company;
        $this->view->title = 'Write A Review For ' . $company->name;

        if ($company->local_business) {
            $this->view->title .= ' ' . $company->city . ' ' . $company->state;
        }

        $this->view->reviewForm = new Companies_Form_LeaveReview($company->id);
        $this->view->employees = $this->getService("employee")->getAll($company->id);
    }

    /**
     * Confirm review
     */
    public function confirmReviewAction() {
        $hash = $this->_request->getParam("hash", "");
        $service = $this->getService("review");
        $table = $service->getTable("review");

        $reviews = $table->findReviewByApproveHash($hash);

        if (!$reviews->count()) {
            $this->_redirectNotFoundPage();
        }

        $review = $reviews->get(0);

        if ($this->_request->isPost() && $this->getService("review")->confirmReview($review)) {
            $this->redirect($this->view->urlGenerator()->companyUrl($review->Company), array( "exit" => true ));
        }

        $this->view->title = "Confirm review";
        $this->view->hash = $hash;
        $this->view->review = $review;
        $this->view->form = Main_Service_Models::getStaticForm("confirm-review");
    }

    /**
     * Review changing page
     */
    public function changeReviewAction() {
        $hash = $this->_request->getParam('hash', '');
        $service = $this->getService('review');
        $table = $service->getTable('review');

        $reviews = $table->findReviewByReconcileHash($hash);

        if (!$reviews->count()) {
            $this->_redirectNotFoundPage();
        }

        $review = $reviews->get(0);

        if ($this->_request->isPost() && $this->getService('review')->changeReview($review)) {
            $this->redirect($this->view->urlGenerator()->companyUrl($review->Company), array( 'exit' => true ));
        }

        $this->view->title = 'Change review';
        $this->view->review = $review;
        $this->view->reviewForm = Main_Service_Models::getStaticForm('change-review');

        if ($this->_request->isPost()) {
            $this->view->reviewForm->populate($this->_request->getPost());
        } else {
            $this->view->reviewForm->populate($review->toArray());
            $floatToStar = new Main_Service_Filter_FloatToStar();
            $this->view->reviewForm->rating->setValue($floatToStar->filter($review->rating));
        }
    }

    /**
     * Action with search form
     */
    public function searchAction() {
        $searchContainer = new Main_Session_Search_Companies();
        $searchContainer->clearSearchData();

        if ($this->_request->isPost() && $this->getService('company')->search($searchContainer)) {
            $this->redirect($this->url('search_results'), array( 'exit' => true ));
            return;
        }

        $this->view->title = 'Search';
        $this->view->searchForm = Main_Service_Models::getStaticForm('search');
    }

    /**
     * Action for sending message to the company owner
     */
    public function contactAction() {
        $this->checkCompanyByUri();

        $company = $this->getService('company')->getCompanyByUri();

        if ($company->status == Companies_Model_Company::STATUS_UNOWNED) {
            $this->_redirectNotFoundPage();
        }

        if ($this->_request->isPost()) {
            $this->getService('company')->contact($company);
        }

        $this->view->title = 'Contact Business';
        $this->view->company = $company;
        $this->view->contactForm = $this->view->regForm = $this->getService('company')->getForm('contact');
    }

    /**
     * Search results action
     *
     */
    public function searchResultAction() {
        if ($this->_request->getParam('page') == 1) {
            $this->redirect($this->url('search_results'), array( 'exit' => true ));
            return;
        }

        $searchContainer = new Main_Session_Search_Companies();
        $query = $searchContainer->getSearchData();
        $paginator = null;

        if ($query) {
            $paginator = $this->getService('company')->getSearchPaginator($query);
        }

        $this->view->title = 'Search';
        $this->view->query = $query;
        $this->view->paginator = $paginator;
    }

    /**
     * Add review video
     */
    public function addReviewVideoAction() {
        $hash = $this->_request->getParam('hash', '');
        $service = $this->getService('review');
        $table = $service->getTable('review');

        $reviews = $table->findReviewByVideoHash($hash);

        if (!$reviews->count()) {
            $this->_redirectNotFoundPage();
        }

        $review = $reviews->get(0);

        if ($this->_request->isPost() && $this->getService('review')->uploadVideo($review)) {
            $this->redirect($this->view->urlGenerator()->companyUrl($review->Company), array('exit' => true));
        }

        $this->view->title = 'Add Video';
        $this->view->company = $review->Company;
        $this->view->review = $review;
        $this->view->hash = $hash;
        $this->view->videoForm = Main_Service_Models::getStaticForm('add-video');
    }

    /**
     * Page showing notification about cancelled account
     */
    public function accountCancelledAction() {
        $session = new Zend_Session_Namespace();

        if (!isset($session->accountCancelled)) {
            $this->_redirectNotFoundPage();
        }

        $this->view->title = 'Account Cancelled';

        unset($session->accountCanceled);
    }

    /**
     * Employee page
     */
    private function _showEmployee() {
        $name = $this->_request->getParam('name', 0);
        $id = 0;
        $matches = array();

        if (preg_match('/.*?\-(\d+)$/', $name, $matches)) {
            $id = (int) $matches[1];
        }

        $employee = $this->getService('employee')->getTable()->findOneById($id);

        if (!$employee || !$employee->public_profile) {
            $this->_redirectNotFoundPage();
        }

        $this->view->employee = $employee;
        $page = $this->_request->getParam('page', 0);

        if ($page == 1) {
            $this->redirect($this->view->urlGenerator()->employeeUrl($employee), array('exit' => true));
            return;
        }

        $this->view->paginator = $this->getService('review')->getEmployeeReviewsPaginator($employee, 50);
        $cnt = $this->view->paginator->getTotalItemCount();

        if (($cnt == 0 && $page > 1) || $page > $this->view->paginator->count()) {
            $this->_redirectNotFoundPage();
        }

        $this->view->paginatorParams = $this->_request->getParams();
        $this->view->title = $employee->name;
        $this->view->description = "$cnt Reviews For " . $employee->name . ", " . $employee->Company->name . ".";
        $this->view->employees = $this->getService("employee")->getAll();
        $this->_helper->viewRenderer("employee");
    }

    /**
     * Base action
     */
    public function baseAction() {
        try {
            $this->_showCompany();
            return;
        } catch (Zend_Controller_Action_Exception $e) {
            if ($e->getCode() != 404) {
                throw $e;
            }
        }

        $this->_showEmployee();
    }

    /**
     * Company action
     */
    public function companyAction() {
        $this->_showCompany();
    }

    /**
     * Company articles
     */
    public function articlesAction() {
        $this->checkCompanyByUri();
        $company = $this->getService("company")->getCompanyByUri();
        $page = $this->_request->getParam("page", 1);

        $this->view->title = "Articles";
        $this->view->company = $company;
        $this->view->paginator = $this->getService("company-article")->getPaginator($company->id, $page);
        $this->view->paginatorParams = $this->_request->getParams();
    }

    /**
     * View article
     */
    public function articleAction() {
        $this->checkCompanyByUri();
        $company = $this->getService("company")->getCompanyByUri();
        $id = $this->_request->getParam("id", 0);
        $article = $this->getService("company-article")->getTable()->findOneByIdAndCompanyId($id, $company->id);

        if (!$article) {
            $this->_redirectNotFoundPage();
        }

        if ($this->_request->isPost()) {
            $this->getService("company-article")->comment($article);
        }

        $this->view->title = $article->title;
        $this->view->article = $article;
        $this->view->company = $company;
        $this->view->comments = $this->getService("company-article")->getComments($id);
        $this->view->form = $this->getService("company")->getForm("company-article-comment");
    }
}
