<?php

class Login extends Controller
{
    public function index($error = null)
    {
        # If user is logged in but accidentally arrives to login page
        # redirect the user to index page
        if(HTTPSession::getInstance()->IsLoggedIn())
            header('Location: ' . SITE_URL);

        $this->view('login/index', ['error'=>$error]);
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
            # Set project id
            HTTPSession::getInstance()->PROJECT_ID = $user->getProjectId();
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

}