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

        if($this->toBeDone != 0 && $this->runningOverDeadline != 0)
            $AP[] = 100*$this->runningOverDeadline/$this->toBeDone;
        if($this->finished != 0 && $this->finishedAfterDeadline != 0)
            $AP[] = 100*$this->finishedAfterDeadline/$this->finished;
        if($this->avgGrade != 0)
            # It's reversed, because the bigger the mark, the better
            $AP[] = abs(100*$this->avgGrade/self::MAX_GRADE - 100);

        if(count($AP) == 0)
            $AP[] = 0;

        return abs(array_sum($AP) / count($AP) - 100);

    }

    public function getMeetingsPercentage()
    {
        $M = array();

        if($this->mTotal != 0 && $this->cancelled != 0)
            $M[] = 100*$this->cancelled/$this->mTotal;
        if($this->mTotal != 0 && $this->noShow != 0)
            $M[] = 100*$this->noShow/$this->mTotal;
        if($this->takenPlace != 0 && $this->studentArrivedOnTime != 0)
            $M[] = abs(100*$this->studentArrivedOnTime/$this->takenPlace - 100);

        if(count($M) == 0)
            $M[] = 0;

        return abs(array_sum($M) / count($M) - 100);
    }

    public function getStatus()
    {
        $final[] = $this->getActionPointsPercentage();
        $final[] = $this->getMeetingsPercentage();

        $finalAvg = abs(array_sum($final) / count($final));

        $color = 'red';

        if($finalAvg >= 80)
            $color = 'green';
        elseif($finalAvg >= 60 && $finalAvg < 80)
            $color = 'orange';

        return array('percentage'=>round($finalAvg),'color'=>$color);
    }
}