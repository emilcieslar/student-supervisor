<?php

class Login extends Controller
{
    public function index($error = null)
    {
        # If user is logged in but accidentally arrives to login page
        # redirect the user to index page
        if(HTTPSession::getInstance()->IsLoggedIn())
            header('Location: ' . SITE_URL);

        # Get link for google auth
        $link = GoogleAuth::getInstance()->getAuthLink();

        $this->view('login/index', ['error'=>$error, 'link'=>$link], false);
    }

    public function loginPost($post)
    {
        $user = $post['user'];
        $pass = $post['pass'];

        $loggedIn = HTTPSession::getInstance()->Login($user,$pass);

        # If successfully logged in, set projectId and redirect to the index
        if($loggedIn)
        {
            # Get user
            $user = HTTPSession::getInstance()->GetUserObject();
            # Set project id session
            HTTPSession::getInstance()->PROJECT_ID = $user->getProjectId();
            # Set user type session
            HTTPSession::getInstance()->USER_TYPE = $user->getType();
            # Set username session
            HTTPSession::getInstance()->USERNAME = $user->getUsername();
            # Redirect to index
            header('Location: ' . SITE_URL);
        }
        # Otherwise redirect back with an error
        else
            header('Location: ' . SITE_URL . 'login/error');

        // We don't want to perform GET action
        die();
    }

    public function error()
    {
        $this->index(true);
    }

    public function forgotPassword($error = null)
    {
        $this->view('login/pass', ['error'=>$error], false);
    }

    public function generatePass($post)
    {

    }

    public function permissionDenied()
    {
        $this->view('login/permission_denied', [], false);
    }

}