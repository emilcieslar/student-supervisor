<?php

class Home extends Controller
{
    public function index()
    {
        // Default is actionpoints
        header('Location: ' . SITE_URL . 'agenda');
    }

    public function accessDenied()
    {
        echo "You are not authorized to access this page.";
    }
}