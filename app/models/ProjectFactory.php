<?php

require_once("Project.php");

/**
 * Generates Project objects from records in the database
 */
class ProjectFactory
{
    /**
     * A method to return all projects
     * Not used in this version, however might be useful in future
     * @return array the project objects
     */
    public static function getAllProjects()
    {
        # Get PDO
        $objPDO = PDOFactory::get();

        # Get all projects from db
        $strQuery = "SELECT id FROM Project";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all projects to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
            foreach($result as $row)
                $myArr[$row["id"]] = new Project($row["id"]);

        # Return the project objects
        return $myArr;
    }

    /**
     * A method to return project associated with a provided user id
     * If the user has more projects (supervisor), the first one is returned
     * @param int $id the user id
     * @return null|Project
     */
    public static function getProjectWithUserId($id)
    {
        # Get PDO
        $objPDO = PDOFactory::get();

        # Get the project
        $strQuery = "SELECT project_id FROM UserProject WHERE user_id=:id";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':id',$id,PDO::PARAM_INT);
        $objStatement->execute();

        # If there's any project like this, return it
        if($result = $objStatement->fetch(PDO::FETCH_ASSOC))
            return new Project($result["project_id"]);

        # Otherwise return null
        return null;
    }

    /**
     * A method to return all projects associated with a user ID
     * @param int $userId the user id
     * @return array the project objects
     */
    public static function getAllProjectsForUser($userId)
    {
        # Get PDO
        $objPDO = PDOFactory::get();

        # Get all projects associated with the $userId from a database
        $strQuery = "SELECT DISTINCT project_id FROM UserProject WHERE user_id = :user_id";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':user_id',$userId,PDO::PARAM_INT);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all projects associated with the $userId to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
            foreach($result as $row)
                $myArr[$row["project_id"]] = new Project($row["project_id"]);

        # Return the projects
        return $myArr;
    }

    /**
     * A method to return all users associated with a project
     * @param int  $projectI the project idd
     * @return array the user objects
     */
    public static function getAllUsersForProject($projectId)
    {
        # Get PDO
        $objPDO = PDOFactory::get();

        # Get alluserss associated with the $projectId from a database
        $strQuery = "SELECT user_id FROM UserProject WHERE project_id = :project_id";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id',$projectId,PDO::PARAM_INT);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all users associated with the $projectId to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
            foreach($result as $row)
                $myArr[$row["user_id"]] = new User($row["user_id"]);

        # Return the user objects
        return $myArr;
    }

}