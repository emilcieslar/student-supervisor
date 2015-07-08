<?php

class Home extends Controller
{
    public function index()
    {
        // Default is actionpoints
        header('Location: ' . SITE_URL . 'actionpoints');
    }
}