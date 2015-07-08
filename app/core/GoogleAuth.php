<?php

class GoogleAuth
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
        $this->client->setClientId('20865495637-3lpoaisr8t4eimfd7j0dah9c9k7a22cg.apps.googleusercontent.com');
        $this->client->setClientSecret('lkgo08wubnJ6iqawMSa3afhz');
        $this->client->setDeveloperKey("AIzaSyD6CFYSOhlUbdRLzZdqBYkxO6qCtFbTbcg");

        $this->client->setRedirectUri('http://localhost/master/student-supervisor/index.php');
        $this->client->addScope("https://www.googleapis.com/auth/calendar");
        $this->client->addScope("https://www.googleapis.com/auth/userinfo.email");
        $this->client->setAccessType('offline');
    }

    public function checkCode($code)
    {
        $this->client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $this->client->getAccessToken();
        $this->setToken();
    }

    /**
     * Saves refresh token to database in order to retrieve it
     * when token expires
     * @return mixed
     */
    public function saveRefreshToken()
    {
        $google_token = json_decode($_SESSION['access_token']);
        return $google_token->refresh_token;
    }

    private function setToken()
    {
        $this->client->setAccessToken($_SESSION['access_token']);
    }

    public function getUserEmail()
    {
        # Get user payload
        $payload = $this->client->verifyIdToken()->getAttributes()['payload'];

        # And email from it
        $email = $payload['email'];

        return $email;
    }

    public function getAuthLink()
    {
        return '<a href="' . $this->client->createAuthUrl() . '">Sign in</a>';
    }
}
