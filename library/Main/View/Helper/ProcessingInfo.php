<?php 
/**
 * Helper processing data from SessionMessenger
 * 
 * @author domencom
 */
class Zend_View_Helper_ProcessingInfo extends Zend_View_Helper_Abstract
{
    protected $_result = array();
    
    /**
     * Classes for message wrappers
     */
    private $_addClasses = array(
        Main_Service_Models::PROCESSING_INFO_ERROR_TYPE   => 'alert-error', // errors info
        Main_Service_Models::PROCESSING_INFO_SUCCESS_TYPE => 'alert-success', // success info
        Main_Service_Models::PROCESSING_INFO_NOTICE_TYPE  => 'alert-info' // other information
    );

    
    public function processingInfo()
    {
        $result = '';
        $info   = $this->_getProcessingInfo();
        
        foreach($info as $type => $data) {
            $mergedData = $this->_getData($data);
            
            if(empty($mergedData)) { continue; }
            
            $this->_result = array(); // cleaning array before next cycle
            
            $result .= $this->view->partial(
                'process_info_partial.phtml',
                array(
                    'class' => $type . ' ' . $this->_addClasses[$type] ,
                    'data'  => $mergedData
            ));
        }
        
        $this->_clearMessages();
        
        return $result;
    }
    
    protected function _getData(array $data)
    {
        foreach ($data as $value) {
            if(is_array($value)) {
                $this->_getData($value);
            }elseif(is_string($value)) {
                $this->_result[] = $value;
            }
        }
        return  $this->_result;
    }
    
    private function _getProcessingInfo()
    {
        $messenger  = $this->_getMessenger();
        $namespaces = $messenger->getAllowedNamespaces();
        $info       = array();
        
        foreach($namespaces as $namespace) {
            $info[$namespace] = $messenger->getCurrentMessages($namespace);
        }

        return $info;
    }
    
    private function _getMessenger()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('SessionMessenger');
    }
    
    private function _clearMessages()
    {
        $messenger  = $this->_getMessenger();
        $messenger->clearCurrentMessages();
    }
}