<?php

/**
 * Admin controller
 */
class Companies_AdminController extends Main_Controller_Action {
    /**
     * Admin account main page
     */
    public function indexAction() {
        $this->redirect($this->url('admin_reviews'), array('exit' => true));
    }

    /**
     * Reviews listing
     */
    public function reviewsAction() {
        if ($this->_request->getParam('page') == 1) {
            $this->redirect($this->url('admin_reviews'), array('exit' => true));
            return;
        }

        $searchContainer = new Main_Session_Search_Review();
        $searchContainer->clearSearchData();
        $reviewService = $this->getService('company');

        if ($this->_request->isPost() && $reviewService->search($searchContainer)) {
            $this->redirect($this->url('admin_review_search_results'), array('exit' => true));
            return;
        }

        $this->view->reviewSearchForm = $reviewService->getForm('search-review');
        $this->view->reviewSearchForm->search->setAttrib('id', 'admin_search');

        $this->view->user = Main_Service_Models::getAuthUser();
        $status = isset($_COOKIE["review_status"]) ? $_COOKIE["review_status"] : false;

        if (!in_array($status, Companies_Model_ReviewService::getAvailableStatuses(true))) {
            $status = false;
        }

        $this->view->paginator = $this->getService('review')->getReviewsPaginator($status);
        $this->view->pageId = $this->_request->getParam('page', 1);

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER['REQUEST_URI'];
        $this->view->status = $status;

        $this->view->title = 'Reviews';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Review search results page
     */
    public function reviewSearchResultsAction() {
        if ($this->_request->getParam('page') == 1) {
            $this->redirect($this->url('admin_review_search_results'), array('exit' => true));
            return;
        }

        $searchContainer = new Main_Session_Search_Review();
        $queryData = $searchContainer->getSearchData();

        $paginator = null;
        $status = isset($_COOKIE["review_status"]) ? $_COOKIE["review_status"] : false;

        if (!in_array($status, Companies_Model_ReviewService::getAvailableStatuses(true))) {
            $status = null;
        }

        if ($queryData) {
            $paginator = $this->getService('review')->getSearchPaginator($queryData, null, $status);
        }

        $reviewService = $this->getService('review');
        $this->view->reviewSearchForm = $reviewService->getForm('search-review');
        $this->view->reviewSearchForm->search->setAttrib('id', 'admin_search');

        $this->view->user = Main_Service_Models::getAuthUser();
        $this->view->title = 'Reviews';
        $this->view->query = $queryData;
        $this->view->paginator = $paginator;
        $this->view->status = $status;

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER['REQUEST_URI'];

        $this->_helper->layout->setLayout('account');
    }

    /**
     * Review comment action
     */
    public function reviewCommentAction() {
        $id = $this->_request->getParam("id", 0);
        $review = $this->getService("review")->getTable()->findOneById($id);

        if (!$review || $review->status != Companies_Model_Review::STATUS_PUBLISHED && $review->status != Companies_Model_Review::STATUS_RECONCILIATION) {
            $this->_redirectNotFoundPage();
        }

        $form = Main_Service_Models::getStaticForm("review-comment");
        $form->comment->setValue($review->owner_comment);
        $updated = false;

        if ($this->_request->isPost()) {
            try {
                $this->getService("review")->updateComment($review);
                $updated = true;
            } catch (Exception $e) {
                // pass
            }
        }

        $this->view->review = $review;
        $this->view->form = $form;
        $this->view->updated = $updated;
        $this->view->title = "Owner Comment";
        $this->_helper->layout->setLayout("account");
    }

    /**
     * Publish review
     */
    public function publishReviewAction(){

        $reviewId = $this->_request->getParam("id", 0);
        $review = $this->getService("review")->getTable()->findOneById($reviewId);

        if (!$review) {
            $this->_redirectNotFoundPage();
        }

        $this->getService("review")->publish($review);

        $session = new Zend_Session_Namespace();

        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url("admin_review_publish"),
            array("exit" => true)
        );
    }
  
    /**
     * Review delete
     */
    public function deleteReviewAction() {
        $reviewId = $this->_request->getParam("id", 0);
        $review = $this->getService("review")->getTable()->findOneById($reviewId);

        if (!$review) {
            $this->_redirectNotFoundPage();
        }
        
        $this->getService("review")->deleteReview($review->id, $review->Company->id);

        $session = new Zend_Session_Namespace();
        
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url("admin_reviews"),
            array("exit" => true)
        );
    }

    /**
     * Review confirm
     */
    public function confirmReviewAction() {
        $reviewId = $this->_request->getParam("id", 0);
        $review = $this->getService("review")->getTable()->findOneById($reviewId);

        if (!$review) {
            $this->_redirectNotFoundPage();
        }

        $this->getService("review")->confirm($review);
        $session = new Zend_Session_Namespace();

        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url("admin_reviews"),
            array("exit" => true)
        );
    }
    
    /**
     * Companies listing
     */
    public function companiesAction() {
        if ($this->_request->getParam("page") == 1) {
            $this->redirect($this->url("admin_companies"), array("exit" => true));
            return;
        }
        
        $searchContainer = new Main_Session_Search_Business();
        $searchContainer->clearSearchData();
        
        $companyService = $this->getService("company");
        
        if ($this->_request->isPost() && $companyService->search($searchContainer)) {
            $this->redirect($this->url("admin_companies_search_results"), array("exit" => true));
            return;
        }

        $status = isset($_COOKIE["company_status"]) ? $_COOKIE["company_status"] : false;

        if (!in_array($status, Companies_Model_CompanyService::getAvailableStatuses())) {
            $status = null;
        }
        
        $this->view->companiesSearchForm = $companyService->getForm("search");
        $this->view->companiesSearchForm->search->setAttrib("id", "admin_search");
        $this->view->paginator = $companyService->getCompaniesPaginator(null, null, $status);
        $this->view->pageId = $this->_request->getParam("page", 1);
        $this->view->status = $status;

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER["REQUEST_URI"];

        $this->_helper->layout->setLayout("account");
        $this->view->title = "Companies";
    }
    
    /**
     * Companies search result page
     */
    public function companiesSearchResultAction() {
        if ($this->_request->getParam("page") == 1) {
            $this->redirect($this->url("admin_companies_search_results"), array("exit" => true));
            return;
        }

        $searchContainer = new Main_Session_Search_Business();
        $queryData = $searchContainer->getSearchData();

        $paginator = null;

        if ($queryData) {
            $paginator = $this->getService("company")->getSearchPaginator($queryData, array(
                Companies_Model_Company::STATUS_SUSPENDED,
                Companies_Model_Company::STATUS_NOT_ACTIVATED,
                Companies_Model_Company::STATUS_ACTIVE,
                Companies_Model_Company::STATUS_EXPIRED,
                Companies_Model_Company::STATUS_CANCELLED,
                Companies_Model_Company::STATUS_UNOWNED,
                Companies_Model_Company::STATUS_TAKEN,
            ));
        }

        $companyService = $this->getService('company');
        $this->view->companiesSearchForm = $companyService->getForm('search');
        $this->view->companiesSearchForm->search->setAttrib('id', 'admin_search');

        $this->view->title = 'Companies';
        $this->view->query = $queryData;
        $this->view->paginator = $paginator;

        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Company edit page
     */
    public function editCompanyAction() {
        $companyForm = Main_Service_Models::getStaticForm('company');
        $user = Main_Service_Models::getAuthUser();
        $id = $this->_request->getParam('id', 0);
        $company = $this->getService('company')->getTable()->findOneById($id);
        $updated = false;
        
        if (!$company || $company->status == Companies_Model_Company::STATUS_DELETED) {
            $this->_redirectNotFoundPage();
        }
        
        $companyForm->populate($company->toArray());
        
        if ($this->_request->isPost() && $this->getService('company')->updateCompany($company)) {
            $this->setUpdateCacheHeaders();
            $updated = true;

            $company->refresh();
            $companyForm->populate($company->toArray());
        }
        
        $dirGenerator = new Main_Service_Dir_Generator_Company($company);
        $this->view->companyDirs = $dirGenerator->getFoldersPathsFromRule(false);
        
        $this->view->user = $user;
        $this->view->company = $company;
        $this->view->companyForm = Main_Service_Models::getStaticForm('company');
        $this->view->imageForm = Main_Service_Models::getStaticForm('company-image');
        $this->view->companyImages = $this->getService('company')->getTable('image')->findByCompanyId($company->id);
        $this->view->updated = $updated;

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER['REQUEST_URI'];

        $this->_helper->layout->setLayout('account');
        $this->view->title = $company->name;
    }
    
    /**
     *  Deleting company action
     */
    public function deleteCompanyAction() {
        $id = $this->_request->getParam('id', 0);
        $company = $this->getService('company')->getTable()->findOneById($id);
        $res = false;
        
        if (!$company || $company->status == Companies_Model_Company::STATUS_DELETED) {
            $this->_redirectNotFoundPage();
        }

        $this->getService('company')->deleteCompany($company);

        $this->_redirectToRequestPage();
    }

    /**
     * Banners
     */
    public function companyBannersAction() {
        $user = Main_Service_Models::getAuthUser();
        $id = $this->_request->getParam('id', 0);
        $company = $this->getService('company')->getTable()->findOneById($id);

        if (!$company || $company->status == Companies_Model_Company::STATUS_DELETED) {
            $this->_redirectNotFoundPage();
        }
        $this->view->company = $company;
        $this->_helper->layout->setLayout('account');
        $this->view->title = 'Banners';
    }

    /**
     * Add company image
     */
    public function addImageAction()
    {
        $id = $this->_request->getParam('id', 0);
        $company = $this->getService('company')->getTable()->findOneById($id);

        if (!$company || $company->status == Companies_Model_Company::STATUS_DELETED)
            $this->_redirectNotFoundPage();

        if ($this->_request->isPost()) {
            $this->getService('company')->addImage($company);
        }

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url('admin_company_edit', array( 'id' => $id )),
            array( 'exit' => true )
        );
    }

    /**
     * Delete company logo action
     */
    public function deleteLogoAction()
    {
        $companyId = $this->_request->getParam('id', 0);
        $company = $this->getService('company')->getTable()->findOneById($companyId);

        if (!$company || $company->status == Companies_Model_Company::STATUS_DELETED)
            $this->_redirectNotFoundPage();

        $this->getService('company')->deleteLogo($company);

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url('admin_companies'),
            array( 'exit' => true )
        );
    }

    /**
     * Deleting company image
     */
    public function deleteImageAction()
    {
        $companyId = $this->_request->getParam('id', 0);
        $imageId = $this->_request->getParam('image', 0);

        $company = $this->getService('company')->getTable()->findOneById($companyId);

        if (!$company || $company->status == Companies_Model_Company::STATUS_DELETED)
            $this->_redirectNotFoundPage();

        $this->getService('company')->deleteCompanyImage($imageId, $company);

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url('admin_companies'),
            array( 'exit' => true )
        );
    }
    
    /**
     * Delete company video
     */
    public function deleteVideoAction() {
        $id = $this->_request->getParam('id', 0);
        $this->adminCheckCompanyById($id);
        $this->getService('company')->deleteVideo($id);

        $this->redirect($this->url('admin_company_edit', array('id' => $id)), array('exit' => true));
    }

    /**
     * Company coupon
     */
    public function companyCouponAction() {
        $id = $this->_request->getParam('id', 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService('company')->getCompanyById($id);
        
        $form = Main_Service_Models::getStaticForm('company-coupon', 'companies');
        $form->populate($company->toArray());

        if ($this->_request->isPost()) {
            $this->getService('coupon')->process($company);
            $company->refresh();
        }
        
        $coupon = $company->Coupons->get(0);
        
        if ($coupon) {
            $dirGenerator = new Main_Service_Dir_Generator_Coupon($coupon);
            $this->view->couponDirs = $dirGenerator->getFoldersPathsFromRule(false);
            $this->view->coupon = $coupon;
        }

        $this->view->form = $form;
        $this->view->company = $company;
        $this->view->title = 'Coupon';

        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Delete company coupon
     */
    public function companyDeleteCouponAction() {
        $couponId = $this->_request->getParam('id', 0);
        $couponService = $this->getService('coupon');
        $coupon = $couponService->getTable('coupon')->findOneById($couponId);
        
        if (!$coupon) {
            $this->_redirectNotFoundPage();
        }
        
        $couponService->deleteCoupon($coupon);

        $this->redirect(
            $this->url('admin_company_coupon', array('id' => $coupon->Company->id)),
            array('exit' => true)
        );
    }
    
    /**
     * Company promos list
     */
    public function companyPromosAction()
    {
        $id = $this->_request->getParam('id',0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService('company')->getCompanyById($id);

        $this->view->companyId = $id;
        $this->view->company = $company;
        $this->view->paginator = $this->getService('company-promo')->getCompanyPromosPaginator($id);

        $this->view->title = 'Promos';
        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Company promo page
     */
    public function companyPromoAction()
    {
        $id = (int) $this->_request->getParam('id', 0);
        $promoId = (int) $this->_request->getParam('promoId', 0);

        $promo = $this->getService('company-promo')->getTable()->findByCompanyAndId($id, $promoId);

        if (!$promo)
            $this->_redirectNotFoundPage();

        $this->adminCheckCompanyById($promo->Company->id);
        $company = $this->getService('company')->getCompanyById($id);
        
        if ($this->_request->isPost())
            $this->getService('company-promo')->processCompanyPromo($promo);
        
        $dirGenerator = new Main_Service_Dir_Generator_Company($promo->Company);
        $this->view->companyDirs = $dirGenerator->getFoldersPathsFromRule(false);
        $imgPath = $this->view->getPath($this->view->companyDirs, 'images');
        
        $form = Main_Service_Models::getStaticForm('company-promo', 'companies');

        // set images upload dir for current company
        $form->setKCFinderOptions(array('uploadURL' => $this->view->serverUrl() . $imgPath));

        $form->setAction($this->url('admin_company_promo', array('id' => $promo->company_id, 'promoId' => $promoId)));
        $form->populate($promo->toArray());
        
        $this->view->form = $form;
        $this->view->title = $promo->title;
        $this->view->company = $company;

        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Adding promo page 
     */
    public function addPromoAction()
    {
        $companyId = (int)$this->_request->getParam('companyId', 0);
        $this->adminCheckCompanyById($companyId);
        
        $company = $this->getService('company')->getCompanyById($companyId);
        $form = Main_Service_Models::getStaticForm('company-promo', 'companies');
        
        if($this->_request->isPost()) {
            $promoId = $this->getService('company-promo')->createPromo($company);
            if($promoId) {
                $this->redirect($this->url('admin_company_promo', 
                            array( 'id' => $companyId, 'promoId' => $promoId)),
                            array( 'exit' => true));
            }
        }
        $dirGenerator = new Main_Service_Dir_Generator_Company($company);
        $this->view->companyDirs = $dirGenerator->getFoldersPathsFromRule(false);
        $imgPath = $this->view->getPath($this->view->companyDirs, 'images');
        
        // set images uoload dir for current company
        $form->setKCFinderOptions(array('uploadURL' => $this->view->serverUrl() . $imgPath));
        $form->setAction($this->url('admin_add_promo', array( 'companyId' => $companyId)));
        $this->view->form = $form;
        $this->view->title = 'Add Promo';
        $this->view->company = $company;

        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * User list
     */
    public function usersAction() {
        if ($this->_request->getParam("page") == 1) {
            $this->redirect($this->url("admin_users"), array( "exit" => true ));
            return;
        }
        
        $searchContainer = new Main_Session_Search_Users();
        $searchContainer->clearSearchData();
        $usersService = $this->getService("user", "users");
        
        if ($this->_request->isPost() && $usersService->prepareSearch($searchContainer)) {
            $this->redirect($this->url("admin_users_search_results"), array( "exit" => true ));
            return;
        }
        
        $this->view->usersSearchForm = $usersService->getForm("search", "users");
        $skipAdmins = false;

        if (Main_Service_Models::getAuthUser()->Role->name == Users_Model_Role::SUBADMIN_ROLE) {
            $skipAdmins = true;
        }

        $this->view->paginator = $usersService->getUsersPaginator(null, $skipAdmins);
        $this->view->title = "Users";

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER["REQUEST_URI"];
        $this->_helper->layout->setLayout("account");
    }
    
    /**
     * Users search result page
     */
    public function usersSearchResultAction() {
        if ($this->_request->getParam("page") == 1) {
            $this->redirect($this->url("admin_users_search_results"), array( "exit" => true ));
            return;
        }

        $searchContainer = new Main_Session_Search_Users();
        $queryData = $searchContainer->getSearchData();
        $paginator = null;
        $skipAdmins = false;

        if (Main_Service_Models::getAuthUser()->Role->name == Users_Model_Role::SUBADMIN_ROLE) {
            $skipAdmins = true;
        }

        if ($queryData) {
            $paginator = $this->getService("user", "users")->getUsersPaginator($queryData, $skipAdmins);
        }

        $usersService = $this->getService("user", "users");
        $this->view->usersSearchForm = $usersService->getForm("search", "users");

        $this->view->title = "Users";
        $this->view->query = $queryData;
        $this->view->paginator = $paginator;

        $this->_helper->layout->setLayout("account");
    }
    
    /**
     * Delete user
     */
    public function deleteUserAction()
    {
        $id = $this->_request->getParam('id', 0);
        $user = $this->getService('user', 'users')->getTable('user','users')->findOneById($id);
        
        if (!$user || $user->status == Users_Model_User::STATUS_DELETED)
            $this->_redirectNotFoundPage();

        $this->getService('company')->deleteUser($user);

        $this->_redirectToRequestPage();
    }
    
    /**
     *  Change user pass
     */
    public function editUserAction() {
        $id = $this->_request->getParam("id", 0);
        $userService = $this->getService("user", "users");
        $user = $userService->getTable("user","users")->findOneById($id);

        if (!$user || $user->status == Users_Model_User::STATUS_DELETED) {
            $this->_redirectNotFoundPage();
        }

        $this->view->form = Main_Service_Models::getStaticForm("edit-user", "users");

        if ($this->_request->isPost()) {
            $userService->updateUser($user);
        } else {
            $this->view->form->populate($user->toArray());
        }

        $this->view->user = $user;
        $this->view->title = $user->name ? $user->name : $user->mail;

        $this->_helper->layout->setLayout("account");
    }

    /**
     * Category list
     */
    public function categoriesAction() {
        if ($this->_request->getParam("page") == 1) {
            $this->redirect($this->url("admin_categories"), array("exit" => true));
            return;
        }

        $searchContainer = new Main_Session_Search_Business();
        $searchContainer->clearSearchData();

        $service = $this->getService("category");

        if ($this->_request->isPost() && $service->search($searchContainer)) {
            $this->redirect($this->url("admin_categories_search_results"), array("exit" => true));
            return;
        }
                
        $this->view->categorySearchForm = $service->getForm("search");
        $this->view->categorySearchForm->search->setAttrib("id", "admin_search");
        $this->view->paginator = $this->getService("category")->getCategoriesPaginator();
        $this->view->pageId = $this->_request->getParam("page", 1);

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER["REQUEST_URI"];

        $this->_helper->layout->setLayout("account");
        $this->view->title = "Categories";
    }

    /**
     * Categories search result page
     */
    public function categoriesSearchResultAction() {
        if ($this->_request->getParam("page") == 1) {
            $this->redirect($this->url("admin_categories_search_results"), array("exit" => true));
            return;
        }

        $searchContainer = new Main_Session_Search_Business();
        $queryData = $searchContainer->getSearchData();

        $paginator = null;
        $service = $this->getService("category");

        if ($queryData) {
            $paginator = $service->getSearchPaginator($queryData);
        }

        $this->view->categorySearchForm = $service->getForm("search");
        $this->view->categorySearchForm->search->setAttrib("id", "admin_search");

        $this->view->title = "Categories";
        $this->view->query = $queryData;
        $this->view->paginator = $paginator;

        $this->_helper->layout->setLayout("account");
    }
    
    /**
     * Edit category
     */
    public function editCategoryAction() {
        $id = $this->_request->getParam('id', 0);
        $category = $this->getService('category')->getTable()->findOneById($id);
        $categoryForm = Main_Service_Models::getStaticForm('category');
        
        if (!$category) {
            $this->_redirectNotFoundPage();
        }
        
        $categoryForm->populate($category->toArray());
        
        if ($this->_request->isPost()) {
            $this->getService('category')->updateCategory($category);
        }

        $session = new Zend_Session_Namespace();
        $this->view->returnUrl = isset($session->returnUrl) ? $session->returnUrl : $this->url('admin_categories');
        $this->view->category = $category;
        $this->view->categoryForm = $categoryForm;
        $this->view->title = $category->name;

        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Add category
     */
    public function addCategoryAction()
    {
        if ($this->_request->isPost())
        {
            $id = $this->getService('category')->addCategory();

            if ($id)
            {
                $this->redirect(
                    $this->url('admin_category_edit', array( 'id' => $id )),
                    array( 'exit' => true )
                );

                return;
            }
        }

        $session = new Zend_Session_Namespace();
        $this->view->returnUrl = isset($session->returnUrl) ? $session->returnUrl : $this->url('admin_categories');
        $this->view->categoryForm = Main_Service_Models::getStaticForm('category');;
        $this->view->title = 'Add Category';

        $this->_helper->layout->setLayout('account');
    }
    
    /**
     *  Delete category action
     */
    public function deleteCategoryAction()
    {
        $id = $this->_request->getParam('id', 0);
        $category = $this->getService('category')->getTable()->findOneById($id);
        
        if (!$category)
            $this->_redirectNotFoundPage();

        $this->getService('category')->deleteCategory($category);

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url('admin_categories'),
            array( 'exit' => true )
        );
    }
    
    /**
     * Discount list
     */
    public function discountsAction() {
        $this->view->paginator = $this->getService('discount')->getDiscountsPaginator();
        $this->view->title = 'Discounts';
        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Delete discount
     */
    public function deleteDiscountAction() {
        $id = $this->_request->getParam('id', 0);
        $service = $this->getService('discount');
        $discount = $service->getTable()->findOneById($id);
        
        if (!$discount) {
            $this->_redirectNotFoundPage();
        }

        $service->delete($discount);
        $this->redirect($this->url('admin_discounts'), array('exit' => true));
    }
    
    /**
     * Add discount
     */
    public function addDiscountAction() {
        $service = $this->getService('discount');
        
        if ($this->_request->isPost()) {
            $service->save(null);
            $this->redirect($this->url('admin_discounts'), array('exit' => true));
        }
        
        $this->view->form = Main_Service_Models::getStaticForm('discount');
        $this->view->title = 'Add Discount';

        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Edit discount
     */
    public function editDiscountAction() {
        $id = $this->_request->getParam('id', 0);
        $service = $this->getService('discount');
        $discount = $service->getTable()->findOneById($id);
        
        if (!$discount) {
            $this->_redirectNotFoundPage();
        }
        
        $form = Main_Service_Models::getStaticForm('discount');
        $form->populate($discount->toArray());
        $form->getElement("first_month_discount")->setAttrib("disabled", "disabled");
        $form->getElement("monthly_discount")->setAttrib("disabled", "disabled");
        
        if ($this->_request->isPost() && $service->save($discount)) {
            $this->redirect($this->url('admin_discount_edit', array('id' => $id)), array('exit' => true));
        }
        
        $this->view->form = $form;
        $this->view->title = $discount->code;
        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Company payments page
     */
    public function companyPaymentsAction() {
        $id = $this->_request->getParam('id', 0);
        
        $this->adminCheckCompanyById($id);
        
        $company = $this->getService('company')->getCompanyById($id);
        $service = $this->getService('company-payment');
        
        $this->view->company = $company;
        $this->view->paginator = $service->getPaginator($company->id);

        $this->view->title = 'Payments';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Company reviews page
     */
    public function companyReviewsAction() {
        $id = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService("company")->getCompanyById($id);

        $status = isset($_COOKIE["review_status"]) ? $_COOKIE["review_status"] : false;

        if (!in_array($status, Companies_Model_ReviewService::getAvailableStatuses(true))) {
            $status = false;
        }

        $this->view->status = $status;
        $this->view->company = $company;
        $this->view->paginator = $this->getService("review")->getCompanyReviewsPaginator($company, $status);

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER["REQUEST_URI"];

        $this->view->title = "Reviews";
        $this->_helper->layout->setLayout("account");
    }

    /**
     * Company employees page
     */
    public function companyEmployeesAction() {
        $id = $this->_request->getParam("id", 0);
        $page = $this->_request->getParam("page", 1);

        $this->adminCheckCompanyById($id);

        $company = $this->getService("company")->getCompanyById($id);

        $this->view->company = $company;
        $this->view->paginator = $this->getService("employee")->getPaginator($company->id, $page);

        $this->view->title = "Employees";
        $this->_helper->layout->setLayout("account");
    }

    /**
     * Add employee page
     */
    public function companyAddEmployeeAction() {
        $companyId = (int)$this->_request->getParam('id', 0);
        $this->adminCheckCompanyById($companyId);

        $company = $this->getService('company')->getCompanyById($companyId);
        $form = Main_Service_Models::getStaticForm("employee", "companies");

        if ($this->_request->isPost()) {
            $id = $this->getService("employee")->save($company);

            if ($id) {
                $this->redirect(
                    $this->url("admin_company_employee", array("id" => $companyId, "employeeId" => $id)),
                    array("exit" => true)
                );
            }
        }

        $dirGenerator = new Main_Service_Dir_Generator_Company($company);
        $this->view->companyDirs = $dirGenerator->getFoldersPathsFromRule(false);
        $this->view->form = $form;
        $this->view->title = "Add Employee";
        $this->view->company = $company;

        $this->_helper->layout->setLayout("account");
    }

    /**
     * Edit employee
     */
    public function companyEmployeeAction() {
        $companyId = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($companyId);
        $id = $this->_request->getParam("employeeId", 0);

        $company = $this->getService("company")->getCompanyById($companyId);
        $service = $this->getService("employee");
        $employee = $service->getTable()->findOneByIdAndCompanyId($id, $companyId);

        if (!$employee) {
            $this->_redirectNotFoundPage();
        }

        $form = Main_Service_Models::getStaticForm("employee", "companies");
        $form->populate($employee->toArray());

        if ($this->_request->isPost() && $service->save($company, $employee)) {
            $this->redirect($this->url("admin_company_employee", array("id" => $companyId, "employeeId" => $id)), array("exit" => true));
        }

        $this->view->employee = $employee;
        $this->view->form = $form;
        $this->view->title = $employee->name;
        $this->view->company = $company;
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Company employee reviews page
     */
    public function companyEmployeeReviewsAction() {
        $companyId = $this->_request->getParam('id', 0);
        $this->adminCheckCompanyById($companyId);

        $id = $this->_request->getParam("employeeId", 0);
        $employee = $this->getService("employee")->getTable()->findOneByIdAndCompanyId($id, $companyId);
        $status = isset($_COOKIE["review_status"]) ? $_COOKIE["review_status"] : false;

        if (!in_array($status, Companies_Model_ReviewService::getAvailableStatuses(true))) {
            $status = false;
        }

        if (!$employee) {
            $this->_redirectNotFoundPage();
        }

        $reviewService = $this->getService("review");
        $this->view->company = $this->getService('company')->getCompanyById($id);
        $this->view->paginator = $reviewService->getEmployeeReviewsPaginator($employee, null, $status);
        $this->view->employee = $employee;
        $this->view->status = $status;

        $this->view->title = 'Reviews';
        $this->_helper->layout->setLayout('account');

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER['REQUEST_URI'];
    }

    /**
     * Review deleting
     *
     */
    public function companyEmployeeReviewDeleteAction() {
        $companyId = $this->_request->getParam('id', 0);
        $this->adminCheckCompanyById($companyId);

        $employeeId = $this->_request->getParam("employeeId", 0);
        $employee = $this->getService("employee")->getTable()->findOneByIdAndCompanyId($employeeId, $companyId);

        if (!$employee) {
            $this->_redirectNotFoundPage();
        }

        $id = $this->_request->getParam("reviewId", 0);
        $review = $this->getService('review')->getTable()->findOneById($id);

        if (!$review) {
            $this->_redirectNotFoundPage();
        }

        $this->getService('review')->deleteReview($review->id, $review->Company->id);

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url('admin_company_employee_reviews', array(
                "id" => $companyId,
                "employeeId" => $employeeId
            )),
            array('exit' => true)
        );
    }

    /**
     * Delete employee photo
     */
    public function companyEmployeeDeletePhotoAction() {
        $companyId = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($companyId);
        $id = $this->_request->getParam("employeeId", 0);

        $service = $this->getService("employee");
        $employee = $service->getTable()->findOneByIdAndCompanyId($id, $companyId);

        if (!$employee) {
            $this->_redirectNotFoundPage();
        }

        $service->deletePhoto($employee);

        $this->redirect(
            $this->url("admin_company_employee", array("id" => $companyId, "employeeId" => $id)),
            array("exit" => true)
        );
    }

    /**
     * Delete employee
     */
    public function companyDeleteEmployeeAction() {
        $companyId = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($companyId);
        $id = $this->_request->getParam("employeeId", 0);

        $service = $this->getService("employee");
        $employee = $service->getTable()->findOneByIdAndCompanyId($id, $companyId);

        if (!$employee) {
            $this->_redirectNotFoundPage();
        }

        $service->delete($employee);

        $this->redirect(
            $this->url("admin_company_employees", array("id" => $companyId)),
            array("exit" => true)
        );
    }
    
    /**
     * Cancelling account 
     */
    public function cancelAccountAction() {
        $id = $this->_request->getParam('id', 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService('company')->getTable()->findOneById($id);
        
        if ($company->status == Companies_Model_Company::STATUS_CANCELLED) {
            $this->_redirectNotFoundPage();
        }
        
        if ($this->_request->isPost()) {
            $user = $company->Users->get(0);
            $userService = $this->getService('user', 'users');

            if ($userService->cancelAccount($user)) {
                $this->redirect($this->url('admin_company_billing', array( 'id' => $company->id )), array('exit' => true));
            }            
        }

        $this->view->company = $company;
        $this->view->title = 'Cancel Account';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Convert company to paid
     */
    public function convertAction() {
        $id = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService("company")->getTable()->findOneById($id);

        if ($company->status != Companies_Model_Company::STATUS_ACTIVE || $company->subscription_id) {
            $this->_redirectNotFoundPage();
        }

        if ($this->_request->isPost()) {
            try {
                $this->getService("company")->convertToPaid($company);
                $this->redirect($this->url("admin_company_billing", array("id" => $company->id)), array("exit" => true));
            } catch (Exception $e) {
                // pass
            }
        }

        $this->view->company = $company;
        $this->view->title = "Convert to Paid";
        $this->_helper->layout->setLayout("account");
    }
    
    /**
     * All payments list action
     */
    public function paymentsAction() {
        $this->view->title = 'Payments';

        $months = $this->getService("company-payment")->getAllMonths();
        $monthValues = array();

        foreach ($months as $m) {
            $monthValues[] = $m["value"];
        }

        $month = isset($_COOKIE["payments_month"]) ? $_COOKIE["payments_month"] : false;

        if (!in_array($month, $monthValues)) {
            $month = false;
        }

        $this->view->paginator = $this->getService('company-payment')->getPaginator(null, null, $month);

        $this->view->months = $months;
        $this->view->month = $month;
        $this->view->total = $this->getService("company-payment")->getTotal($month);

        $this->_helper->layout->setLayout('account');
    }

    /**
     * Billing info action
     */
    public function companyBillingAction() {
        $id = $this->_request->getParam('id', 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService('company')->getTable()->findOneById($id);

        $form = Main_Service_Models::getStaticForm('discount-code');
        $cardForm = Main_Service_Models::getStaticForm('company-card');

        if ($this->_request->isPost()) {
            $cvv = $this->_request->getParam("cvv", null);

            if (!$cvv && $company->status == Companies_Model_Company::STATUS_NOT_ACTIVATED) {
                $this->getService('company')->applyDiscount($company);
            } else {
                $this->getService('company')->addCard($company);
            }
        }

        $card = null;

        if ($company->Cards->count() > 0) {
            $card = $company->Cards->get(0);
        }

        $this->view->discountForm = $form;
        $this->view->cardForm = $cardForm;
        $this->view->company = $company;
        $this->view->card = $card;
        $this->view->title = 'Billing';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Articles page
     */
    public function companyArticlesAction() {
        $id = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService("company")->getTable()->findOneById($id);
        $page = $this->_request->getParam("page", 1);

        $this->view->company = $company;
        $this->view->paginator = $this->getService("company-article")->getPaginator($company->id, $page);
        $this->view->title = "Articles";
        $this->_helper->layout->setLayout("account");

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER["REQUEST_URI"];
    }

    /**
     * Add article page
     */
    public function addCompanyArticleAction() {
        $id = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService("company")->getTable()->findOneById($id);
        $form = Main_Service_Models::getStaticForm("company-article", "companies");

        if ($this->_request->isPost()) {
            $id = $this->getService("company-article")->save($company);

            if ($id) {
                $this->redirect(
                    $this->url("admin_company_article", array("id" => $company->id, "articleId" => $id)),
                    array("exit" => true)
                );
            }
        }

        $this->view->form = $form;
        $this->view->title = "Add Article";
        $this->view->company = $company;
        $this->_helper->layout->setLayout("account");
    }

    /**
     * Edit article
     */
    public function companyArticleAction() {
        $id = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService("company")->getTable()->findOneById($id);
        $articleId = $this->_request->getParam("articleId", 0);

        $service = $this->getService("company-article");
        $article = $service->getTable()->findOneByIdAndCompanyId($articleId, $company->id);

        if (!$article) {
            $this->_redirectNotFoundPage();
        }

        $form = Main_Service_Models::getStaticForm("company-article", "companies");
        $form->populate($article->toArray());

        if ($this->_request->isPost() && $service->save($company, $article)) {
            $this->redirect($this->url("admin_company_article", array("id" => $company->id, "articleId" => $articleId)), array("exit" => true));
        }

        $this->view->company = $company;
        $this->view->article = $article;
        $this->view->form = $form;
        $this->view->title = $article->title;

        $this->_helper->layout->setLayout("account");
    }

    /**
     * Article comments page
     */
    public function companyArticleCommentsAction() {
        $id = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService("company")->getTable()->findOneById($id);
        $articleId = $this->_request->getParam("articleId", 0);
        $page = $this->_request->getParam("page", 1);

        $service = $this->getService("company-article");
        $article = $service->getTable()->findOneByIdAndCompanyId($articleId, $company->id);

        if (!$article) {
            $this->_redirectNotFoundPage();
        }

        $this->view->company = $company;
        $this->view->paginator = $service->getCommentsPaginator($article->id, $page);
        $this->view->article = $article;
        $this->view->title = "Comments";
        $this->_helper->layout->setLayout("account");

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER["REQUEST_URI"];
    }

    /**
     * Publish article comment
     */
    public function publishCompanyArticleCommentAction() {
        $id = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService("company")->getTable()->findOneById($id);
        $articleId = $this->_request->getParam("articleId", 0);
        $commentId = $this->_request->getParam("commentId", 0);

        $service = $this->getService("company-article");
        $article = $service->getTable()->findOneByIdAndCompanyId($articleId, $company->id);

        if (!$article) {
            $this->_redirectNotFoundPage();
        }

        $comment = $service->getTable("company-article-comment")->findOneByIdAndArticleId($commentId, $article->id);

        if (!$comment) {
            $this->_redirectNotFoundPage();
        }

        $comment->published = 1 - $comment->published;
        $comment->save();

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url("admin_company_article_comments", array("id" => $id, "articleId" => $articleId)),
            array("exit" => true)
        );
    }

    /**
     * Delete article comment
     */
    public function deleteCompanyArticleCommentAction() {
        $id = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService("company")->getTable()->findOneById($id);
        $articleId = $this->_request->getParam("articleId", 0);
        $commentId = $this->_request->getParam("commentId", 0);

        $service = $this->getService("company-article");
        $article = $service->getTable()->findOneByIdAndCompanyId($articleId, $company->id);

        if (!$article) {
            $this->_redirectNotFoundPage();
        }

        $comment = $service->getTable("company-article-comment")->findOneByIdAndArticleId($commentId, $article->id);

        if (!$comment) {
            $this->_redirectNotFoundPage();
        }

        $service->deleteComment($comment);

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url("admin_company_article_comments", array("id" => $id, "articleId" => $articleId)),
            array("exit" => true)
        );
    }

    /**
     * Delete article
     */
    public function deleteCompanyArticleAction() {
        $id = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService("company")->getTable()->findOneById($id);
        $articleId = $this->_request->getParam("articleId", 0);

        $service = $this->getService("company-article");
        $article = $service->getTable()->findOneByIdAndCompanyId($articleId, $company->id);

        if (!$article) {
            $this->_redirectNotFoundPage();
        }

        $service->delete($article);

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url("admin_company_articles", array("id" => $id)),
            array("exit" => true)
        );
    }

    /**
     * Import companies action
     */
    public function importCompaniesAction() {
        $form = Main_Service_Models::getStaticForm("company-import");

        if ($this->_request->isPost()) {
            $this->getService("company")->import();
        }

        $this->view->form = $form;
        $this->view->title = "Import";
        $this->_helper->layout->setLayout("account");
    }

    /**
     * Company users page
     */
    public function companyUsersAction() {
        $id = $this->_request->getParam("id", 0);
        $this->adminCheckCompanyById($id);
        $company = $this->getService("company")->getCompanyById($id);

        $this->view->company = $company;
        $userService = new Users_Model_UserService();
        $this->view->paginator = $userService->getUsersPaginator(array("company_id" => $company->id));
        $this->view->title = "Users";

        $this->_helper->layout->setLayout("account");
    }

    /**
     *  Approve company listing verification action
     */
    public function approveCompanyAction() {
        $id = $this->_request->getParam("id", 0);
        $company = $this->getService("company")->getTable()->findOneById($id);

        if (!$company || $company->status != Companies_Model_Company::STATUS_TAKEN) {
            $this->_redirectNotFoundPage();
        }

        $this->getService("company")->approve($company);
        $this->_redirectToRequestPage();
    }

    /**
     *  Disapprove company listing verification action
     */
    public function disapproveCompanyAction() {
        $id = $this->_request->getParam("id", 0);
        $company = $this->getService("company")->getTable()->findOneById($id);

        if (!$company || $company->status != Companies_Model_Company::STATUS_TAKEN) {
            $this->_redirectNotFoundPage();
        }

        $this->getService("company")->disapprove($company);
        $this->_redirectToRequestPage();
    }
}
