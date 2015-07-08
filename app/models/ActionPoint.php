<?php

class ActionPoint extends DataBoundObject
{
    protected $Deadline;
    protected $DatetimeCreated;
    protected $Text;
    protected $IsApproved;
    protected $IsDone;
    protected $Grade;

    protected $MeetingId;
    protected $UserId;
    protected $ProjectId;

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
            "is_done" => "IsDone",
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
}
