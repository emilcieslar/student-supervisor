<?php

/**
 * Holds data associated with Notification entity
 */
class Notification extends DataBoundObject
{
    const ADD = "added";
    const EDIT = "modified";
    const DELETE = "deleted";

    protected $DatetimeCreated;
    protected $IsDone;
    protected $Controller;
    protected $ObjectType;
    protected $ObjectId;
    protected $Action;
    protected $ProjectId;
    protected $ReasonForAction;
    protected $CreatorUserId;

/*    public function __construct($datetimeCreated = null, $id = null)
    {
        # Call the super class
        parent::__construct($id);


    }*/

    protected function DefineTableName()
    {
        return "Notification";
    }

    protected function DefineRelationMap()
    {
        return(array(
            "id" => "ID",
            "datetime_created" => "DatetimeCreated",
            "is_done" => "IsDone",
            "controller" => "Controller",
            "object_type" => "ObjectType",
            "object_id" => "ObjectId",
            "action" => "Action",
            "project_id" => "ProjectId",
            "reason_for_action" => "ReasonForAction",
            "creator_user_id" => "CreatorUserId",
            "is_deleted" => "IsDeleted"
        ));
    }

    /**
     * Get username of the creator of the notification
     * @return mixed
     */
    public function getUsername()
    {
        require_once("User.php");
        # Get the user object
        $user = new User($this->CreatorUserId);
        # Return hers/his username
        return $user->getUsername();
    }

    /**
     * A method to return an object based on $ObjectType
     * Description:
     * $ObjectType variable holds objects such as Action Point or Meeting,
     * where in case of Meeting it's fine, however Action Point has a space
     * so in order to retrieve ActionPoint object, we have to remove that space.
     * This method facilitates removal of such space and returns the object so it can
     * be further handled (for example to get some data from it to display in notification)
     * @param bool $temp if it should be retrieved from temporary table (for example action point, we want to see how it was modified)
     * @return mixed the object (Meeting, ActionPoint, ...)
     */
    public function getObject($temp = false)
    {
        # Get object type without spaces
        $objectType = str_replace(' ', '', $this->ObjectType);
        require_once($objectType.".php");
        # Create the object
        $object = new $objectType($this->ObjectId, $temp);
        # And return it
        return $object;
    }
}