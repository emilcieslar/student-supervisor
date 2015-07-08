<?php

# Display errors
ini_set('display_errors', 1);

# Load config file
$file = file_get_contents("app/config.json");
$config = json_decode($file);

# Define site URL
define('SITE_URL',$config->{'site_url'});

require_once 'core/DatetimeConverter.php';
require_once 'core/App.php';
require_once 'core/Controller.php';
require_once 'core/GoogleAuth.php';
require_once 'models/DataBoundObject.php';
require_once 'models/ProjectFactory.php';
require_once 'models/HTTPSession.php';

# Start a more secure session
$objSession = HTTPSession::getInstance();

# For testing purposes, set default PROJECT_ID to 1
#$objSession->PROJECT_ID = 1;

# Check if user is NOT logged in
if(!$objSession->IsLoggedIn())
    # Redirect to login page only if we're not already on login page
    if(!(strpos($_GET['url'],'login') !== false))
        header("Location: " . SITE_URL . "login");