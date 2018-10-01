<?php
class Main_Service_Dir_Generator_Review extends Main_Service_Dir_Generator_Company
{
    protected $_review = null;
    
    /**
     *  
     */
    public function __construct(Companies_Model_Review $review) 
    {
        $this->_review = $review;
        parent::__construct($this->_review->Company);
    }
    
    protected function _createRule()
    {
        // TODO get rule from config by default
        $rule = array (
            $this->getMainDir() => array(
                'folders' => array(
                    $this->_review->id => array( // review folder with name from id
                        'mode' => 0755,
                        'folders' => array( 
                            // /data/companies/%_company_id%/reviews/%review_id%/video
                            'video' => array ( 
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
    
    public function getMainDir($isAbs = TRUE)
    {
        $parentRelPath = parent::getMainDir(FALSE);
        // /data/companies/%id%/reviews
        $relPath = $parentRelPath . '/' . $this->_company->id . '/reviews';
        // WARNING do not use realpath here
        return $isAbs === true ? $this->getRestrictedPath() . $relPath : $relPath;
    }
}