<?php

/**
 * Disable view plugin
 */
class Main_Controller_Plugin_DisableView extends Zend_Controller_Plugin_Abstract
{
    /**
     * Pre-Dispatch handler
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if ($request->getModuleName() != 'api')
            return;

        Zend_Controller_Action_HelperBroker::getExistingHelper('layout')->disableLayout();
        Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer')->setNeverRender(true);
    }
}
