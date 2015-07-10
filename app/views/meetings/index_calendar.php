<?php

# Days to display
define('DAYS_TO_DISPLAY',35);

if(!isset($data['month']) && !isset($data['year']))
{
    # Get actual day
    $day = date('d');
    # Get actual month
    $month = date('m');
    # Get name of the month
    $monthName = date('F');
    # Get actual year
    $year = date('Y');
}
else
{
    $day = 1;
    $month = $data['month'];

    # Get month name
    $dateObj   = DateTime::createFromFormat('!m', $month);
    $monthName = $dateObj->format('F');

    # Get year
    $year = $data['year'];

}




# Set calendar
$cal = 'CAL_GREGORIAN';
# Get number of days in the month
$numDays = cal_days_in_month(CAL_GREGORIAN,$month,$year);
# Get starting day of the month
$startingDay = (int) date('w', strtotime($monthName . " " . $year));
# If it's sunday it's 0, we want 7
$startingDay = ($startingDay == 0) ? 7 : $startingDay;

# Get last month
$lastMonth = mktime(0, 0, 0, $month-1, $day, $year);
# Get last month in user friendly format
$lastMonthNum = date("n", mktime(0, 0, 0, $month-1, $day, $year));
# Get number of days in the last month
$numDaysLastMonth = date("t", mktime(0,0,0, $month - 1));
# Get year of last month
$lastMonthYear = date("Y", mktime(0,0,0, $month - 1, $day, $year));

# Get next month
$nextMonth = mktime(0, 0, 0, $month+1, $day, $year);
# Get next month in user friendly format
$nextMonthNum = date("n", mktime(0, 0, 0, $month+1, $day, $year));
# Get number of days in the next month
$numDaysNextMonth = date("t", mktime(0,0,0, $month + 1));
# Get year of next month
$nextMonthYear = date("Y", mktime(0,0,0, $month + 1, $day, $year));

# Get starting day for the last month for loop (we have to add 2, because
# starting day is for example Wednesday, which is 3 and last month had 30 days,
# that means 30 - 3 = 27. But we want to start with 29 (and display last 2 days - Monday and Tuesday)
$lastMonthForLoopStart = $numDaysLastMonth - $startingDay + 2;

# Same with next month
$nextMonthForLoopEnd = DAYS_TO_DISPLAY - $numDays - $startingDay + 2;
# If the count is over DAYS_TO_DISPLAY, it cannot be
#$nextMonthForLoopEnd = (($nextMonthForLoopEnd+$numDays+$startingDay) > DAYS_TO_DISPLAY) ? $nextMonthForLoopEnd-1 : $nextMonthForLoopEnd;

echo $startingDay;

?>

<div class="large-12 columns">
    <h3>Dashboard</h3>
</div>

<div class="large-12 columns dashboard-links">
    <ul class="inline-list">
        <li><a href="<?=SITE_URL;?>actionpoints">Action Points</a></li>
        <li><a href="#">Notes</a></li>
        <li class="active"><a href="<?=SITE_URL;?>meetings">Meetings</a></li>
        <li><a href="#">Notifications</a></li>
    </ul>
</div>

<div class="large-12 columns dashboard-main">

    <h4><?=$monthName?> <?=$year?></h4>

    <ul class="calendar">
        <li class="title clearfix">
            <a href="<?=SITE_URL."meetings/".$lastMonthNum."/".$lastMonthYear?>" class="left">Previous month</a>
            <a href="<?=SITE_URL."meetings/".$nextMonthNum."/".$nextMonthYear?>" class="right">Next month</a>
        </li>
        <li class="day-header">
            <div class="small-1 medium-1 large-1 day">
                <span class="show-for-medium-up">Monday</span>
                <span class="show-for-small">Mon</span>
            </div>
            <div class="small-1 medium-1 large-1 day">
                <span class="show-for-medium-up">Tuesday</span>
                <span class="show-for-small">Tue</span>
            </div>
            <div class="small-1 medium-1 large-1 day">
                <span class="show-for-large-up">Wednesday</span>
                <span class="show-for-medium-down">Wed</span>
            </div>
            <div class="small-1 medium-1 large-1 day">
                <span class="show-for-medium-up">Thursday</span>
                <span class="show-for-small">Thu</span>
            </div>
            <div class="small-1 medium-1 large-1 day">
                <span class="show-for-medium-up">Friday</span>
                <span class="show-for-small">Fri</span>
            </div>
            <div class="small-1 medium-1 large-1 day">
                <span class="show-for-medium-up">Saturday</span>
                <span class="show-for-small">Sat</span>
            </div>
            <div class="small-1 medium-1 large-1 day">
                <span class="show-for-medium-up">Sunday</span>
                <span class="show-for-small">Sun</span>
            </div>
        </li>
        <li class="week">

            <!-- PREVIOUS MONTH LOOP -->
            <?php for($i=$lastMonthForLoopStart;$i<=$numDaysLastMonth;$i++): ?>
                <div class="small-1 medium-1 large-1 day previous-month"><?=$i?></div>
            <?php endfor; ?>

            <!-- THIS MONTH LOOP -->
            <?php for($i=1;$i<=$numDays;$i++): ?>
                <?php if($i == $day): ?>
                    <div class="small-1 medium-1 large-1 day today"><?=$i?></div>
                <?php else: ?>
                    <div class="small-1 medium-1 large-1 day"><?=$i?></div>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- NEXT MONTH LOOP -->
            <?php for($i=1;$i<$nextMonthForLoopEnd;$i++): ?>
                <div class="small-1 medium-1 large-1 day next-month"><?=$i?></div>
            <?php endfor; ?>
        </li>
    </ul>

</div><!-- dashboard-main -->