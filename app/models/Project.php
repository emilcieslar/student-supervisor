<?php

class Project extends DataBoundObject
{
    protected $Name;
    protected $DatetimeCreated;
    protected $Description;

    protected function DefineTableName()
    {
        return("Project");
    }

    protected function DefineRelationMap()
    {
        return(array(
            "id" => "ID",
            "name" => "Name",
            "datetime_created" => "DatetimeCreated",
            "description" => "Description",
            "is_deleted" => "IsDeleted"
        ));
    }

}
