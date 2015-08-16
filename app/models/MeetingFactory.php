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
        $strQuery = "SELECT id FROM Meeting WHERE project_id = :project_id" . $untilNowDatetime . " AND is_deleted = 0 ORDER BY datetime DESC";
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

        # Get the next meeting, which we can recognize by
        # 1. datetime of that meeting is bigger than now
        # 2. it's the first next meeting therefore LIMIT 1
        # 3. it hasn't been cancelled
        # 4. it is approved
        $strQuery = "SELECT id FROM Meeting WHERE project_id = :project_id
                      AND datetime > NOW()
                      AND is_deleted = 0
                      AND is_cancelled = 0
                      AND is_approved
                      LIMIT 1";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->execute();


        # Define empty variable
        $nextMeeting = null;

        # Get the next meeting
        if($row = $objStatement->fetch(PDO::FETCH_ASSOC))
        {
            $nextMeeting = new Meeting($row['id']);
        }

        return $nextMeeting;
    }

    public static function getPreviousMeeting($meeting)
    {
        # Get database connection
        $objPDO = PDOFactory::get();

        # Get project ID from session
        $projectId = HTTPSession::getInstance()->PROJECT_ID;

        # Get the previous meeting, which we can recognize by
        # 1. datetime of that meeting is less than meeting provided
        # 2. Meeting is not deleted, however can be cancelled, because we might look for a meeting that was cancelled
        # 3. Meeting is approved
        $strQuery = "SELECT id FROM Meeting WHERE project_id = :project_id
                      AND datetime < (SELECT datetime FROM Meeting WHERE id = ".$meeting->getID().")
                      AND is_deleted = 0
                      AND is_approved
                      ORDER BY datetime DESC
                      LIMIT 1";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->execute();


        # Define empty variable
        $prevMeeting = null;

        # Get the previous meeting if it exists
        if($row = $objStatement->fetch(PDO::FETCH_ASSOC))
        {
            $prevMeeting = new Meeting($row['id']);
        }

        return $prevMeeting;
    }

    public static function getMeetingsCount($factor)
    {
        # Get database connection
        $objPDO = PDOFactory::get();

        # Get project ID from session
        $projectId = HTTPSession::getInstance()->PROJECT_ID;

        $select = "COUNT(id) AS m_count";

        # Decide what count to get from DB
        switch($factor)
        {
            case RedAmberGreen::TAKEN_PLACE: $factor = " AND taken_place = 1";
                break;
            case RedAmberGreen::STUDENT_ARRIVED_ON_TIME: $factor = " AND taken_place = 1 AND arrived_on_time = 1";
                break;
            case RedAmberGreen::CANCELLED: $factor = " AND is_cancelled = 1";
                break;
            case RedAmberGreen::NO_SHOW: $factor = " AND datetime < NOW() AND taken_place = 0 AND is_cancelled = 0";
                break;
            # Cancelled can be in the future and we want to include it in the total
            case RedAmberGreen::M_TOTAL: $factor = " AND (datetime < NOW() OR is_cancelled = 1)";
                break;
            default: $factor = "";
        }

        # Get number of action points that are not finished yet and are approved
        $strQuery = "SELECT ".$select." FROM Meeting WHERE project_id = :project_id AND is_approved = 1".$factor." AND is_deleted = 0";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->execute();

        # Return the value
        return $objStatement->fetch()['m_count'];
    }
}