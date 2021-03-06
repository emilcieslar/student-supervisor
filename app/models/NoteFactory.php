<?php

require_once("Note.php");

/**
 * Generates Note objects from records in the database
 */
class NoteFactory
{
    /**
     * A method to return note objects from database
     * @param null $meeting filter by a specific meeting
     * @param bool $agenda if it should be agenda notes returned
     * @return array the note objects
     */
    public static function getNotes($meeting = null, $agenda = false)
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

        # If notes for agenda are requested
        if($agenda)
            $agenda = " AND is_agenda = 1";
        # Otherwise only notes that are not agenda should be returned
        else
            $agenda = " AND is_agenda = 0";

        # Get all notes associated with a given project with the following condition:
        # – Apart from notes that are private AND associated with a different user than logged in
        $strQuery = "SELECT id FROM Note WHERE project_id = :project_id AND NOT (user_id != :user_id AND is_private = 1) AND is_deleted = 0 " . $meeting . $agenda . " ORDER BY datetime_created DESC";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $objStatement->bindValue(':user_id', $userID, PDO::PARAM_INT);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all notes to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
            foreach($result as $row)
                $myArr[$row["id"]] = new Note($row["id"]);

        # Return the note objects
        return $myArr;
    }
}