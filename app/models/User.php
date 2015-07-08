<?php

class User extends DataBoundObject
{
    protected $Username;
    protected $Password;
    protected $FirstName;
    protected $LastName;
    protected $Type;

    const USER_TYPE_STUDENT = 0;
    const USER_TYPE_SUPERVISOR = 1;
    const USER_TYPE_ADMIN = 2;

    protected function DefineTableName()
    {
        return("User");
    }

    protected function DefineRelationMap()
    {
        return(array(
            "id" => "ID",
            "username" => "Username",
            "password" => "Password",
            "first_name" => "FirstName",
            "last_name" => "LastName",
            "type" => "Type"
        ));
    }

    public function getProjectId()
    {
        # Get Project object and its ID
        return ProjectFactory::getProjectWithUserId($this->ID)->getID();
    }

}
