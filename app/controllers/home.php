<?php

/**
 * Controls all the functionality associated with default home page
 */
class Home extends Controller
{
    public function index()
    {
        # Default is agenda
        header('Location: ' . SITE_URL . 'agenda');
    }

    public function accessDenied()
    {
        echo "You are not authorized to access this page.";
    }
}