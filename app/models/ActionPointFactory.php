<?php

require_once("ActionPoint.php");

class ActionPointFactory
{
    public static function getActionPointsForProject($projectId, $sinceNow = false)
    {
        $objPDO = PDOFactory::get();

        # Find out whether we want only future meetings
        if($sinceNow)
            $sinceNowDatetime = " AND deadline >= '" . date("Y-m-d H:i:s") . "'";
        else
            $sinceNowDatetime = "";

        # Get all action points associated with the $projectId from a database
        $strQuery = "SELECT id FROM ActionPoint WHERE project_id = :project_id" . $sinceNowDatetime . " ORDER BY datetime_created";
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