<?php
class Main_Service_Dir_Generator_Coupon extends Main_Service_Dir_Generator_Company
{
    protected $_coupon = null;
    
    /**
     *  
     */
    public function __construct(Companies_Model_Coupon $coupon) 
    {
        $this->_coupon = $coupon;
        parent::__construct($this->_coupon->Company);
    }
    
    protected function _createRule()
    {
        $rule = array (
            $this->getMainDir() => array(
                'folders' => array(
                    $this->_coupon->id => array( 
                        'mode' => 0755,
                        'folders' => array( 
                            'coupon_images' => array ( 
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
        // /data/companies/%id%/coupons
        $relPath = $parentRelPath . '/' . $this->_coupon->Company->id . '/coupons';
        // WARNING do not use realpath here
        return $isAbs === true ? $this->getRestrictedPath() . $relPath : $relPath;
    }
}