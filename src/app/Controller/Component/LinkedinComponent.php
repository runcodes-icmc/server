<?php

class LinkedinComponent extends Component {
    
    private $loginUrl=null;
    private $accessToken = null;
    
    public function redirectToLoginUrl() {
        $params = array('response_type' => 'code',
                        'client_id' => Configure::read('Linkedin.apiKey'),
                        'scope' => Configure::read('Linkedin.scope'),
                        'state' => uniqid('', true), // unique long string
                        'redirect_uri' => Configure::read('Linkedin.redirectUri'),
                  );

        // Authentication request
        $url = 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($params);
        $this->loginUrl = $url;
        $_SESSION['linkedinState'] = $params['state'];
        header("Location: $url");
        exit;
    }
     
    public function getAccessToken($code) {
        $params = array('grant_type' => 'authorization_code',
                        'client_id' => Configure::read('Linkedin.apiKey'),
                        'client_secret' => Configure::read('Linkedin.secretKey'),
                        'code' => $code,
                        'redirect_uri' => Configure::read('Linkedin.redirectUri'),
                  );

        // Access Token request
        $url = 'https://www.linkedin.com/oauth/v2/accessToken';

        // Tell streams to make a POST request
        $context = stream_context_create(
                        array('http' => 
                            array('method' => 'POST',
                            'header' => 'Content-Type: application/x-www-form-urlencoded',
                            'content' => http_build_query($params)
                            )
                        )
                    );

        // Retrieve access token information
        $response = file_get_contents($url, false, $context);

        // Native PHP object, please
        $token = json_decode($response);
//        debug($token);
        // Store access token and expiration time
        $this->accessToken = $token->access_token; // guard this! 
//        $_SESSION['expires_in']   = $token->expires_in; // relative time (in seconds)
//        $_SESSION['expires_at']   = time() + $_SESSION['expires_in']; // absolute time

        return true;
    }

    public function fetch($method, $resource, $body = '') {
        $params = array('oauth2_access_token' => $this->accessToken,
                        'format' => 'json',
                  );

        // Need to use HTTPS
        $url = 'https://api.linkedin.com' . $resource;
        // Tell streams to make a (GET, POST, PUT, or DELETE) request
        $context = stream_context_create(
                        array('http' => 
                            array(
                                'method' => $method,
                                'header' => 'Authorization: Bearer ' . $this->accessToken
                            )
                        )
                    );


        // Hocus Pocus
        $response = file_get_contents($url, false, $context);

        // Native PHP object, please
        return json_decode($response);
    }
}