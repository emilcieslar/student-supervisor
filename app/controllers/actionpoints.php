<?php

/**
 * Controls all the functionality associated with Action Points
 */
class ActionPoints extends Controller
{
    /**
     * Default view that displays list of APs with one specific AP selected
     * @param null $id int the ID of the AP (if we want to display specific AP)
     * @param null $delete boolean defines whether to display a revert removal message
     */
    public function index($id = null, $delete = null)
    {
        # Get action points from DB
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

        # If we have nothing to display (there are no APs in DB) we instead call add()
        if(!$id)
        {
            $this->add();
            die();
        }

        # Check access rights for user
        $this->checkAuth($id, true, false);

        # Get meeting associated with action point
        $meeting = $this->model('Meeting',$id->getMeetingId());

        # Set values that will be passed to a view
        $data['actionpoints'] = $actionPoints;
        $data['id'] = $id;
        $data['meeting'] = $meeting;

        # If delete is set
        if($delete)
            $data['delete'] = $id->getID();

        # Display the view
        $this->view('actionpoints/index', $data);
    }

    /**
     * Displays a list of action points and a form to Add AP
     */
    public function add()
    {
        # Get action points from DB
        $actionPoints = $this->model('ActionPointFactory');
        $actionPoints = $actionPoints->getActionPointsForProject(HTTPSession::getInstance()->PROJECT_ID);

        # Get meetings for the add form
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true);

        # Display the view
        $this->view('actionpoints/index', ['actionpoints'=>$actionPoints, 'meetings'=>$meetings, 'add'=>true]);
    }


    public function addPost($post = null)
    {
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

            # If it's the supervisor who adds the action point,
            # it is automatically approved and student don't have to send it for approval
            if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
            {
                $actionPoint->setSentForApproval(1);
                $actionPoint->setIsApproved(1);
            }

            # Save the action point
            $actionPoint->Save();

            # Create a notification only if it's supervisor who removes
            if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
                new NotificationAP($actionPoint->getID(),NotificationAP::ADDED);

            # Redirect back to action points
            Header('Location: ' . SITE_URL . 'actionpoints/' . $actionPoint->getID());
            die();
        }

        # Redirect back to action points
        Header('Location: ' . SITE_URL . 'actionpoints');
    }

    public function remove($id)
    {
        # Retrieve action point from database based on provided id
        $actionPoint = $this->model('ActionPoint',$id);

        # Check access rights for user
        $this->checkAuth($actionPoint);

        # Action point will never actually be removed from database, we will keep it
        # for reference, it is only flagged as removed
        $actionPoint->Delete();

        # Create a notification only if it's supervisor who removes
        if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
            new NotificationAP($id,NotificationAP::REMOVED);

        # Save the existing actionPoint to the temporary table
        # in order to retrieve it if not approved by supervisor
        # Delete operation will appear in the supervisor's notification
        # and supervisor and cancel the action if necessary
        /*$actionPoint->SaveTemporary();

        $actionPoint->MarkForDeletion();

        # Create a new notification
        $notif = new Notification();
        $notif->setController("actionpoints");
        $notif->setObjectType("Action Point");
        $notif->setObjectId($actionPoint->getID());
        $notif->setAction(Notification::DELETE);
        $notif->setProjectId(HTTPSession::getInstance()->PROJECT_ID);
        $notif->setCreatorUserId(HTTPSession::getInstance()->GetUserID());
        # Save notification
        $notif->Save();

        */

        # Redirect back to action points
        Header('Location: ' . SITE_URL . 'actionpoints/' . $id . '/deleted');
    }

    public function edit($id)
    {
        $actionPoints = $this->model('ActionPointFactory');
        $actionPoints = $actionPoints->getActionPointsForProject(HTTPSession::getInstance()->PROJECT_ID);

        # Create Action Point from provided ID
        $id = new ActionPoint($id);

        # Check access rights for user
        $this->checkAuth($id);

        # Set correct format of provided date
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $id->getDeadline());
        $data['date'] = $date->format('d-m-Y');
        $data['hours'] = $date->format('H');
        $data['minutes'] = $date->format('i');

        # Get meetings for the add form
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true);

        $this->view('actionpoints/index', ['actionpoints'=>$actionPoints, 'id'=>$id, 'edit'=>true, 'datetime'=>$data, 'meetings'=>$meetings]);
    }

    public function editPost($post)
    {
        # Retrieve action point from database based on provided id
        $actionPoint = $this->model('ActionPoint', $post['id']);

        # Check access rights for user
        $this->checkAuth($actionPoint);

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

        # If it's the supervisor who edits, he/she can provide a grade
        $grade = 0;
        if(isset($post['grade']))
            $grade = $post['grade'];

        # Default is 0 because default is student and every time student
        # edits action point, isApproved must be set to 0
        $isApproved = 0;
        # If it's a supervisor who edits the AP, isApproved is automatically true with any edit
        if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
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
        if($grade)
            $actionPoint->setGrade($grade);

        $actionPoint->setIsApproved($isApproved);
        # If the action point is approved, we must delete the temporary one from temp table
        if($isApproved)
            $actionPoint->RemoveTemporary();

        # Save the action point
        $actionPoint->Save();

        # Redirect back to action points
        header('Location: ' . SITE_URL . 'actionpoints/' . $post['id']);
        die();
    }

    public function approve($id)
    {
        # Only supervisor can approve, no one else has access to this page
        if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
        {
            # Retrieve action point from database based on provided id
            $actionPoint = $this->model('ActionPoint',$id);

            # Check action point project scope access for a supervisor
            $this->checkAuth($actionPoint, false, false);

            # Approve the action point
            $actionPoint->setIsApproved(1);

            # Save the action point
            $actionPoint->Save();

            # Create a notification
            new NotificationAP($id,NotificationAP::APPROVED);
        }

        # Redirect back to action points
        Header('Location: ' . SITE_URL . 'actionpoints/' . $id);
    }

    public function done($id)
    {
        # Retrieve action point from database based on provided id
        $actionPoint = $this->model('ActionPoint',$id);

        # Check user access
        $this->checkAuth($actionPoint, false, false);

        # Check if action point has been approved
        $this->checkAuthApproved($actionPoint->getIsApproved());

        # isApproved default is 0 because default is student and every time student
        # sets the AP as done, isApproved must be set to 0
        $isApproved = 0;
        # If it's a supervisor who sets the AP as done, isApproved is automatically true
        if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
        {
            $isApproved = 1;
        }
        $actionPoint->setIsApproved($isApproved);

        # Set done to the action point
        $actionPoint->setIsDone(1);

        # Set time that the action point has been done
        $now = new DateTime('NOW');
        $now = $now->format('Y-m-d H:i:s');
        $actionPoint->setDatetimeDone($now);

        # Save the action point
        $actionPoint->Save();

        # Create a notification (it's automatically saved)
        new NotificationAP($id,NotificationAP::DONE);

        # Redirect back to action points
        Header('Location: ' . SITE_URL . 'actionpoints/' . $id);
    }

    public function send($id)
    {
        # Retrieve action point from database based on provided id
        $actionPoint = $this->model('ActionPoint',$id);

        # Check user access
        $this->checkAuth($actionPoint, true, false);

        # Set done to the action point
        $actionPoint->setSentForApproval(1);

        # Save the action point
        $actionPoint->Save();

        # Create a notification
        new NotificationAP($id,NotificationAP::SENT_FOR_APPROVAL);

        # Redirect back to action points
        Header('Location: ' . SITE_URL . 'actionpoints/' . $id);
    }

    public function revertRemoval($id)
    {
        $actionPoint = $this->model('ActionPoint',$id);

        # Check if user is allowed to perform this action
        $this->checkAuth($actionPoint);

        $actionPoint->setIsDeleted(0);

        $actionPoint->Save();

        # Redirect back to actionpoints
        header('Location: ' . SITE_URL . 'actionpoints');
    }

    protected function checkAuthSentForApproval($actionPointSentForApproval)
    {
        # If the action point hasn't been sent for approval and logged in user is supervisor, the supervisor
        # is not able to access it
        if(!$actionPointSentForApproval && HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
        {
            header('Location: ' . SITE_URL . 'actionpoints');
            # Do not execute code any longer
            die();
        } else
            return true;
    }

    protected function checkAuthStudentAfterApproval($actionPointSentForApproval)
    {
        # If it has been sent for approval and a user is a student, then the student
        # is not able to access it
        if($actionPointSentForApproval && HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_STUDENT)
        {
            header('Location: ' . SITE_URL . 'actionpoints');
            # Do not execute code any longer
            die();
        } else
            return true;
    }

    protected function checkAuthApproved($actionPointApproved)
    {
        # If an action point hasn't been approved yet, no access (return back to action points)
        if(!$actionPointApproved)
        {
            header('Location: ' . SITE_URL . 'actionpoints');
            # Do not execute code any longer
            die();
        }
    }

    protected function checkAuth($actionPoint = null, $supervisor = true, $student = true, $project = true)
    {
        if($supervisor)
            # Check if a supervisor has access
            $this->checkAuthSentForApproval($actionPoint->getSentForApproval());

        if($student)
            # Check if a student has access
            $this->checkAuthStudentAfterApproval($actionPoint->getSentForApproval());

        if($project)
            # Check if it's within the scope of the project
            $this->checkAuthProjectScope($actionPoint->getProjectId());
    }

}