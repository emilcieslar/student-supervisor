<?php

/**
 * Holds data associated with Notifications focused on Action Points
 */
class NotificationAP extends Notification
{
    const DONE = 1;
    const SENT_FOR_APPROVAL = 2;
    const REMOVED = 3;
    const APPROVED = 4;
    const AMENDED = 5;
    const ADDED = 6;

    public function __construct($APId = null, $action = null, $NotificationId = null, $autoSave = true)
    {
        # We call super, because there are some essential steps that need to be performed
        # before we start (also this is used when retrieving an existing AP from DB)
        parent::__construct($NotificationId);

        # If we want to create a new AP Notification
        if($APId)
        {
            $this->Controller = "actionpoints";
            $this->CreatorUserId = HTTPSession::getInstance()->GetUserID();
            $this->ObjectId = $APId;
            $this->ObjectType = "Action Point";
            $this->ProjectId = HTTPSession::getInstance()->PROJECT_ID;
            $this->Action = $action;

            # We have just set the values above, however unless we use set methods, DataBoundObject
            # won't recognize these as modified, therefore we have to add them to modified relations table
            $this->arModifiedRelations['Controller'] = "1";
            $this->arModifiedRelations['CreatorUserId'] = "1";
            $this->arModifiedRelations['ObjectId'] = "1";
            $this->arModifiedRelations['ObjectType'] = "1";
            $this->arModifiedRelations['ProjectId'] = "1";
            $this->arModifiedRelations['Action'] = "1";

            # Save the notification
            if($autoSave)
                $this->Save();
        }
    }

    /**
     * A method to return text representation of different action constants
     * @param int $action the action point constant
     * @return string the text representation
     */
    public static function getActionText($action)
    {
        $actionText[0] = "";
        $actionText[1] = "set as done";
        $actionText[2] = "sent for approval";
        $actionText[3] = "removed";
        $actionText[4] = "approved";
        $actionText[5] = "amended";
        $actionText[6] = "added";

        return $actionText[$action];
    }
}