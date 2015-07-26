<?php

class Note extends DataBoundObject
{
    protected $Text;
    protected $IsAgenda;
    protected $IsPrivate;
    protected $DatetimeCreated;
    protected $MeetingId;
    protected $UserId;
    protected $ProjectId;
    protected $Title;

    protected function DefineTableName()
    {
        return "Note";
    }

    protected function DefineRelationMap()
    {
        return(array(
            "id" => "ID",
            "text" => "Text",
            "is_agenda" => "IsAgenda",
            "is_private" => "IsPrivate",
            "datetime_created" => "DatetimeCreated",
            "meeting_id" => "MeetingId",
            "user_id" => "UserId",
            "project_id" => "ProjectId",
            "title" => "Title",
            "is_deleted" => "IsDeleted"
        ));
    }

    /**
     * Get excerpt from string
     *
     * @param String $str String to get an excerpt from
     * @param Integer $startPos Position int string to start excerpt from
     * @param Integer $maxLength Maximum length the excerpt may be
     * @return String excerpt
     */
    function getExcerpt() {

        $str = $this->Text;
        $startPos = 0;
        $maxLength = 150;

        if(strlen($str) > $maxLength) {
            $excerpt   = substr($str, $startPos, $maxLength-3);
            $lastSpace = strrpos($excerpt, ' ');
            $excerpt   = substr($excerpt, 0, $lastSpace);
            $excerpt  .= '...';
        } else {
            $excerpt = $str;
        }

        return $excerpt;
    }

    function getUsername()
    {
        require_once('User.php');
        $user = new User($this->UserId);
        return $user->getUsername();
    }

    function getMeetingDatetime()
    {
        if($this->MeetingId != 0)
        {
            require_once('Meeting.php');
            $meeting = new Meeting($this->MeetingId);
            $meetingDatetime = $meeting->getDatetime();
            return DatetimeConverter::getUserFriendlyDateTimeFormat($meetingDatetime);
        } else
            return "";

    }
}