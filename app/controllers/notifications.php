<?php

class Notifications extends Controller
{
    public function index()
    {
        $notifications = $this->model('NotificationFactory');
        $notifications = $notifications->getNotificationsForProject(HTTPSession::getInstance()->PROJECT_ID);

        $this->view('notifications/index', ['notifications'=>$notifications]);
    }

    public function done($id)
    {
        $notif = new Notification($id);

        # Check access rights
        $this->checkAuthProjectScope($notif->getProjectId());
        $this->checkAuthCreatedByStudent($notif);

        $notif->Delete();

        header('Location: '.SITE_URL.'notifications');
    }

    private function checkAuthCreatedByStudent(Notification $notif)
    {
        # If it's the student who created the notification, only a supervisor can
        # remove it from the list (it is necessary for the supervisor to see every student action)
        $creatorUserType = $this->model('User',$notif->getCreatorUserId())->getType();
        if($creatorUserType == User::USER_TYPE_STUDENT && HTTPSession::getInstance()->USER_TYPE != User::USER_TYPE_SUPERVISOR)
            header('Location: '.SITE_URL.'notifications');
    }
}