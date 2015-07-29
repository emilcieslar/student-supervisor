<?php

require_once("Meeting.php");

class MeetingFactory
{
    public static function getMeetingsForProject($projectId, $untilNow = false)
    {

        $objPDO = PDOFactory::get();

        # Find out whether we want only future meetings
        if($untilNow)
            $untilNowDatetime = " AND datetime < '" . date("Y-m-d H:i:s") . "'";
        else
            $untilNowDatetime = "";

        # Get all meetings associated with the $projectId from a database
        $strQuery = "SELECT id FROM Meeting WHERE project_id = :project_id" . $untilNowDatetime . " ORDER BY datetime DESC";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all meetings associated with the $projectId to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
        {
            foreach($result as $row)
            {
                $myArr[$row["id"]] = new Meeting($row["id"]);
            }
        }

        return $myArr;
    }

    public static function getNextMeeting()
    {
        # Get database connection
        $objPDO = PDOFactory::get();

        # Get project ID from session
        $projectId = HTTPSession::getInstance()->PROJECT_ID;

        # Get the next meeting
        $strQuery = "SELECT id FROM Meeting WHERE project_id = :project_id AND datetime > NOW() AND is_deleted = 0 LIMIT 1";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->execute();


        # Define empty variable
        $nextMeeting = null;

        # Add all notes to an array
        if($row = $objStatement->fetch(PDO::FETCH_ASSOC))
        {
            $nextMeeting = new Meeting($row['id']);
        }

        return $nextMeeting;
    }
}