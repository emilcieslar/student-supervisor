<?php

class GoogleAuth
{
    protected $client;
    public static $auth;

    private static $instance;

    private function __construct()
    {
        # Start up the login
        require_once 'app/google-api-php-client/src/Google/autoload.php';
        # Create google client
        $client = new Google_Client();

        $this->client = $client;
        $this->client->setClientId('20865495637-3lpoaisr8t4eimfd7j0dah9c9k7a22cg.apps.googleusercontent.com');
        $this->client->setClientSecret('lkgo08wubnJ6iqawMSa3afhz');
        $this->client->setDeveloperKey("AIzaSyD6CFYSOhlUbdRLzZdqBYkxO6qCtFbTbcg");

        $this->client->setRedirectUri(SITE_URL.'index.php');
        $this->client->addScope("https://www.googleapis.com/auth/calendar");
        $this->client->addScope("https://www.googleapis.com/auth/userinfo.email");
        $this->client->setAccessType('offline');
    }

    public static function getInstance()
    {
        if(!isset(GoogleAuth::$instance))
            GoogleAuth::$instance = new GoogleAuth();

        # Set auth code
        GoogleAuth::$auth = HTTPSession::getInstance()->ACCESS_TOKEN;

        # If auth code is not empty, it means that user was successfully signed in
        # using Google sign in, therefore set token for client and authorise using HTTPSession
        if(!empty(GoogleAuth::$auth))
        {
            GoogleAuth::$instance->setToken();
            # TODO: First we need to check if session is still valid
            HTTPSession::getInstance()->LoginGoogle(GoogleAuth::$instance->getUserEmail());
        }

        return GoogleAuth::$instance;
    }

    public function checkCode($code)
    {
        $this->client->authenticate($_GET['code']);
        HTTPSession::getInstance()->ACCESS_TOKEN = $this->client->getAccessToken();
        $this->setToken();
    }

    /**
     * Saves refresh token to database in order to retrieve it
     * when token expires
     * @return mixed
     */
    public function saveRefreshToken()
    {
        $google_token = json_decode(HTTPSession::getInstance()->ACCESS_TOKEN);
        return $google_token->refresh_token;
    }

    private function setToken()
    {
        $this->client->setAccessToken(HTTPSession::getInstance()->ACCESS_TOKEN);
    }

    public function getUserEmail()
    {
        # Get ID token from session
        $idToken = json_decode(HTTPSession::getInstance()->ACCESS_TOKEN)->{'id_token'};

        # Get user payload
        $payload = $this->client->verifyIdToken($idToken)->getAttributes()['payload'];

        # And email from it
        $email = $payload['email'];

        return $email;
    }

    public function isLoggedIn()
    {
        if(!empty(self::$auth)) {
            $idToken = json_decode(self::$auth)->{'id_token'};
            # If ID token is verified correctly, user is still logged in
            if ($this->client->verifyIdToken($idToken))
                return true;
            else
                return false;
        }
        else
            return false;
    }

    public function getAuthLink()
    {
        return $this->client->createAuthUrl();
    }
}
