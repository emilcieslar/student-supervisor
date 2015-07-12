<?php

require_once("Project.php");

class ProjectFactory
{
    public static function getAllProjects($projectId, $sinceNow = false)
    {
        # Get PDO
        $objPDO = PDOFactory::get();

        # Find out whether we want only future meetings
        if($sinceNow)
            $sinceNowDatetime = " WHERE datetime_created >= '" . date("Y-m-d H:i:s") . "'";
        else
            $sinceNowDatetime = "";

        # Get all meetings associated with the $projectId from a database
        $strQuery = "SELECT id FROM Project" . $sinceNowDatetime;
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all meetings associated with the $projectId to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
        {
            foreach($result as $row)
            {
                $myArr[$row["id"]] = new Project($row["id"]);
            }
        }

        return $myArr;
    }

    public static function getProjectWithUserId($id)
    {
        # Get PDO
        $objPDO = PDOFactory::get();

        $strQuery = "SELECT project_id FROM UserProject WHERE user_id=:id";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':id',$id,PDO::PARAM_INT);
        $objStatement->execute();

        if($result = $objStatement->fetch(PDO::FETCH_ASSOC))
        {
            return new Project($result["project_id"]);
        }

        return null;
    }

    public static function getAllProjectsForUser($userId)
    {
        # Get PDO
        $objPDO = PDOFactory::get();

        # Get all meetings associated with the $projectId from a database
        $strQuery = "SELECT DISTINCT project_id FROM UserProject WHERE user_id = :user_id";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':user_id',$userId,PDO::PARAM_INT);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all meetings associated with the $projectId to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
        {
            foreach($result as $row)
            {
                $myArr[$row["project_id"]] = new Project($row["project_id"]);
            }
        }

        return $myArr;
    }

    public static function getAllUsersForProject($projectId)
    {
        # Get PDO
        $objPDO = PDOFactory::get();

        # Get all meetings associated with the $projectId from a database
        $strQuery = "SELECT user_id FROM UserProject WHERE project_id = :project_id";
        $objStatement = $objPDO->prepare($strQuery);
        $objStatement->bindValue(':project_id',$projectId,PDO::PARAM_INT);
        $objStatement->execute();

        # Define empty array
        $myArr = array();

        # Add all users associated with the $projectId to an array
        if($result = $objStatement->fetchAll(PDO::FETCH_ASSOC))
        {
            foreach($result as $row)
            {
                $myArr[$row["user_id"]] = new User($row["user_id"]);
            }
        }

        return $myArr;
    }

}