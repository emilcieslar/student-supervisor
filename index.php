<?php

require_once 'app/init.php';

# Start up the login
/*require 'app/google-api-php-client/src/Google/autoload.php';
# Create google client
$client = new Google_Client();
# Init helper class that will log user in
$googleAuth = new GoogleAuth($client);

# Check whether code was passed back
if(isset($_GET['code']))
{
    # If it was, pass it to the class
    $googleAuth->checkCode($_GET['code']);
}

echo $_SESSION['access_token'];

# Is user logged in?
if(isset($_SESSION['access_token']) && $_SESSION['access_token'])
{
    # If access_token has expired
    if($client->isAccessTokenExpired())
    {
        # We have to display a home page to user to log in again
        unset($_SESSION['access_token']);
        header('Location: /master/student-supervisor');
    } else {
        echo "logout";
    }

} else {
    # No!
    echo $googleAuth->getAuthLink();
}*/

# Start up the app
$app = new App;