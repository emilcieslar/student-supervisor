<?php

class Notifications extends Controller
{
    public function index()
    {
        $notifications = $this->model('NotificationFactory');
        $notifications = $notifications->getNotificationsForProject(HTTPSession::getInstance()->PROJECT_ID);

        $this->view('notifications/index', ['notifications'=>$notifications]);
    }
}