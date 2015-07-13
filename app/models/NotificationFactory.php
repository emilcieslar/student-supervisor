<?php

class NotificationFactory
{
    public static function getNotificationsForProject($projectId)
    {
        $objPDO = PDOFactory::get();

        # Get all action points associated with the $projectId from a database
        $strQuery = "SELECT id FROM Notification WHERE project_id = :project_id ORDER BY datetime_created DESC";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all notifications associated with the $projectId to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
        {
            foreach($result as $row)
            {
                $myArr[$row["id"]] = new Notification($row["id"]);
            }
        }

        return $myArr;

    }
}