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
}