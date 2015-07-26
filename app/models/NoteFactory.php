<?php

require_once("Note.php");

class NoteFactory
{
    public static function getNotes($meeting = null)
    {
        # Get database connection
        $objPDO = PDOFactory::get();

        # Get project ID from session
        $projectId = HTTPSession::getInstance()->PROJECT_ID;

        # Get user ID from session
        $userID = HTTPSession::getInstance()->getUserId();

        # If notes for specific meeting are requested
        if($meeting)
            $meeting = " AND meeting_id = " . $meeting;
        else
            $meeting = "";

        # Get all notes associated with a given project with the following condition:
        # – Apart from notes that are private AND associated with a different user than logged in
        $strQuery = "SELECT id FROM Note WHERE project_id = :project_id AND NOT (user_id != :user_id AND is_private = 1) AND is_deleted = 0 " . $meeting . " ORDER BY datetime_created DESC";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->bindValue(':user_id', $userID, PDO::PARAM_INT);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all notes to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
        {
            foreach($result as $row)
            {
                $myArr[$row["id"]] = new Note($row["id"]);
            }
        }

        return $myArr;
    }
}