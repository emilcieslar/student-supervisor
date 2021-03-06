<?php

/**
 * Controls all the functionality associated with Login
 */
class Login extends Controller
{
    public function index($error = null)
    {
        # If user is logged in but accidentally arrives to login page
        # redirect the user to home page
        if(HTTPSession::getInstance()->IsLoggedIn())
            header('Location: ' . SITE_URL);

        # Get link for google auth (this is for the "Sign in using Google" button
        $link = GoogleAuth::getInstance()->getAuthLink();

        # Display login page
        $this->view('login/index', ['error'=>$error, 'link'=>$link], false);
    }

    /**
     * A method to process POST request for logging in
     * @param null $post the $_POST array
     */
    public function loginPost($post)
    {
        # Get the values
        $user = $post['user'];
        $pass = $post['pass'];

        # Try to log in the user with provided values
        $loggedIn = HTTPSession::getInstance()->Login($user,$pass);

        # If successfully logged in, set following variables and redirect to the index
        if($loggedIn)
        {
            # Get user
            $user = HTTPSession::getInstance()->GetUserObject();
            # Set project id session
            HTTPSession::getInstance()->PROJECT_ID = $user->getProjectId();
            # Set user type session (authorization purposes)
            HTTPSession::getInstance()->USER_TYPE = $user->getType();
            # Set username session
            HTTPSession::getInstance()->USERNAME = $user->getUsername();
            # Redirect to index
            header('Location: ' . SITE_URL);
        }
        # Otherwise redirect back with an error
        else
            header('Location: ' . SITE_URL . 'login/error');

        die();
    }

    /**
     * A method to redirect to index with an error
     */
    public function error()
    {
        $this->index(true);
    }

    /**
     * A method to display a form to generate a new password
     * This is not fully implemented as it was not in the requirements
     * It is left here for future implementation
     * @param null $error
     */
    public function forgotPassword($error = null)
    {
        $this->view('login/pass', ['error'=>$error], false);
    }

    /**
     * Similarly to forgotPassword method, it is left here for future implementation
     * @param $post
     */
    public function generatePass($post)
    {

    }

    /**
     * A method to redirect user to the permission denied page if a user
     * appears unauthorized to access certain area
     */
    public function permissionDenied()
    {
        $this->view('login/permission_denied', [], false);
    }

}