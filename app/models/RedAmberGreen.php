<?php

# We need ActionPointFactory and MeetingFactory to get the data
require_once 'ActionPointFactory.php';
require_once 'MeetingFactory.php';

class RedAmberGreen
{
    # Action Point constants
    const TO_BE_DONE = 1;
    const RUNNING_OVER_DEADLINE = 2;
    const FINISHED = 3;
    const FINISHED_AFTER_DEADLINE = 4;
    const AVG_GRADE = 5;
    const AP_TOTAL = 6;

    const MAX_GRADE = 22;

    # Meeting constants
    const TAKEN_PLACE = 1;
    const STUDENT_ARRIVED_ON_TIME = 2;
    const CANCELLED = 3;
    const NO_SHOW = 4;
    const M_TOTAL = 5;

    # Instance variables
    protected $toBeDone = 0;
    protected $runningOverDeadline = 0;
    protected $finished = 0;
    protected $finishedAfterDeadline = 0;
    protected $avgGrade = 0;

    protected $takenPlace = 0;
    protected $studentArrivedOnTime = 0;
    protected $cancelled = 0;
    protected $noShow = 0;
    protected $mTotal = 0;

    public function __construct()
    {
        # Action points
        $this->toBeDone = ActionPointFactory::getActionPointsCount(self::TO_BE_DONE);
        $this->runningOverDeadline = ActionPointFactory::getActionPointsCount(self::RUNNING_OVER_DEADLINE);
        $this->finished = ActionPointFactory::getActionPointsCount(self::FINISHED);
        $this->finishedAfterDeadline = ActionPointFactory::getActionPointsCount(self::FINISHED_AFTER_DEADLINE);
        $this->avgGrade = ActionPointFactory::getActionPointsCount(self::AVG_GRADE);

        # Meetings
        $this->takenPlace = MeetingFactory::getMeetingsCount(self::TAKEN_PLACE);
        $this->studentArrivedOnTime = MeetingFactory::getMeetingsCount(self::STUDENT_ARRIVED_ON_TIME);
        $this->cancelled = MeetingFactory::getMeetingsCount(self::CANCELLED);
        $this->noShow = MeetingFactory::getMeetingsCount(self::NO_SHOW);
        $this->mTotal = MeetingFactory::getMeetingsCount(self::M_TOTAL);
    }

    public function getActionPointsToBeDone()
    {
        return $this->toBeDone;
    }

    public function getActionPointsRunningOverDeadline()
    {
        return $this->runningOverDeadline;
    }

    public function getActionPointsFinished()
    {
        return $this->finished;
    }

    public function getActionPointsFinishedAfterDeadline()
    {
        return $this->finishedAfterDeadline;
    }

    public function getActionPointsAvgGrade()
    {
        return $this->avgGrade;
    }

    public function getMeetingsTakenPlace()
    {
        return $this->takenPlace;
    }

    public function getMeetingsStudentArrivedOnTime()
    {
        return $this->studentArrivedOnTime;
    }

    public function getMeetingsCancelled()
    {
        return $this->cancelled;
    }

    public function getMeetingsNoShow()
    {
        return $this->noShow;
    }

    public function getMeetingsTotal()
    {
        return $this->mTotal;
    }


    public function getActionPointsPercentage()
    {
        $AP = array();

        # Add the following results to the array only if the number is not 0

        # To be done compared with running over deadline
        # The smaller the percentage is, the better (we don't want too many APs to be running over deadline)
        if($this->toBeDone != 0 && $this->runningOverDeadline != 0)
            $AP[] = 100*$this->runningOverDeadline/$this->toBeDone;

        # Finished compared with finished after deadline
        # The smaller the percentage is, the better (we don't want too many APs to be finished after deadline)
        if($this->finished != 0 && $this->finishedAfterDeadline != 0)
            $AP[] = 100*$this->finishedAfterDeadline/$this->finished;

        # Average grade compared with maximum grade
        # The bigger the percentage is (the bigger the average mark), the better, therefore it has to be reversed
        if($this->avgGrade != 0)
            $AP[] = abs(100*$this->avgGrade/self::MAX_GRADE - 100);

        # If there's nothing in the array, add one item that is 0
        if(count($AP) == 0)
            $AP[] = 0;

        # Return average percentage, which is sum of the array divided by its count
        # So if there are for example, (20% + 20% + 20%) / 3, the average will be 20%
        return array_sum($AP) / count($AP);

    }

    public function getMeetingsPercentage()
    {
        $M = array();

        # Add the following results to the array only if the number is not 0

        # Total number of meetings compared with number of cancelled meetings
        # The smaller the percentage is, the better (we don't want student or supervisor to cancel meetings very often)
        if($this->mTotal != 0 && $this->cancelled != 0)
            $M[] = 100*$this->cancelled/$this->mTotal;

        # Total number of meetings compared with number of no shows
        # The smaller the percentage is, the better (we don't want student not showing up at meetings)
        if($this->mTotal != 0 && $this->noShow != 0)
            $M[] = 100*$this->noShow/$this->mTotal;

        # The number of meetings that has taken place compared with how many times a student arrived on time
        # The bigger the number is, the better (we want student coming on time every time), therefore it has to be reversed
        if($this->takenPlace != 0 && $this->studentArrivedOnTime != 0)
            $M[] = abs(100*$this->studentArrivedOnTime/$this->takenPlace - 100);

        # If there's nothing in the array, add one item that is 0
        if(count($M) == 0)
            $M[] = 0;

        # Return average percentage, which is sum of the array divided by its count
        return array_sum($M) / count($M);
    }

    public function getStatus()
    {
        # Get avg APs percentage
        $final[] = $this->getActionPointsPercentage();
        # Get avg Meetings percentage
        $final[] = $this->getMeetingsPercentage();

        # Count the final percentage
        # The final percentage has to be reversed, since the lower the percentage we get from APs and Meetings,
        # the better, however the percentage bar is displayed in reversed manner, which means, the bigger the percentage
        # the project is running better
        $finalAvg = abs(array_sum($final) / count($final) - 100);

        # Set default colour for the progress bar
        $color = 'red';

        # If the average is bigger than 80, we have green status
        if($finalAvg >= 80)
            $color = 'green';
        # If the average is bigger than 60 and smaller than 80, it's orange status
        elseif($finalAvg >= 60 && $finalAvg < 80)
            $color = 'orange';

        return array('percentage'=>round($finalAvg),'color'=>$color);
    }
}