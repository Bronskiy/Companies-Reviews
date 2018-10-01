<?php
class Main_Service_Dir_Generator_Company extends Main_Service_Dir_Generator_Abstract
{
    protected $_generationRule = array();
    
    protected $_company = null;
    
    /**
     *  
     */
    public function __construct(Companies_Model_Company $company) {
        $this->_company = $company;
        $this->_generationRule = $this->_createRule();
    }
    
    protected function _createRule()
    {
        // TODO get rule from config by default
        $rule = array (
            $this->getMainDir() => array(
                'folders' => array(
                    $this->_company->id => array( // company folder with name from id
                        'mode' => 0755,
                        'folders' => array( 
                            'images' => array ( // id/images
                                'mode' => 0755,
                                'folders' => array(
                                    'gallery' => array( // id/images/gallery
                                        'mode' => 0755
                                    )
                                )
                            ),
                            'reviews' => array ( // id/reviews
                                'mode' => 0755
                            ),
                            'coupons' => array (
                                'mode' => 0755,
                            ),
                            'videos' => array (
                                'mode' => 0755,
                            ),
                            'employees' => array(
                                'mode' => 0755
                            )
                        )
                    )
                )
        ));
        
        return $rule;
    }
    
    public function getGenerationRule() {
        return $this->_generationRule;
    }
    
    public function getMainDir($isAbs = true)
    {
        $relPath = '/data/companies';
        // WARNING do not use realpath here
        return $isAbs === true ? $this->getRestrictedPath() . $relPath : $relPath;
    }
}