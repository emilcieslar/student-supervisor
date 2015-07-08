<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Supervisor Management System</title>
    <link rel="stylesheet" href="http://localhost/master/student-supervisor/public/css/normalize.css" />
    <link rel="stylesheet" href="http://localhost/master/student-supervisor/public/css/foundation.min.css" />
    <link rel="stylesheet" href="http://localhost/master/student-supervisor/public/css/font-awesome.css" />
    <link rel="stylesheet" href="http://localhost/master/student-supervisor/public/css/foundation-datepicker.css" />
    <link rel="stylesheet" href="http://localhost/master/student-supervisor/public/css/style.css" />
    <script src="http://localhost/master/student-supervisor/public/js/vendor/modernizr.js"></script>
    <script src="http://localhost/master/student-supervisor/public/js/vendor/jquery.js"></script>
    <script src="http://localhost/master/student-supervisor/public/js/foundation-datepicker.js"></script>
</head>
<body>

<nav class="top-bar" data-topbar role="navigation">
    <ul class="title-area">
        <li class="name">
            <h1><a href="#" class="fa fa-comment"> ssms</a></h1>
        </li>
        <!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
        <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
    </ul>

    <section class="top-bar-section">
        <!-- Right Nav Section -->
        <ul class="right">
            <?php if(HTTPSession::getInstance()->IsLoggedIn()): ?>
                <li><a href="<?=SITE_URL;?>logout">Logout</a></li>
            <?php endif; ?>
            <li class="has-dropdown">
                <a href="#">ssms project</a>
                <ul class="dropdown">
                    <li><a href="#">Another project</a></li>
                    <li><a href="#">Another one</a></li>
                </ul>
            </li>
        </ul>

    </section>
</nav>

<div class="row">


