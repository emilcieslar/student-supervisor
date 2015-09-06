<?php

class NotificationFactory
{
    /**
     * A method to get notification objects
     * @param int $projectId the project id these notes are associated with
     * @return array the notification objects
     */
    public static function getNotificationsForProject($projectId)
    {
        # Get db connection
        $objPDO = PDOFactory::get();

        # Get all notifications associated with the $projectId from a database
        $strQuery = "SELECT id, object_type FROM Notification WHERE project_id = :project_id AND is_deleted = 0 ORDER BY datetime_created DESC";
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
                # Decide what kind of object should be created
                switch($row["object_type"])
                {
                    case "Action Point": $myArr[$row["id"]] = new NotificationAP(null, null, $row["id"]);
                        break;
                    case "Meeting": $myArr[$row['id']] = new NotificationMeeting(null, null, $row['id']);
                        break;
                    case "Note": $myArr[$row['id']] = new NotificationNote(null, null, $row['id']);
                        break;
                    default: $myArr[$row["id"]] = new Notification(null, null, $row["id"]);
                }

            }
        }

        # Return the array of notifications
        return $myArr;

    }
}