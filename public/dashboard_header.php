<?php

$page = $_GET['url'];

# Decide which link should be active
$activeAg = $activeAP = $activeN = $activeM = $activeNo = "";
switch($page)
{
    case strpos($page,'agenda') !== false: $activeAg = 'class="active"';
        break;
    case strpos($page,'actionpoints') !== false: $activeAP = 'class="active"';
        break;
    case strpos($page,'notes') !== false: $activeN = 'class="active"';
        break;
    case strpos($page,'meetings') !== false: $activeM = 'class="active"';
        break;
    case strpos($page,'notifications') !== false: $activeNo = 'class="active"';
        break;
    default: $activeAg = 'class="active"';
}

?>

<div class="large-12 columns dashboard-links">
    <ul class="inline-list">
        <li <?=$activeAg?>><a href="<?=SITE_URL;?>agenda">Agenda</a></li>
        <li <?=$activeAP?>><a href="<?=SITE_URL;?>actionpoints">Action Points</a></li>
        <li <?=$activeN?>><a href="<?=SITE_URL;?>notes">Notes</a></li>
        <li <?=$activeM?>><a href="<?=SITE_URL;?>meetings">Meetings</a></li>
        <li <?=$activeNo?>><a href="<?=SITE_URL;?>notifications">Notifications</a></li>

        <!--<li class="right"><a><i class="fa fa-question-circle"></i></a></li>-->

    </ul>

</div>

<div class="large-12 columns dashboard-main">