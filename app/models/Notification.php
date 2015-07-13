<?php

class Notification extends DataBoundObject
{
    const ADD = "added";

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
            "creator_user_id" => "CreatorUserId"
        ));
    }
}