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
        $notif->Delete();

        header('Location: '.SITE_URL.'notifications');
    }
}