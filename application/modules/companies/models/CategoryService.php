<?php

/**
 * Category service
 */
class Companies_Model_CategoryService extends Main_Service_Models {
    /**
     * Get paginator
     * @return Zend_Paginator
     */
    public function getCategoriesPaginator() {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $pageNumber = (int)$request->getParam("page", 1);
        
        $query = $this->getTable()->getQueryToFetchAll();
        $itemsPerPage = @self::getConfig()->pagination->categories->itemsPerPage;
        $itemsPerPage = (int)$itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();
        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }

    /**
     * Get letter paginator
     * @return Zend_Paginator
     */
    public function getCategoryPaginatorByLetter($letter, $page) {
        $query = $this->getTable()->getQueryToFetchAllByLetter($letter);
        $itemsPerPage = @self::getConfig()->pagination->categories->itemsPerPage;
        $itemsPerPage = (int)$itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();

        return $this->getPaginator($query, $page, $itemsPerPage);
    }
    
    /**
     * Category adding
     * 
     * @return boolean 
     */
    public function addCategory()
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm('category');

        if ($form->isValid($post))
        {
            $filter = new Main_Service_Filter_StringToUri();
            $uri = $filter->filter($form->getValue('name'));
            $validator = new Zend_Validate_Db_NoRecordExists('categories', 'uri');

            if (!$validator->isValid($uri) || $uri == Companies_Model_Category::UNCATEGORIZED) {
                self::addProcessingInfo('Category with this name already exists');
                return false;
            }

            $category = new Companies_Model_Category();
            $category->fromArray($post);
            $category->uri = $uri;
            $category->save();

            Main_Service_Models::addProcessingInfo('Category added', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

            return $category->id;
        }
        else
        {
            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME))
                self::addProcessingInfo('Error adding category, please contact the administrator or try again.');
            else
                self::addProcessingInfo('Please fix the errors below.');
        }

        return false;
    }
    
    /**
     * Category updating
     * 
     * @param Companies_Model_Category $category 
     */
    public function updateCategory(Companies_Model_Category $category)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = self::getStaticForm('category');

        if ($form->isValid($post)) {
            $filter = new Main_Service_Filter_StringToUri();
            $uri = $filter->filter($form->getValue('name'));

            $categorySearch = $this->getTable()->getByUri($uri);

            if ($categorySearch && $categorySearch->id != $category->id || $uri == Companies_Model_Category::UNCATEGORIZED) {
                self::addProcessingInfo('Category with this name already exists');
                return false;
            }

            $category->fromArray($post);
            $category->uri = $uri;
            $category->save();

            Main_Service_Models::addProcessingInfo('Category saved', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

            return true;
        } else {
            if ($form->getMessages(Main_Forms_Abstract::TOKEN_NAME))
                self::addProcessingInfo('Error updating category, please contact the administrator or try again.');
            else
                self::addProcessingInfo('Please fix the errors below.');
        }

        return false;
    }
    
    /**
     * Delete category
     * @param Companies_Model_Category $category 
     */
    public function deleteCategory(Companies_Model_Category $category)
    {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();

        if (empty($post) || !$this->isValidCsrfToken($post))
        {
            self::addProcessingInfo('Error deleting category, please contact the administrator or try again.');
            return false;
        }
        
        try
        {
            $category->delete();
            Main_Service_Models::addProcessingInfo('Category deleted', Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE);

            return true;
        }
        catch(Exception $e)
        {
            self::getLogger()->log($e->getMessage());
        }

        self::addProcessingInfo('Error deleting category, please contact the administrator or try again.');

        return false;
    }

    /**
     * Search
     * @param Main_Session_Search_Abstract $data
     */
    public function search(Main_Session_Search_Abstract $container) {
        $post = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        $form = $this->getForm("search");

        if ($form->isValid($post)) {
            try {
                $query = $form->getValue("search");
                $container->saveSearchData($query);

                return true;
            } catch(Exception $e) {
                self::getLogger()->log($e);
                self::addProcessingInfo("Error searching category");

                return false;
            }
        } else {
            $form->populate($post);
        }

        return false;
    }

    /**
     * Paginator for search query result
     * @param string $searchString
     */
    public function getSearchPaginator($searchString) {
        $pageNumber = (int)Zend_Controller_Front::getInstance()->getRequest()->getParam("page", 1);
        $query = $this->getTable()->getQueryToFetchSearchAll($searchString);
        $itemsPerPage = @self::getConfig()->pagination->companies->itemsSearchPerPage;
        $itemsPerPage = (int) $itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();

        return $this->getPaginator($query, $pageNumber, $itemsPerPage);
    }

    /**
     * Get all categories
     */
    public function getAll() {
        return $this->getTable()->getAll();
    }

    /**
     * Get category page count
     */
    public function getCategoryPageCount() {
        $count = $this->getTable()->count();
        $pages = (int)($count / 9);

        if ($count % 9) {
            $pages++;
        }

        return $pages;
    }

    /**
     * Get category page count
     * @param $letter
     */
    public function getCategoryPageCountByLetter($letter) {
        $count = $this->getTable()->getCountByLetter($letter);
        $itemsPerPage = @self::getConfig()->pagination->companies->itemsSearchPerPage;
        $itemsPerPage = (int) $itemsPerPage > 0 ? $itemsPerPage : self::getItemsPerPageDefault();
        $pages = (int)($count / $itemsPerPage);

        if ($count % $itemsPerPage) {
            $pages++;
        }

        return $pages;
    }
}