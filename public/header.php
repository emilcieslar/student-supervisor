<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Supervisor Management System</title>
    <link rel="stylesheet" href="<?=SITE_URL;?>public/css/normalize.css" />
    <link rel="stylesheet" href="<?=SITE_URL;?>public/css/foundation.min.css" />
    <link rel="stylesheet" href="<?=SITE_URL;?>public/css/font-awesome.css" />
    <link rel="stylesheet" href="<?=SITE_URL;?>public/css/foundation-datepicker.css" />
    <link rel="stylesheet" href="<?=SITE_URL;?>public/css/foundation.calendar.css" />
    <link rel="stylesheet" href="<?=SITE_URL;?>public/css/style.css" />
    <script src="<?=SITE_URL;?>public/js/vendor/modernizr.js"></script>
    <script src="<?=SITE_URL;?>public/js/vendor/jquery.js"></script>
    <script src="<?=SITE_URL;?>public/js/foundation-datepicker.js"></script>
</head>
<body>

<nav class="top-bar" data-topbar role="navigation">
    <ul class="title-area">
        <li class="name">
            <h1><a href="<?=SITE_URL?>" class="fa fa-comment"> &nbsp;<strong>s</strong>tudent<strong>s</strong>upervisor</a></h1>
        </li>
        <?php if(HTTPSession::getInstance()->IsLoggedIn()): ?>
            <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
        <?php endif; ?>
    </ul>

    <section class="top-bar-section">
        <!-- Right Nav Section -->
        <ul class="right">
            <?php if(HTTPSession::getInstance()->IsLoggedIn()): ?>
                <li class="has-dropdown">
                    <a href="#">Hello <strong><?=HTTPSession::getInstance()->USERNAME;?></strong></a>
                    <ul class="dropdown">
                        <li><a href="<?=SITE_URL;?>logout">Sign out</a></li>
                    </ul>
                </li>
                <!-- Only supervisor can choose from different projects -->
                <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR): ?>
                    <li class="has-dropdown">
                        <a href="#">Project</a>
                        <ul class="dropdown">
                            <?php foreach(ProjectFactory::getAllProjectsForUser(HTTPSession::getInstance()->GetUserID()) as $project): ?>
                                <?php $active = (HTTPSession::getInstance()->PROJECT_ID == $project->getID()) ? ' class="active"' : ''; ?>
                                <li<?=$active?>><a href="<?=SITE_URL.'projects/'.$project->getID();?>"><?=$project->getName();?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <li><a data-dropdown="drop-project" aria-controls="drop-project" aria-expanded="false"><i class="fa fa-info-circle"></i></a></li>
            <?php endif; ?>
        </ul>

        <?php if(HTTPSession::getInstance()->IsLoggedIn()): ?>
            <div id="drop-project" data-dropdown-content class="f-dropdown content small" aria-hidden="true" tabindex="-1">
                <h5>Project information</h5>
                <h6><?=$data['project']->getName()?></h6>
                <p><?=$data['project']->getDescription()?></p>
                <hr>
                <h5>Participants</h5>
                <?php foreach($data['projectUsers'] as $user): ?>
                    <?=$user->getFirstName() . " " . $user->getLastName() . " (" . $user->getTypeText($user->getType()) . ")"?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </section>
</nav>

<div class="row">


