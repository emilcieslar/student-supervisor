<?php

class NotificationNote extends Notification
{
    const ADDED = 1;

    public function __construct($objId = null, $action = null, $NotificationId = null, $autoSave = true)
    {
        # We call super, because there are some essential steps that need to be performed
        # before we start (also this is used when retrieving an existing Object from DB)
        parent::__construct($NotificationId);

        # If we want to create a new Notification
        if($objId)
        {
            $this->Controller = "notes";
            $this->CreatorUserId = HTTPSession::getInstance()->GetUserID();
            $this->ObjectId = $objId;
            $this->ObjectType = "Note";
            $this->ProjectId = HTTPSession::getInstance()->PROJECT_ID;
            $this->Action = $action;

            $this->arModifiedRelations['Controller'] = "1";
            $this->arModifiedRelations['CreatorUserId'] = "1";
            $this->arModifiedRelations['ObjectId'] = "1";
            $this->arModifiedRelations['ObjectType'] = "1";
            $this->arModifiedRelations['ProjectId'] = "1";
            $this->arModifiedRelations['Action'] = "1";

            if($autoSave)
                $this->Save();
        }
    }

    public function edited()
    {

    }

    public static function getActionText($action)
    {
        $actionText[0] = "";
        $actionText[1] = "added";

        return $actionText[$action];
    }
}