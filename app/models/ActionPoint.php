<?php
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

    public static function addDaysToCurrentDate($numOfDays)
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"));
        $date->modify('+' . $numOfDays . ' day');
        return $date->format('Y-m-d H:i:s');
    }

    public function setDeadlineUserFriendly($day, $month, $year, $hours, $minutes)
    {
        $this->setDeadline("$year-$month-$day $hours:$minutes:00");
    }

    public function getDeadlineUserFriendly()
    {
        return DatetimeConverter::getUserFriendlyDateTimeFormat($this->Deadline);
    }

    public function getDatetimeCreatedUserFriendly()
    {
        return DatetimeConverter::getUserFriendlyDateTimeFormat($this->DatetimeCreated);
    }

    public function hasRunOverDeadline()
    {
        $now = new DateTime('NOW');
        $deadline = DateTime::createFromFormat('Y-m-d H:i:s', $this->Deadline);
        $timeCompleted = DateTime::createFromFormat('Y-m-d H:i:s', $this->DatetimeDone);
        $isDone = $this->IsDone;

        if($now > $deadline && !$isDone || $timeCompleted > $deadline)
            return true;
        else
            return false;
    }
}
