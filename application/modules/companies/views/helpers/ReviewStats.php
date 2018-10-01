<?php 
/**
 * Return path to the folders by name
 *  
 */
class Zend_View_Helper_ReviewStats extends Zend_View_Helper_Abstract
{
    protected $_rateRangesRule = array(array(0.9, 1), array(0.8, 0.9), array(0.7, 0.8),
                                       array(0.6, 0.7), array(0, 0.6));
    
    
    public function reviewStats()
    {
        return $this;
    }
    
    public function getRatingStatsFromRule(array &$reviewData)
    {
        $result = array();
        
        foreach($this->_rateRangesRule as $aRanges) {
            $cnt = $this->_getReviewsCntFromRange($aRanges, $reviewData);
            $result[] = array('range' => array($aRanges[0]*100, $aRanges[1]*100),
                              'cnt' => $cnt);
        }
        
        return $result;
    }
    
    private function _getReviewsCntFromRange(array $range, &$reviewData)
    {
        $rateMin = array_shift($range);
        $rateMax = array_shift($range);
        $cnt = 0;
        foreach($reviewData as $key => $aData) {
            if($rateMin == 0) {
                if($aData['rating'] >= $rateMin && $aData['rating'] <= $rateMax) {
                    $cnt += $aData['cnt'];
                    unset($aData[$key]);
                }
            }else {
                if($aData['rating'] > $rateMin && $aData['rating'] <= $rateMax) {
                    $cnt += $aData['cnt'];
                    unset($aData[$key]);
                }
            }
        }
        return $cnt;
    }
}