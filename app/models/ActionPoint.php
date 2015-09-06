<?php

/**
 * Holds data associated with ActionPoint entity
 */
class ActionPoint extends DataBoundObject
{
    protected $Deadline;
    protected $DatetimeCreated;
    protected $Text;
    protected $IsApproved;
    protected $IsDone;
    protected $SentForApproval;
    protected $DatetimeDone;
    protected $Grade;

    protected $MeetingId;
    protected $UserId;
    protected $ProjectId;

    public function __construct($id = NULL, $temp = NULL)
    {
        parent::__construct($id);

        # If we want to instantiate an object from a temporary table in the DB
        if($temp)
            $this->strTableName = $this->strTableName . "Temp";
    }

    protected function DefineTableName()
    {
        return("ActionPoint");
    }

    protected function DefineRelationMap()
    {
        return(array(
            "id" => "ID",
            "deadline" => "Deadline",
            "datetime_created" => "DatetimeCreated",
            "text" => "Text",
            "is_approved" => "IsApproved",
            "is_deleted" => "IsDeleted",
            "is_done" => "IsDone",
            "sent_for_approval" => "SentForApproval",
            "datetime_done" => "DatetimeDone",
            "grade" => "Grade",
            "meeting_id" => "MeetingId",
            "user_id" => "UserId",
            "project_id" => "ProjectId"
        ));
    }

    /**
     * A method that adds a number of days to the current day and returns the date
     * @param $numOfDays
     * @return string the current date advanced by a provided number of days
     */
    public static function addDaysToCurrentDate($numOfDays)
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"));
        $date->modify('+' . $numOfDays . ' day');
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * A method to set the deadline in more user friendly way
     * @param $day
     * @param $month
     * @param $year
     * @param $hours
     * @param $minutes
     */
    public function setDeadlineUserFriendly($day, $month, $year, $hours, $minutes)
    {
        $this->setDeadline("$year-$month-$day $hours:$minutes:00");
    }

    /**
     * A method to return deadline in more user friendly way
     * @return string the deadline
     */
    public function getDeadlineUserFriendly()
    {
        return DatetimeConverter::getUserFriendlyDateTimeFormat($this->Deadline);
    }

    /**
     * A method to return datetime created in more user friendly way
     * @return string the datetime of creation
     */
    public function getDatetimeCreatedUserFriendly()
    {
        return DatetimeConverter::getUserFriendlyDateTimeFormat($this->DatetimeCreated);
    }

    /**
     * A method to find out whether an action point has run over deadline
     * @return bool true if it has run over deadline
     */
    public function hasRunOverDeadline()
    {
        # Get now datetime
        $now = new DateTime('NOW');
        # Get the deadline
        $deadline = DateTime::createFromFormat('Y-m-d H:i:s', $this->Deadline);
        # Get the time the action point was completed
        $timeCompleted = DateTime::createFromFormat('Y-m-d H:i:s', $this->DatetimeDone);

        $isDone = $this->IsDone;

        # Check whether it is running over deadline now, or it was already done and run over deadline
        if($now > $deadline && !$isDone || $timeCompleted > $deadline)
            return true;
        else
            return false;
    }
}
