<?php

require_once 'app/init.php';

# Check whether code was passed back
if(isset($_GET['code']))
{
    # If it was, pass it to the class
    GoogleAuth::getInstance()->checkCode($_GET['code']);
}

# Start up the app
$app = new App;