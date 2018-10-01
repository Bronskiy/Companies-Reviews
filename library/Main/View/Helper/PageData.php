<?php 
/**
 * Helper rendering page data for Curent Language
 * 
 * @author domencom
 */
class Zend_View_Helper_PageData extends Zend_View_Helper_Abstract
{
    public function pageData($fieldName)
    {
        $page = $this->view->getPage();
        if(null === $page || empty($fieldName)) {
            return FALSE;
        }
        $pageLang = $page->PageLang->getFirst();
        return $pageLang->$fieldName;
    }
    
}