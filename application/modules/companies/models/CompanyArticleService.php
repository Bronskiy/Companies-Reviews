<?php

/**
 * Company article service
 */
class Companies_Model_CompanyArticleService extends Main_Service_Models {
    /**
     * Paginator for articles
     * @param int $companyId
     * @param int $pageNumber
     * @return Zend_Paginator 
     */
    public function getPaginator($companyId = null, $pageNumber = 1) {
        $query = $this->getTable()->getQueryToFetchAll($companyId);
        $perPage = self::getItemsPerPageDefault();
        
        return parent::getPaginator($query, $pageNumber, $perPage);
    }

    /**
     * Add employee
     * @param Companies_Model_Company $company
     * @param Companies_Model_CompanyArticle $article
     * @return boolean
     */
    public function save(Companies_Model_Company $company, Companies_Model_CompanyArticle $article = null) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm("company-article");
        $newRecord = false;

        if (!$article) {
            $newRecord = true;
        }

        if ($form->isValid($post)) {
            $curConnection = Doctrine_Manager::connection();

            try {
                $curConnection->beginTransaction();

                if (!$article) {
                    $article = new Companies_Model_CompanyArticle();
                }

                $article->fromArray(array(
                    "company_id" => $company->id,
                    "title" => $form->getValue("title"),
                    "intro" => $form->getValue("intro"),
                    "content" => $form->getValue("content"),
                ));

                $article->save();
                $curConnection->commit();

                Main_Service_Models::addProcessingInfo(
                    $newRecord ? "Article added" : "Article saved",
                     Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                );

                return $article->id;
            } catch (Exception $e) {
                $curConnection->rollback();
                self::getLogger()->log($e);
                self::addProcessingInfo("Error saving article, please contact the administrator or try again.");
            }
        } else {
            $form->populate($post);
            self::addProcessingInfo("Please fix the errors below.");
        }

        return false;
    }

    /**
     * Delete article
     * @param Companies_Model_CompanyArticle $article
     */
    public function delete(Companies_Model_CompanyArticle $article) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || !$this->isValidCsrfToken($post)) {
            self::addProcessingInfo("Error deleting article, please contact the administrator or try again.");
            return false;
        }

        try {
            $article->delete();
            self::addProcessingInfo("Article deleted.", Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);
            return true;
        } catch (Exception $e) {
            self::getLogger()->log($e);
        }

        self::addProcessingInfo("Error deleting article, please contact the administrator or try again.");

        return false;
    }

    /**
     * Delete comment
     * @param Companies_Model_CompanyArticleComment $comment
     */
    public function deleteComment(Companies_Model_CompanyArticleComment $comment) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || !$this->isValidCsrfToken($post)) {
            self::addProcessingInfo("Error deleting comment, please contact the administrator or try again.");
            return false;
        }

        try {
            $comment->delete();
            self::addProcessingInfo("Comment deleted.", Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);
            return true;
        } catch (Exception $e) {
            self::getLogger()->log($e);
        }

        self::addProcessingInfo("Error deleting comment, please contact the administrator or try again.");

        return false;
    }

    /**
     * Get all articles for the given company
     * @param null $companyId
     * @return mixed
     */
    public function getAll($companyId = null) {
        return $this->getTable()->getQueryToFetchAll($companyId)->execute();
    }

    /**
     * Get paginator for article comments
     * @param int $articleId
     * @param int $pageNumber
     * @return Zend_Paginator
     */
    public function getCommentsPaginator($articleId = null, $pageNumber = 1) {
        $query = $this->getTable("company-article-comment")->getQueryAll($articleId);
        $perPage = self::getItemsPerPageDefault();

        return parent::getPaginator($query, $pageNumber, $perPage);
    }

    /**
     * Get all comments for article
     * @param int $articleId
     * @return Doctrine_Collection
     */
    public function getComments($articleId = null) {
        return $this->getTable("company-article-comment")->getQueryAll($articleId, true)->execute();
    }

    /**
     * Leave a comment for company article
     * @param Companies_Model_CompanyArticle $article
     */
    public function comment(Companies_Model_CompanyArticle $article) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm("company-article-comment");

        if ($form->isValid($post)) {
            try {
                $comment = new Companies_Model_CompanyArticleComment();
                $comment->article_id = $article->id;
                $comment->name = $form->getValue("name");
                $comment->email = $form->getValue("email");
                $comment->comment = $form->getValue("comment");
                $comment->published = 0;
                $comment->save();

                Main_Service_Models::addProcessingInfo(
                    "Thank you! Your comment has been submitted and will appear on the website after approval.",
                    Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE
                );

                // send notification to owner
                try {
                    $owner = $article->Company->Users->get(0);
                    $userService = new Users_Model_UserService();
                    $userService->sendArticleCommentEmail($owner, $article, $form->getValues());
                } catch (Exception $e) {
                    self::getLogger()->log($e);
                }

                $form->reset();
            } catch (Exception $e) {
                self::getLogger()->log($e);
                self::addProcessingInfo("Error saving comment, please contact the administrator or try again.");
            }
        } else {
            $form->populate($post);

            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME)) {
                self::addProcessingInfo("Error saving comment, please contact the administrator or try again.");
            } else {
                self::addProcessingInfo("Please fix the errors below.");
            }
        }
    }

    /**
     * Get article page count for company
     * @param $companyId
     */
    public function getArticlePageCountForCompany($companyId) {
        $reviews = $this->getTable()->getArticleCount($companyId);
        $perPage = self::getItemsPerPageDefault();
        $pages = (int)($reviews / $perPage);

        if ($reviews % $perPage) {
            $pages++;
        }

        return $pages;
    }
}