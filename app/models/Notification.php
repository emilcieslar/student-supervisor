<?php

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

    public function getUsername()
    {
        require_once("User.php");
        $user = new User($this->CreatorUserId);
        return $user->getUsername();
    }

    public function getObject($temp = false)
    {
        # Get object type without spaces
        $objectType = str_replace(' ', '', $this->ObjectType);
        require_once($objectType.".php");
        $object = new $objectType($this->ObjectId, $temp);
        return $object;
    }
}