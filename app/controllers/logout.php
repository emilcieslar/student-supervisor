<?php

class Logout extends Controller
{
    public function index()
    {
        # Log out a user
        HTTPSession::getInstance()->LogOut();
        # Redirect back to home page
        header('Location: ' . SITE_URL);
    }
}