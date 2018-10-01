<?php

/**
 * Service controller
 */
class Companies_ServiceController extends Main_Controller_Action
{
    /**
     * Init controller
     */
    public function init() {
        parent::init();
        $this->_helper->contextSwitch()->addActionContext('company-banner', 'json')->initContext();
    }
    
    /**
     * Company banner action
     */
    public function companyBannerAction() {
        if ($this->_request->getMethod() == "OPTIONS") {
            $this->_sendResponseOptionHeaders();
        }
        
        if ($this->_request->isPost()) {
            $this->view->result = false;
            $this->view->answer = "Incorrect request";
            header("Access-Control-Allow-Origin: *", true);

            $companyId = $this->_request->getParam("company_id", 0);
            $style = $this->_request->getParam("style", 0);

            if (!$companyId) {
                $input = file_get_contents("php://input");

                if ($input) {
                    foreach (explode("&", $input) as $var) {
                        list($k, $v) = explode("=", $var);

                        if ($k == "company_id") {
                            $companyId = (int) $v;
                        } else if ($k == "style") {
                            $style = $v;
                        }
                    }
                }

                if (!$companyId) {
                    return;
                }
            }

            if (!in_array($style, array("small", "vertical", "square"))) {
                $this->view->answer = "Widget error: invalid style.";
                return;
            }

            $companyCollection = $this->getService("company")->getTable("company")->findCompany($companyId);
            $company = $companyCollection->get(0);
            
            if ($companyId <= 0 || $company->exists() === false) {
                $this->view->answer = "Widget error: can not find company. Possibly incorrect request params.";
                return;
            }

            $dirsGenerator = new Main_Service_Dir_Generator_Company($company);
            
            $this->view->companyDirsRel = $dirsGenerator->getFoldersPathsFromRule(false);
            $this->view->company = $company;
            $this->view->ratings = Main_Service_Company_Rating_Loader::getAllRatings($company->rating);

            $this->view->reviews = $this->getService("review")
                ->getTable("review")
                ->getCompanyLastReviews($companyId, $style == "square" ? 4 : 3);

            $this->view->result = true;
            $this->view->message = $this->view->render("service/banners/" . $style . ".phtml");

            unset($this->view->company);
            unset($this->view->ratings);
            unset($this->view->reviews);
            unset($this->view->companyDirsRel);
        }
    }
    
    /**
     * headers for origin validate respond
     */
    private function _sendResponseOptionHeaders() {
        header('Access-Control-Allow-Origin: *', true);
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS', true);
        header('Access-Control-Allow-Headers: X-PINGARUNER, CONTENT-TYPE', true);
        header('Access-Control-Max-Age: 1728000', true);
        exit;
    }
    
    /**
     * Webhook for braintree payment service
     * @link https://www.braintreepayments.com/docs/php/webhooks/overview
     */
    public function braintreeWebhookAction() {
        $challenge = $this->_request->getQuery('bt_challenge');
        Main_Service_Models::configureBrainTree();        

        // destination verification
        if ($this->_request->getMethod() == 'GET' && $challenge) {
            echo Braintree_WebhookNotification::verify($challenge);
            exit();
        }

        if ($this->_request->isPost()) {
            $this->getService('company')->processBraintreeWebHook();
        }
    }
}