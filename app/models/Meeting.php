<?php

/**
 * Holds data associated with Meeting entity
 */
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

    /**
     * A method to return the previous meeting object
     * @return Meeting|null
     */
    public function getPreviousMeeting()
    {
        require_once 'MeetingFactory.php';
        $prevMeeting = MeetingFactory::getPreviousMeeting($this);
        return $prevMeeting;
    }

    /**
     * A method to find out whether the meeting had taken place or not and if a student arrived
     * @param bool $buttons hide editing buttons after 7 days
     * @return bool
     */
    public function getIsNoShow($buttons = true)
    {
        # Datetime now
        $currentDate = new DateTime();

        # We want to hide the buttons after 7 days from no show (supervisor need some time to decide
        # if it was no show or not)
        if($buttons)
            $currentDate->sub(new DateInterval('P7D'));

        # When did the meeting take place?
        $meetingDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $this->Datetime);

        # Return true if the meeting is in the past and has not taken place and hasn't been cancelled
        return ($meetingDateTime < $currentDate && !$this->TakenPlace && !$this->IsCancelled);
    }

    /**
     * A method to find out if this meeting is the next meeting
     * @return bool true if it is the next meeting
     */
    public function getIsNext()
    {
        # If the variable IsNext is not set yet, set it
        if(!$this->IsNext)
            if(MeetingFactory::getNextMeeting())
                $this->IsNext = $this->ID == MeetingFactory::getNextMeeting()->getID();

        # Otherwise just return
        return $this->IsNext;
    }

}
