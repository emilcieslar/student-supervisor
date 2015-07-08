<?php

class GoogleAuth
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;

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
