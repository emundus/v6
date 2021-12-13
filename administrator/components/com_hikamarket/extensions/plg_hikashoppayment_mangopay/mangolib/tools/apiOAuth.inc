<?php
namespace MangoPay;

/**
 * Authentication manager
 */
class ApiOAuth extends ApiBase {
    
    /**
     * Get token information to OAuth Authentication
     * @return \MangoPay\OAuthToken OAuthToken object with token information
     */
    public function CreateToken() {
        $urlMethod = $this->GetRequestUrl('authentication_oauth');
        $requestType = $this->GetRequestType('authentication_oauth');
        $requestData = array(
            'grant_type' => 'client_credentials'
        );
        
        $rest = new RestTool(false, $this->_root);
        $authHlp = new AuthenticationHelper($this->_root);
        
        $urlDetails = parse_url($this->_root->Config->BaseUrl);
        $rest->AddRequestHttpHeader('Host: ' . @$urlDetails['host']);
        $rest->AddRequestHttpHeader('Authorization: Basic ' . $authHlp->GetHttpHeaderBasicKey());
        $rest->AddRequestHttpHeader('Content-Type: application/x-www-form-urlencoded');
        $response = $rest->Request($urlMethod, $requestType, $requestData);
        return $this->CastResponseToEntity($response, '\MangoPay\OAuthToken');
    }
}