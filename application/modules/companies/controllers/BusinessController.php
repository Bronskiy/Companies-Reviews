<?php

/**
 * Business controller
 */
class Companies_BusinessController extends Main_Controller_Action {
    /**
     * Business account main page
     */
    public function indexAction() {
        $this->redirect($this->url('business_reviews'), array('exit' => true));
    }

    /**
     * Business account main page 
     */
    public function reviewsAction() {
        if ($this->_request->getParam('page') == 1) {
            $this->redirect($this->url('business_reviews'), array('exit' => true));
            return;
        }
        
        $user = Main_Service_Models::getAuthUser();
        $status = isset($_COOKIE["review_status"]) ? $_COOKIE["review_status"] : false;

        if (!in_array($status, Companies_Model_ReviewService::getAvailableStatuses())) {
            $status = array(
                Companies_Model_Review::STATUS_PUBLISHED,
                Companies_Model_Review::STATUS_RECONCILIATION,
            );
        }
        
        $this->view->paginator = $this->getService('review')->getCompanyReviewsPaginator(
            $user->Company,
            $status
        );

        $searchContainer = new Main_Session_Search_Review();
        $searchContainer->clearSearchData();
        $reviewService = $this->getService('company');

        if ($this->_request->isPost() && $reviewService->search($searchContainer)) {
            $this->redirect($this->url('business_review_search_results'), array('exit' => true));
            return;
        }

        $this->view->reviewSearchForm = $reviewService->getForm('search-review');
        $this->view->reviewSearchForm->search->setAttrib('id', 'business_search');
        $this->view->status = $status;

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER['REQUEST_URI'];

        $this->view->title = 'Reviews';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Review search results page
     */
    public function reviewSearchResultsAction() {
        if ($this->_request->getParam('page') == 1) {
            $this->redirect($this->url('business_review_search_results'), array('exit' => true));
            return;
        }

        $searchContainer = new Main_Session_Search_Review();
        $queryData = $searchContainer->getSearchData();
        $status = isset($_COOKIE["review_status"]) ? $_COOKIE["review_status"] : false;

        if (!in_array($status, Companies_Model_ReviewService::getAvailableStatuses())) {
            $status = array(
                Companies_Model_Review::STATUS_PUBLISHED,
                Companies_Model_Review::STATUS_RECONCILIATION,
            );
        }

        $user = Main_Service_Models::getAuthUser();
        $paginator = null;

        if ($queryData) {
            $paginator = $this->getService('review')->getSearchPaginator(
                $queryData,
                $user->Company,
                $status
            );
        }

        $reviewService = $this->getService('review');
        $this->view->reviewSearchForm = $reviewService->getForm('search-review');
        $this->view->reviewSearchForm->search->setAttrib('id', 'business_search');
        $this->view->status = $status;

        $this->view->user = Main_Service_Models::getAuthUser();
        $this->view->title = 'Reviews';
        $this->view->query = $queryData;
        $this->view->paginator = $paginator;

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER['REQUEST_URI'];

        $this->_helper->layout->setLayout('account');
    }

    /**
     * Reviewers list page
     */
    public function reviewersAction() {
        if ($this->_request->getParam('page') == 1) {
            $this->redirect($this->url('business_reviewers'), array( 'exit' => true ));
            return;
        }

        $user = Main_Service_Models::getAuthUser();
        
        $this->view->paginator = $this->getService('review')->getCompanyReviewersPaginator(
            $user->Company,
            array(
                Companies_Model_Review::STATUS_PUBLISHED,
                Companies_Model_Review::STATUS_RECONCILIATION,
            )
        );

        $this->view->title = 'Reviewers';
        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Request to change the review
     */
    public function requestChangeReviewAction() {
        $id = $this->_request->getParam('id', 0);
        $review = $this->getService('review')->getTable()->findOneById($id);
        $company = Main_Service_Models::getAuthUser()->Company;
        
        if (
            !$review
            || $review->status != Companies_Model_Review::STATUS_RECONCILIATION
            || $company->id != $review->Company->id
        ) {
            $this->_redirectNotFoundPage();
        }
        
        $this->getService('review')->requestChangeReview($review);
        $this->redirect($this->url('business_reviews'), array( 'exit' => true ));
    }

    /**
     * Review comment action
     */
    public function reviewCommentAction() {
        $id = $this->_request->getParam("id", 0);
        $review = $this->getService("review")->getTable()->findOneById($id);
        $company = Main_Service_Models::getAuthUser()->Company;
        
        if (
            !$review
            || $review->status != Companies_Model_Review::STATUS_PUBLISHED
            || $company->id != $review->Company->id
        ) {
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
     * Downloading reviewers list file
     *  
     */
    public function downloadReviewersAction()
    {
        $company = Main_Service_Models::getAuthUser()->Company;
        $fileType = $this->_request->getParam('type');
        if(!$this->getService('review')->downloadReviewers($company, $fileType)) {
            $this->_redirectToRequestPage();
        }
    }
    
    /**
     * Edit company profile action
     */
    public function profileAction() {
        $form = Main_Service_Models::getStaticForm('company');
        $form->removeElement("code_letter");
        $user = Main_Service_Models::getAuthUser();
        $form->populate($user->Company->toArray());
        $updated = false;
        
        if ($this->_request->isPost() && $this->getService('company')->updateCompany($user->Company)) {
            $this->setUpdateCacheHeaders();
            $updated = true;

            $user->Company->refresh();
            $form->populate($user->Company->toArray());
        }
        
        $dirGenerator = new Main_Service_Dir_Generator_Company($user->Company);
        $this->view->companyDirs = $dirGenerator->getFoldersPathsFromRule(false);

        $this->view->user = $user;
        $this->view->company = $user->Company;
        $this->view->companyForm = Main_Service_Models::getStaticForm('company');
        $this->view->imageForm = Main_Service_Models::getStaticForm('company-image');
        $this->view->companyImages = $this->getService('company')->getTable('image')->findByCompanyId($user->Company->id);
        $this->view->updated = $updated;

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER['REQUEST_URI'];

        $this->view->title = 'Profile';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Banners
     */
    public function bannersAction() {
        $user = Main_Service_Models::getAuthUser();
        $this->view->user = $user;
        $this->view->company = $user->Company;

        $this->view->title = 'Banners';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Add company image
     */
    public function addImageAction() {
        $user = Main_Service_Models::getAuthUser();

        if ($this->_request->isPost()) {
            $this->getService('company')->addImage($user->Company);
        }

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url('business_profile'),
            array( 'exit' => true )
        );
    }

    /**
     * Delete company logo action
     */
    public function deleteLogoAction()
    {
        $user = Main_Service_Models::getAuthUser();

        if ($user && $user->Company->exists())
            $this->getService('company')->deleteLogo($user->Company);

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url('business_profile'),
            array( 'exit' => true )
        );
    }
      
    /**
     * Delete company image action
     */
    public function deleteImageAction()
    {
        $imageId = $this->_request->getParam('id', 0);
        $user = Main_Service_Models::getAuthUser();

        if ($user && $user->Company->exists())
            $this->getService('company')->deleteCompanyImage($imageId, $user->Company);

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url('business_profile'),
            array('exit' => true)
        );
    }
    
        /**
     * Delete company video
     * 
     */
    public function deleteVideoAction()
    {
        $company = Main_Service_Models::getAuthUser()->Company;
        $this->getService('company')->deleteVideo($company->id);
        $this->redirect($this->url('business_profile'), array( 'exit' => true));
    }

    
    /**
     * Promos list page 
     */
    public function promosAction()
    {
        $this->view->company = Main_Service_Models::getAuthUser()->Company;
        $this->view->paginator = $this->getService('company-promo')->getCompanyPromosPaginator($this->view->company->id);

        $this->view->title = 'Promos';
        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Edit promo page
     */
    public function promoAction()
    {
        $promoId = (int)$this->_request->getParam('promoId', 0);

        $company = Main_Service_Models::getAuthUser()->Company;
        $promo = $this->getService('company-promo')->getTable()->findOneById($promoId);

        if(!$promo || $company->id != $promo->company_id) { 
            $this->_redirectNotFoundPage(); 
        }
        
        if($this->_request->isPost()) {
            $this->getService('company-promo')->processCompanyPromo($promo);
        }
        
        $dirGenerator = new Main_Service_Dir_Generator_Company($company);
        $this->view->companyDirs = $dirGenerator->getFoldersPathsFromRule(false);
        $imgPath = $this->view->getPath($this->view->companyDirs, 'images');
        
        $form = Main_Service_Models::getStaticForm('company-promo', 'companies');
        // set images uoload dir for current company
        $form->setKCFinderOptions(array('uploadURL' => $this->view->serverUrl() . $imgPath));

        $form->setAction($this->url('business_promo', array('promoId' => $promoId)));
        $form->populate($promo->toArray());
        
        $this->view->form = $form;
        $this->view->title = $promo->title;
        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Add promo page 
     */
    public function addPromoAction()
    {
        $company = Main_Service_Models::getAuthUser()->Company;
        $form = Main_Service_Models::getStaticForm('company-promo', 'companies');
        
        if($this->_request->isPost()) {
            $promoId = $this->getService('company-promo')->createPromo($company);
            if($promoId) {
                $this->redirect($this->url('business_promo', 
                            array('promoId' => $promoId)), 
                            array( 'exit' => true));
            }
        }
        $dirGenerator = new Main_Service_Dir_Generator_Company($company);
        $this->view->companyDirs = $dirGenerator->getFoldersPathsFromRule(false);
        $imgPath = $this->view->getPath($this->view->companyDirs, 'images');
        
        // set images uoload dir for current company
        $form->setKCFinderOptions(array('uploadURL' => $this->view->serverUrl() . $imgPath));
        $form->setAction($this->url('business_add_promo'));
        $this->view->form = $form;
        $this->view->title = 'Add Promo';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Coupon page
     */
    public function couponAction() {
        $user = Main_Service_Models::getAuthUser();
        $company = $user->Company;
        
        $form = Main_Service_Models::getStaticForm('company-coupon', 'companies');

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
     * Delete coupon
     */
    public function deleteCouponAction() {
        $coupon = Main_Service_Models::getAuthUser()->Company->Coupons->get(0);
        
        if (!$coupon->exists()) {
            $this->_redirectNotFoundPage();
        }
        
        $this->getService('coupon')->deleteCoupon($coupon);
        $this->redirect($this->url('business_coupon'), array('exit' => true));
    }
    
    /**
     *  Change user pass
     */
    public function accountAction()
    {
        $userService = $this->getService('user', 'users');
        $user = Main_Service_Models::getAuthUser();

        if ($this->_request->isPost()) {
            $userService->changePassword($user);
        }

        $this->view->changePassForm = Main_Service_Models::getStaticForm('change-password', 'users');
        $this->view->title = 'Account';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Billing info action
     */
    public function billingAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $form = Main_Service_Models::getStaticForm('discount-code');
        $cardForm = Main_Service_Models::getStaticForm('company-card');

        if ($this->_request->isPost()) {
            $cvv = $this->_request->getParam("cvv", null);

            if (!$cvv && in_array($company->status, array(Companies_Model_Company::STATUS_NOT_ACTIVATED, Companies_Model_Company::STATUS_TAKEN))) {
                $this->getService('company')->applyDiscount($company);
            } else {
                $oldStatus = $company->status;
                $success = $this->getService('company')->addCard($company);

                if ($success && $oldStatus == Companies_Model_Company::STATUS_NOT_ACTIVATED) {
                    $this->redirect($this->url("business_profile"), array('exit' => true));
                }
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
     * Cancelling account 
     */
    public function cancelAccountAction() {
        if ($this->_request->isPost()) {
            $user = Main_Service_Models::getAuthUser();
            $userService = $this->getService('user', 'users');

            if ($userService->cancelAccount($user)) {
                Zend_Auth::getInstance()->clearIdentity();
                $session = new Zend_Session_Namespace();
                $session->accountCancelled = true;

                $this->redirect($this->url('account_cancelled'), array('exit' => true));
            }            
        }

        $this->view->title = 'Cancel Account';
        $this->_helper->layout->setLayout('account');
    }
    
    /**
     * Profile payments list 
     */
    public function paymentsAction()
    {
        $fields  = $this->_request->getQuery('fields');
        $fields  = (is_array($fields)) ? $fields : null;
        $service = $this->getService('company-payment');
        $company = Main_Service_Models::getAuthUser()->Company;

        $this->view->paginator = $service->getPaginator($company->id, $fields);
        $this->view->title = 'Payments';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Employees page
     */
    public function employeesAction() {
        $page = $this->_request->getParam("page", 1);
        $company = Main_Service_Models::getAuthUser()->Company;
        $this->view->company = $company;
        $this->view->paginator = $this->getService("employee")->getPaginator($company->id, $page);

        $this->view->title = "Employees";
        $this->_helper->layout->setLayout("account");
    }

    /**
     * Add employee page
     */
    public function addEmployeeAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $form = Main_Service_Models::getStaticForm("employee", "companies");

        if ($this->_request->isPost()) {
            $id = $this->getService("employee")->save($company);

            if ($id) {
                $this->redirect(
                    $this->url("business_employee", array("id" => $id)),
                    array("exit" => true)
                );
            }
        }

        $dirGenerator = new Main_Service_Dir_Generator_Company($company);
        $this->view->companyDirs = $dirGenerator->getFoldersPathsFromRule(false);
        $this->view->form = $form;
        $this->view->title = "Add Employee";

        $this->_helper->layout->setLayout("account");
    }

    /**
     * Edit employee
     */
    public function employeeAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $id = $this->_request->getParam("id", 0);

        $service = $this->getService("employee");
        $employee = $service->getTable()->findOneByIdAndCompanyId($id, $company->id);

        if (!$employee) {
            $this->_redirectNotFoundPage();
        }

        $form = Main_Service_Models::getStaticForm("employee", "companies");
        $form->populate($employee->toArray());

        if ($this->_request->isPost() && $service->save($company, $employee)) {
            $this->redirect($this->url("business_employee", array("id" => $id)), array("exit" => true));
        }

        $this->view->employee = $employee;
        $this->view->form = $form;
        $this->view->title = $employee->name;
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Employee reviews page
     */
    public function employeeReviewsAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $id = $this->_request->getParam("id", 0);

        $service = $this->getService("employee");
        $employee = $service->getTable()->findOneByIdAndCompanyId($id, $company->id);
        $status = isset($_COOKIE["review_status"]) ? $_COOKIE["review_status"] : false;

        if (!in_array($status, Companies_Model_ReviewService::getAvailableStatuses())) {
            $status = array(
                Companies_Model_Review::STATUS_PUBLISHED,
                Companies_Model_Review::STATUS_RECONCILIATION,
            );
        }

        if (!$employee) {
            $this->_redirectNotFoundPage();
        }

        $reviewService = $this->getService("review");
        $this->view->company = $company;
        $this->view->paginator = $reviewService->getEmployeeReviewsPaginator($employee, null, $status);
        $this->view->employee = $employee;
        $this->view->status = $status;

        $this->view->title = 'Reviews';
        $this->_helper->layout->setLayout('account');
    }

    /**
     * Delete employee photo
     */
    public function employeeDeletePhotoAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $id = $this->_request->getParam("id", 0);

        $service = $this->getService("employee");
        $employee = $service->getTable()->findOneByIdAndCompanyId($id, $company->id);

        if (!$employee) {
            $this->_redirectNotFoundPage();
        }

        $service->deletePhoto($employee);

        $this->redirect(
            $this->url("business_employee", array("id" => $id)),
            array("exit" => true)
        );
    }

    /**
     * Delete employee
     */
    public function deleteEmployeeAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $id = $this->_request->getParam("id", 0);

        $service = $this->getService("employee");
        $employee = $service->getTable()->findOneByIdAndCompanyId($id, $company->id);

        if (!$employee) {
            $this->_redirectNotFoundPage();
        }

        $service->delete($employee);

        $this->redirect(
            $this->url("business_employees"),
            array("exit" => true)
        );
    }

    /**
     * Articles page
     */
    public function articlesAction() {
        $page = $this->_request->getParam("page", 1);
        $company = Main_Service_Models::getAuthUser()->Company;
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
    public function addArticleAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $form = Main_Service_Models::getStaticForm("company-article", "companies");

        if ($this->_request->isPost()) {
            $id = $this->getService("company-article")->save($company);

            if ($id) {
                $this->redirect(
                    $this->url("business_article", array("id" => $id)),
                    array("exit" => true)
                );
            }
        }

        $this->view->form = $form;
        $this->view->title = "Add Article";

        $this->_helper->layout->setLayout("account");
    }

    /**
     * Edit article
     */
    public function articleAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $id = $this->_request->getParam("id", 0);

        $service = $this->getService("company-article");
        $article = $service->getTable()->findOneByIdAndCompanyId($id, $company->id);

        if (!$article) {
            $this->_redirectNotFoundPage();
        }

        $form = Main_Service_Models::getStaticForm("company-article", "companies");
        $form->populate($article->toArray());

        if ($this->_request->isPost() && $service->save($company, $article)) {
            $this->redirect($this->url("business_article", array("id" => $id)), array("exit" => true));
        }

        $this->view->article = $article;
        $this->view->form = $form;
        $this->view->title = $article->title;

        $this->_helper->layout->setLayout("account");
    }

    /**
     * Article comments page
     */
    public function articleCommentsAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $id = $this->_request->getParam("id", 0);
        $page = $this->_request->getParam("page", 1);

        $service = $this->getService("company-article");
        $article = $service->getTable()->findOneByIdAndCompanyId($id, $company->id);

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
    public function publishArticleCommentAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $id = $this->_request->getParam("id", 0);
        $commentId = $this->_request->getParam("commentId", 0);

        $service = $this->getService("company-article");
        $comment = $service->getTable("company-article-comment")->findOneByIdAndArticleId($commentId, $id);

        if (!$comment || $comment->Article->company_id != $company->id) {
            $this->_redirectNotFoundPage();
        }

        $comment->published = 1 - $comment->published;
        $comment->save();

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url("business_articles"),
            array("exit" => true)
        );
    }

    /**
     * Delete article comment
     */
    public function deleteArticleCommentAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $id = $this->_request->getParam("id", 0);
        $commentId = $this->_request->getParam("commentId", 0);

        $service = $this->getService("company-article");
        $comment = $service->getTable("company-article-comment")->findOneByIdAndArticleId($commentId, $id);

        if (!$comment || $comment->Article->company_id != $company->id) {
            $this->_redirectNotFoundPage();
        }

        $service->deleteComment($comment);

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url("business_articles"),
            array("exit" => true)
        );
    }

    /**
     * Delete article
     */
    public function deleteArticleAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $id = $this->_request->getParam("id", 0);

        $service = $this->getService("company-article");
        $article = $service->getTable()->findOneByIdAndCompanyId($id, $company->id);

        if (!$article) {
            $this->_redirectNotFoundPage();
        }

        $service->delete($article);

        $session = new Zend_Session_Namespace();
        $this->redirect(
            isset($session->returnUrl) ? $session->returnUrl : $this->url("business_articles"),
            array("exit" => true)
        );
    }

    /**
     * Articles comments page
     */
    public function articlesCommentsAction() {
        $company = Main_Service_Models::getAuthUser()->Company;
        $page = $this->_request->getParam("page", 0);

        $service = $this->getService("company-article");

        $this->view->company = $company;
        $this->view->paginator = $service->getCommentsPaginator(null, $page);
        $this->view->title = "Comments";
        $this->_helper->layout->setLayout("account");

        $session = new Zend_Session_Namespace();
        $session->returnUrl = $_SERVER["REQUEST_URI"];
    }
}
