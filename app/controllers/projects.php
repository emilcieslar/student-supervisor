<?php

class Projects extends Controller
{
    public function index($id = null)
    {
        # If we have ID provided, we have to switch to a different project
        if(is_numeric($id))
            # Switch by changing PROJECT_ID session
            HTTPSession::getInstance()->PROJECT_ID = $id;

        # Redirect back to homepage
        header('Location: '.SITE_URL);

    }
}