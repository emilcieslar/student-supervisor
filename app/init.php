<?php

# Do a check for PHP version
if(!version_compare(PHP_VERSION,'5.4.38','>='))
    die('The version of PHP must be larger or same as 5.4.38');

# Display errors
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

# Load config file
$file = file_get_contents("app/config.json");
$config = json_decode($file);

# Define site URL
define('SITE_URL',$config->{'site_url'});

# Set time zone
date_default_timezone_set($config->{'timezone'});

require_once 'core/DatetimeConverter.php';
require_once 'core/App.php';
require_once 'core/Controller.php';
require_once 'core/GoogleAuth.php';
require_once 'models/DataBoundObject.php';
require_once 'models/ProjectFactory.php';
require_once 'models/HTTPSession.php';
require_once 'models/Notification.php';
require_once 'models/NotificationAP.php';
require_once 'models/NotificationMeeting.php';
require_once 'models/NotificationNote.php';

# Start a more secure session
$objSession = HTTPSession::getInstance();
$objSession->Impress();

# Start up a GoogleAuth
#GoogleAuth::getInstance();

# Check if user is NOT logged in
if(!$objSession->IsLoggedIn())
    # Redirect to login page only if we're not already on login page
    if(isset($_GET['url']) && !(strpos($_GET['url'],'login') !== false))
        header("Location: " . SITE_URL . "login");