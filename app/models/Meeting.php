<?php

class Meeting extends DataBoundObject
{
    protected $Datetime;
    protected $IsRepeating;
    protected $RepeatUntil;
    protected $IsApproved;
    protected $TakenPlace;
    protected $ArrivedOnTime;

    protected $GoogleEventId;

    protected $ProjectId;

    protected function DefineTableName()
    {
        return("Meeting");
    }

    protected function DefineRelationMap()
    {
        return(array(
            "id" => "ID",
            "datetime" => "Datetime",
            "is_repeating" => "IsRepeating",
            "repeat_until" => "RepeatUntil",
            "is_approved" => "IsApproved",
            "taken_place" => "TakenPlace",
            "arrived_on_time" => "ArrivedOnTime",
            "google_event_id" => "GoogleEventId",
            "project_id" => "ProjectId",
            "is_deleted" => "IsDeleted"
        ));
    }

    public function getDatetimeUserFriendly()
    {
        return DatetimeConverter::getUserFriendlyDateTimeFormat($this->getDatetime());
    }

    public function getRepeatUntilUserFriendly()
    {
        return DatetimeConverter::getUserFriendlyDateTimeFormat($this->getRepeatUntil());
    }

}
