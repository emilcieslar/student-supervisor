<?php

class Home extends Controller
{
    public function index($name = '')
    {
        // Default is actionpoints
        header('Location: ' . SITE_URL . 'actionpoints');
    }
}