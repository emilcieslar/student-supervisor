<?php

class ActionPoints extends Controller
{
    public function index($id = null)
    {
        $actionPoints = $this->model('ActionPointFactory');
        $actionPoints = $actionPoints->getActionPointsForProject(HTTPSession::getInstance()->PROJECT_ID);

        # Is there a specific ID to display or display default?
        if($id)
            # Create Action Point from provided ID
            $id = new ActionPoint($id);
        # Get default
        else
            # Get the first item from the array
            $id = reset($actionPoints);

        # Get meeting associated with action point
        $meeting = $this->model('Meeting',$id->getMeetingId());

        $this->view('actionpoints/index', ['actionpoints'=>$actionPoints, 'id'=>$id, 'meeting'=>$meeting]);
    }

    public function add($post = null)
    {
        # If it's a post request, we'll have parameters passed
        if($post)
        {
            # Create an empty action point
            $actionPoint = $this->model('ActionPoint');

            # Get details from post request
            $deadline = $post['deadline'];
            $deadline_time_hours = $post['deadline_time_hours'];
            $deadline_time_minutes = $post['deadline_time_minutes'];
            $text = $post['text'];
            $meetingId = $post['meetingId'];

            # Set correct format of provided date
            $date = DateTime::createFromFormat('d-m-Y H:i', $deadline . " " . $deadline_time_hours . ":" . $deadline_time_minutes);
            $date = $date->format('Y-m-d H:i:s');

            # Set provided information
            $actionPoint->setDeadline($date);
            $actionPoint->setText($text);
            $actionPoint->setMeetingId($meetingId);
            $actionPoint->setProjectId(HTTPSession::getInstance()->PROJECT_ID);

            # Save the action point
            $actionPoint->Save();

            # Create a new notification
            $notif = new Notification();
            $notif->setController("actionpoints");
            $notif->setObjectType("Action Point");
            $notif->setObjectId($actionPoint->getID());
            $notif->setAction(Notification::ADD);
            $notif->setProjectId(HTTPSession::getInstance()->PROJECT_ID);
            $notif->setCreatorUserId(HTTPSession::getInstance()->GetUserID());
            # Save notification
            $notif->Save();

            # Redirect back to action points
            Header('Location: ' . SITE_URL . 'actionpoints');
        }
        # If it's a get request
        else
        {
            # Get action points for the list
            $actionPoints = $this->model('ActionPointFactory');
            $actionPoints = $actionPoints->getActionPointsForProject(HTTPSession::getInstance()->PROJECT_ID, true);

            # Get meetings for the add form
            $meetings = $this->model('MeetingFactory');
            $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, false);

            $this->view('actionpoints/index', ['actionpoints'=>$actionPoints, 'meetings'=>$meetings, 'add'=>true]);
        }
    }

    public function remove($id)
    {
        # Retrieve action point from database based on provided id
        $actionPoint = $this->model('ActionPoint',$id);

        # Save the existing actionPoint to the temporary table
        # in order to retrieve it if not approved by supervisor
        # Delete operation will appear in the supervisor's notification
        # and supervisor and cancel the action if necessary
        $actionPoint->SaveTemporary();

        $actionPoint->MarkForDeletion();

        # Redirect back to action points
        Header('Location: ' . SITE_URL . 'actionpoints');
    }

    public function edit($id)
    {
        $actionPoints = $this->model('ActionPointFactory');
        $actionPoints = $actionPoints->getActionPointsForProject(HTTPSession::getInstance()->PROJECT_ID, true);

        # Create Action Point from provided ID
        $id = new ActionPoint($id);

        # Set correct format of provided date
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $id->getDeadline());
        $data['date'] = $date->format('d-m-Y');
        $data['hours'] = $date->format('H');
        $data['minutes'] = $date->format('i');

        # Get meetings for the add form
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(1, false);

        $this->view('actionpoints/index', ['actionpoints'=>$actionPoints, 'id'=>$id, 'edit'=>true, 'datetime'=>$data, 'meetings'=>$meetings]);
    }

    public function editPost($post)
    {
        # Retrieve action point from database based on provided id
        $actionPoint = $this->model('ActionPoint', $post['id']);

        # Save the existing actionPoint to the temporary table
        # in order to retrieve it if not approved by supervisor
        $actionPoint->SaveTemporary();

        # Get details from post request
        $deadline = $post['deadline'];
        $deadline_time_hours = $post['deadline_time_hours'];
        $deadline_time_minutes = $post['deadline_time_minutes'];
        $text = $post['text'];
        $meetingId = $post['meetingId'];
        $isDone = 0;
        # If the action point is done
        if(isset($post['isDone']))
            $isDone = 1;

        # Default is 0 because default is student and every time student
        # edits action point, isApproved must be set to 0
        $isApproved = 0;
        # If it's a supervisor who edits the post, isApproved is passed as well
        if(isset($post['isApproved']))
        {
            $isApproved = 1;
        }

        # Set correct format of provided date
        $date = DateTime::createFromFormat('d-m-Y H:i', $deadline . " " . $deadline_time_hours . ":" . $deadline_time_minutes);
        $date = $date->format('Y-m-d H:i:s');

        # Set provided information
        $actionPoint->setDeadline($date);
        $actionPoint->setText($text);
        $actionPoint->setMeetingId($meetingId);
        $actionPoint->setProjectId(HTTPSession::getInstance()->PROJECT_ID);
        $actionPoint->setIsDone($isDone);

        $actionPoint->setIsApproved($isApproved);
        # If the action point is approved, we must delete the temporary one from temp table
        if($isApproved)
            $actionPoint->RemoveTemporary();

        # Save the action point
        $actionPoint->Save();

        # Redirect back to action points
        Header('Location: ' . SITE_URL . 'actionpoints');
    }
}