<?php

/**
 * Holds data associated with Note entity
 */
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
     * A method from phpsnaps.com: http://www.phpsnaps.com/snaps/view/get-excerpt-from-string/
     * @return String excerpt
     */
    function getExcerpt() {

        # Text to be excerpted
        $str = $this->Text;
        # Starting position
        $startPos = 0;
        # Maximum length of the excerpt
        $maxLength = 150;

        # If the string is longer than maximum length of the excerpt
        if(strlen($str) > $maxLength)
        {
            # Create the excerpt
            $excerpt   = substr($str, $startPos, $maxLength-3);
            $lastSpace = strrpos($excerpt, ' ');
            $excerpt   = substr($excerpt, 0, $lastSpace);
            $excerpt  .= '...';

        # Otherwise just asssign the excerpt
        } else
            $excerpt = $str;

        # Return the excerpt
        return $excerpt;
    }

    /**
     * A method to return the username of the creator of the nate
     * @return String the username
     */
    function getUsername()
    {
        require_once('User.php');
        # Get the user object
        $user = new User($this->UserId);
        # Return his/hers username
        return $user->getUsername();
    }

    /**
     * A method to return the datetime of meeting associated with the note
     * @return string the datetime
     */
    function getMeetingDatetime()
    {
        # If this note has a meeting associated with it
        if($this->MeetingId != 0)
        {
            require_once('Meeting.php');
            # Get the meeting object
            $meeting = new Meeting($this->MeetingId);
            # Get datetime and return it in user friendly format
            $meetingDatetime = $meeting->getDatetime();
            return DatetimeConverter::getUserFriendlyDateTimeFormat($meetingDatetime);

        # Otherwise return an empty string
        } else
            return "";

    }
}