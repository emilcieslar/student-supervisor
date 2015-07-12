<?php

class Notification extends DataBoundObject
{
    protected $DatetimeCreated;
    protected $Controller;
    protected $ObjectId;
    protected $Action;
    protected $ProjectId;
    protected $ReasonForAction;
    protected $CreatorUserId;

    protected function DefineTableName()
    {
        return "Notification";
    }

    protected function DefineRelationMap()
    {
        return(array(
            "id" => "ID",
            "name" => "Name",
            "datetime_created" => "DatetimeCreated",
            "description" => "Description"
        ));
    }
}