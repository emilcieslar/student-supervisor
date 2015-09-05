<?php

class Meeting extends DataBoundObject
{
    protected $Datetime;
    protected $IsRepeating;
    protected $RepeatUntil;
    protected $IsApproved;
    protected $IsCancelled;
    protected $ReasonForCancel;
    protected $TakenPlace;
    protected $ArrivedOnTime;

    protected $GoogleEventId;

    protected $ProjectId;

    protected $IsNext;

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
            "is_cancelled" => "IsCancelled",
            "reason_for_cancel" => "ReasonForCancel",
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

    public function getPreviousMeeting()
    {
        require_once 'MeetingFactory.php';
        $prevMeeting = MeetingFactory::getPreviousMeeting($this);
        return $prevMeeting;
    }

    public function getIsNoShow($buttons = true)
    {
        $currentDate = new DateTime();

        # We want to hide the buttons after 7 days from no show (supervisor need some time to decide
        # if it was no show or not)
        if($buttons)
            $currentDate->sub(new DateInterval('P7D'));

        $meetingDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $this->Datetime);

        return ($meetingDateTime < $currentDate && !$this->TakenPlace && !$this->IsCancelled);
    }

    public function getIsNext()
    {
        if(!$this->IsNext)
            if(MeetingFactory::getNextMeeting())
                $this->IsNext = $this->ID == MeetingFactory::getNextMeeting()->getID();

        return $this->IsNext;
    }

}
