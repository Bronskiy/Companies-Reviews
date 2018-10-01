<?php

class ErrorController extends Zend_Controller_Action {
    /**
     * Error handler
     */
    public function errorAction()
    {
        $error = $this->_getParam('error_handler');
        $this->view->request = $error->request;

        switch ($error->type)
        {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->title = 'Page Not Found';
                $this->view->code  = 404;

                break;

            default:
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->title = 'Application Error';
                $this->view->code  = 500;

                // conditionally display exceptions
                if ($this->getInvokeArg('displayExceptions'))
                    $this->view->exception = $error->exception;

                // log exception, if logger is available
                $bootstrap = $this->getInvokeArg('bootstrap');

                if ($bootstrap->hasResource('Logger'))
                    $bootstrap->getResource('Logger')->log($error->exception);

                break;
        }
        
        $this->view->request = $error->request;
    }
}

