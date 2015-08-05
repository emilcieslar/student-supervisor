<?php

require_once("ActionPoint.php");

class ActionPointFactory
{
    public static function getActionPointsForProject($projectId, $sinceNow = false)
    {
        $objPDO = PDOFactory::get();

        # If it's the supervisor logged in, get only action points that were sent for approval by student
        $sentForApproval = "";
        if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
            $sentForApproval = " AND sent_for_approval = 1";

        # Find out whether we want only future action points
        if($sinceNow)
            $sinceNowDatetime = " AND deadline >= '" . date("Y-m-d H:i:s") . "'";
        else
            $sinceNowDatetime = "";

        # Get all action points associated with the $projectId from a database
        $strQuery = "SELECT id FROM ActionPoint WHERE project_id = :project_id AND is_deleted = 0" . $sinceNowDatetime . $sentForApproval . " ORDER BY datetime_created DESC";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all action points associated with the $projectId to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
        {
            foreach($result as $row)
            {
                $myArr[$row["id"]] = new ActionPoint($row["id"]);
            }
        }

        return $myArr;

    }

    public static function getActionPointsForAgenda()
    {
        # Get database connection
        $objPDO = PDOFactory::get();

        # Get project ID from session
        $projectId = HTTPSession::getInstance()->PROJECT_ID;

        # If it's the supervisor logged in, get only action points that were sent for approval by student
        $sentForApproval = "";
        if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
            $sentForApproval = " AND sent_for_approval = 1";

        # Get action points that have deadline more than the last meeting and less than or equal to the next meeting
        $strQuery = "SELECT id FROM ActionPoint WHERE project_id = :project_id" . $sentForApproval . "
                      AND deadline > (SELECT datetime FROM Meeting WHERE project_id = :project_id AND datetime < NOW() AND is_deleted = 0  ORDER BY datetime DESC LIMIT 1)
                      AND deadline <= (SELECT datetime FROM Meeting WHERE project_id = :project_id AND datetime > NOW() AND is_deleted = 0 LIMIT 1)
                      ORDER BY datetime_created DESC";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all notes to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
        {
            foreach($result as $row)
            {
                $myArr[$row["id"]] = new ActionPoint($row["id"]);
            }
        }

        return $myArr;
    }

    public static function getActionPointsCount($factor)
    {
        # Get database connection
        $objPDO = PDOFactory::get();

        # Get project ID from session
        $projectId = HTTPSession::getInstance()->PROJECT_ID;

        $select = "COUNT(id) AS ap_count";

        # Decide what count to get from DB
        switch($factor)
        {
            case RedAmberGreen::TO_BE_DONE: $factor = " AND is_done = 0";
                break;
            case RedAmberGreen::RUNNING_OVER_DEADLINE: $factor = " AND is_done = 0 AND deadline < NOW()";
                break;
            case RedAmberGreen::FINISHED: $factor = " AND is_done = 1";
                break;
            case RedAmberGreen::FINISHED_AFTER_DEADLINE: $factor = " AND is_done = 1 AND deadline < datetime_done";
                break;
            case RedAmberGreen::AVG_GRADE: $factor = " AND is_done = 1 AND grade <> 0"; $select = "TRUNCATE(AVG(grade),1) AS ap_count";
                break;
            default: $factor = "";
        }

        # Get number of action points that are not finished yet and are approved
        $strQuery = "SELECT ".$select." FROM ActionPoint WHERE project_id = :project_id AND is_approved = 1".$factor." AND is_deleted = 0";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->execute();

        # Return the value
        $result = $objStatement->fetch()['ap_count'];

        if($result)
            return $result;
        else
            return 0;
    }


}