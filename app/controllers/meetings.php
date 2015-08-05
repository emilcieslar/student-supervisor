<?php

class Meetings extends Controller
{
    /**
     * @param null $id
     * @param null $month
     * @param null $year
     */
    public function index($id = null, $delete = null)
    {
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID);

        # Is there a specific ID to display or display default?
        if($id)
            # Create Meeting from provided ID
            $id = new Meeting($id);
        # Get default
        else
            # Get the first item from the array
            $id = reset($meetings);

        # If we have nothing to display we instead call add()
        if(!$id)
        {
            $this->add();
            die();
        } else
            # Check access rights in a project scope
            $this->checkAuthProjectScope($id->getProjectId());

        # Set values
        $data['meetings'] = $meetings;
        $data['id'] = $id;

        # If delete is set
        if($delete)
            $data['delete'] = $id->getID();

        $this->view('meetings/index', $data);
        #$this->view('meetings/index', ['month'=>$month, 'year'=>$year]);
    }

    public function add()
    {
        # Get meetings to display them
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID);

        $this->view('meetings/index', ['meetings'=>$meetings, 'add'=>true]);
    }

    public function addPost($post = null)
    {
        # Define default header, if it's not repeating meeting, we'll change it afterwards
        # in order to be redirected to a meeting we've created
        $header = 'Location: ' . SITE_URL . 'meetings';

        # If it's a post request, we'll have parameters passed
        if($post)
        {
            # Create an empty meeting
            $meeting = $this->model('Meeting');

            # Get details from post request
            $deadline = $post['deadline'];
            $deadline_time_hours = $post['deadline_time_hours'];
            $deadline_time_minutes = $post['deadline_time_minutes'];

            # Default for is repeating is 0
            $isRepeating = 0;
            # Default time for repeat until is nothing
            $repeatUntil = 0;
            $dateTimeRepeatUntil = null;
            # If isRepeating was checked
            if(isset($post['isRepeating']))
            {
                $isRepeating = 1;
                # Get date from repeatUntil input field
                $repeatUntil = $post['repeatUntil'];
                # Convert date into DB friendly format and keep $dateTimeRepeatUntil object so we can use it
                # in order to create more meetings until this date (because it's a repeated meeting)
                $dateTimeRepeatUntil = DateTime::createFromFormat('d-m-Y H:i', $repeatUntil . " 23:59");
                # $repeatUntil will contain DB friendly format, whereas $dateTimeRepeatUntil will contain
                # object that we can manipulate with it further
                $repeatUntil = $dateTimeRepeatUntil->format('Y-m-d H:i:s');
            }

            $isApproved = 0;
            if(isset($post['isApproved']))
                $isApproved = 1;

            $arrivedOnTime = 0;
            if(isset($post['arrivedOnTime']))
                $arrivedOnTime = 1;

            $takenPlace = 0;
            if(isset($post['takenPlace']))
                $takenPlace = 1;

            # If we are logged in as a supervisor, isApproved option is automatically set to true because
            # supervisor adds approved meetings (only student adds meetings that need approval)
            if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
                $isApproved = 1;

            # Set correct format of provided date
            $dateTime = DateTime::createFromFormat('d-m-Y H:i', $deadline . " " . $deadline_time_hours . ":" . $deadline_time_minutes);
            $date = $dateTime->format('Y-m-d H:i:s');

            # Set provided information
            $meeting->setDatetime($date);

            $meeting->setIsRepeating($isRepeating);
            $meeting->setRepeatUntil($repeatUntil);

            $meeting->setIsApproved($isApproved);
            $meeting->setArrivedOnTime($arrivedOnTime);
            $meeting->setTakenPlace($takenPlace);

            $meeting->setProjectId(HTTPSession::getInstance()->PROJECT_ID);

            # Set googleEventId to null as default
            $googleEventId = null;

            # Check if meeting is approved by a supervisor and user is logged in as google user
            if($isApproved && !empty(GoogleAuth::$auth))
            {
                # Get google auth format of datetime
                $datetimeGoogleStart = DatetimeConverter::getGoogleAuthDateTimeFormat($date);
                $datetimeGoogleEnd = $dateTime;
                $datetimeGoogleEnd->modify("+1 hour");
                $datetimeGoogleEnd = DatetimeConverter::getGoogleAuthDateTimeFormat($datetimeGoogleEnd->format('Y-m-d H:i:s'));

                # Get all users associated with the project
                $usersArray = ProjectFactory::getAllUsersForProject(HTTPSession::getInstance()->PROJECT_ID);
                $users = "";
                $attendees = array();
                foreach($usersArray as $user)
                {
                    # Concatenate them into string
                    $users .= $user->getFirstName() . " " . $user->getLastName() . ", ";
                    # Add attendees
                    $attendees[] = array('email' => $user->getEmail());
                }

                # Remove , at the end
                $users = substr($users, 0, strlen($users)-2);

                # Get project name from project ID
                $project = new Project(HTTPSession::getInstance()->PROJECT_ID);
                $project = $project->getName();

                # In that case, we can add it to the google calendar and save the id of this event
                $googleEventId = GoogleAuth::getInstance()->addEventToCalendar($project, $users, $datetimeGoogleStart, $datetimeGoogleEnd, $attendees);
            }

            if(isset($googleEventId))
                $meeting->setGoogleEventId($googleEventId);

            # If the meeting is repeating we have to create more than one record in the database
            # in order to display them in the list
            if($isRepeating)
            {
                # Array of meetings
                $meetings = [];
                $i = 0;
                # Advance current date of the meeting by a week
                $dateTime->modify('+1 week');
                # while datetime <= datetimeRepeatUntil, create new object
                # each time a new object is created, datetime is advanced by 7 days
                while($dateTime <= $dateTimeRepeatUntil)
                {
                    # Create a copy of the existing meeting object, however with a new date
                    $meetings[$i] = clone $meeting;
                    $meetings[$i]->setDatetime($dateTime->format('Y-m-d H:i:s'));
                    # Save the copy in the database
                    $meetings[$i]->Save();

                    # Advance current date of the meeting by a week
                    $dateTime->modify('+1 week');
                }
            }

            # Save the meeting
            $meeting->Save();

            if(!$isRepeating)
                # If it's not repeating meeting, we wanna display the only meeting we've created
                $header = 'Location: ' . SITE_URL . 'meetings/' . $meeting->getID();

        }

        # Redirect back to action points
        header($header);
        die();
    }

    public function edit($id = null)
    {
        if(isset($id))
        {
            # Get list of all meetings to display them in the left side list
            $meetings = $this->model('MeetingFactory');
            $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID);

            # Create Meeting from provided ID
            $id = new Meeting($id);

            # Check if we have access to editing
            $this->checkAuthIsApproved($id);
            $this->checkAuthProjectScope($id->getProjectId());

            # Set correct format of provided date
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $id->getDatetime());
            $data['date'] = $date->format('d-m-Y');
            $data['hours'] = $date->format('H');
            $data['minutes'] = $date->format('i');

            # Set correct format of provided date for repeatUntil
            $dateRepeatUntil = DateTime::createFromFormat('Y-m-d H:i:s', $id->getRepeatUntil());
            $data['dateRepeatUntil'] = $dateRepeatUntil->format('d-m-Y');

            $this->view('meetings/index', ['meetings'=>$meetings, 'id'=>$id, 'edit'=>true, 'datetime'=>$data]);
        }
        else
            header('Location: '.SITE_URL.'meetings');
    }

    public function editPost($post = null)
    {
        if(isset($post))
        {
            # Create an object of existing meeting
            $meeting = $this->model('Meeting',$post['id']);

            # Check if we have access to editing
            $this->checkAuthIsApproved($meeting);
            $this->checkAuthProjectScope($meeting->getProjectId());

            # Set googleEventId to the value provided from database (if any)
            $googleEventId = $meeting->getGoogleEventId();

            # Save the existing Meeting to the temporary table
            # in order to retrieve it if not approved by supervisor
            $meeting->SaveTemporary();

            # Get details from post request
            $deadline = $post['deadline'];
            $deadline_time_hours = $post['deadline_time_hours'];
            $deadline_time_minutes = $post['deadline_time_minutes'];

            $isApproved = 0;
            if(isset($post['isApproved']))
                $isApproved = 1;
            # If it's the supervisor who edits, it's automatically approved
            if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
                $isApproved = 1;

            $arrivedOnTime = 0;
            if(isset($post['arrivedOnTime']))
                $arrivedOnTime = 1;

            $takenPlace = 0;
            if(isset($post['takenPlace']))
                $takenPlace = 1;

            # Set correct format of provided date
            $dateTime = DateTime::createFromFormat('d-m-Y H:i', $deadline . " " . $deadline_time_hours . ":" . $deadline_time_minutes);
            $date = $dateTime->format('Y-m-d H:i:s');

            # Set provided details
            $meeting->setDatetime($date);
            $meeting->setIsApproved($isApproved);
            $meeting->setTakenPlace($takenPlace);
            $meeting->setArrivedOnTime($arrivedOnTime);

            # Check if meeting is approved by a supervisor and user is logged in as google user
            # Also check if googleEventId exists for this meeting
            if($isApproved && !empty(GoogleAuth::$auth) && !empty($googleEventId))
            {
                # Get google auth format of datetime
                $datetimeGoogleStart = DatetimeConverter::getGoogleAuthDateTimeFormat($date);
                $datetimeGoogleEnd = $dateTime;
                $datetimeGoogleEnd->modify("+1 hour");
                $datetimeGoogleEnd = DatetimeConverter::getGoogleAuthDateTimeFormat($datetimeGoogleEnd->format('Y-m-d H:i:s'));

                # In that case, we can add it to the google calendar and save the id of this event
                $googleEventId = GoogleAuth::getInstance()->editEventInCalendar($googleEventId, $datetimeGoogleStart, $datetimeGoogleEnd);
            }

            if(!empty($googleEventId))
                $meeting->setGoogleEventId($googleEventId);

            # If the meeting is approved, we must delete the temporary one from temp table
            if($isApproved)
                $meeting->RemoveTemporary();

            # Save changes
            $meeting->Save();
        }

        # Redirect back to meetings
        header('Location: '.SITE_URL.'meetings/'.$post['id']);

        die();
    }

    public function approve($id)
    {
        # Only supervisor can approve, no one else has access to this page
        if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)
        {
            # Retrieve action point from database based on provided id
            $meeting = $this->model('Meeting',$id);

            # Check action point project scope access
            $this->checkAuthProjectScope($meeting->getProjectId());

            # Approve the action point
            $meeting->setIsApproved(1);

            # Save the action point
            $meeting->Save();
        }

        # Redirect back to action points
        header('Location: ' . SITE_URL . 'meetings/' . $id);
    }

    public function remove($id)
    {
        # Retrieve a meeting from database based on provided id
        $meeting = $this->model('Meeting',$id);

        # Check access rights for user
        $this->checkAuthIsApproved($meeting);
        $this->checkAuthProjectScope($meeting->getProjectId());

        # Meeting will never actually be removed from database, we will keep it
        # for reference, it is only flagged as removed
        $meeting->Delete();

        # Redirect back to meetings
        header('Location: ' . SITE_URL . 'meetings/' . $id . '/deleted');
    }

    public function cancel($id = null)
    {
        if($id)
        {
            # Get list of all meetings to display them in the left side list
            $meetings = $this->model('MeetingFactory');
            $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID);

            # Create Meeting from provided ID
            $id = $this->model('Meeting',$id);

            # Check if we have access to cancelling
            $this->checkAuthIsNotApproved($id);
            $this->checkAuthProjectScope($id->getProjectId());

            $this->view('meetings/index', ['id'=>$id, 'cancel'=>true, 'meetings'=>$meetings]);
        }
        else
            # Redirect back to meetings
            header('Location: ' . SITE_URL . 'meetings');
    }

    public function cancelPost($post = null)
    {
        if($post)
        {
            # Retrieve values from post request
            $id = $post['id'];
            $reason = $post['reason'];

            # Retrieve meeting from provided ID
            $meeting = $this->model('Meeting',$id);

            # Check if we have access to cancelling
            $this->checkAuthIsNotApproved($meeting);
            $this->checkAuthProjectScope($meeting->getProjectId());

            # Set values
            $meeting->setIsCancelled(1);
            $meeting->setReasonForCancel($reason);

            # If it's a student, it needs approval from a supervisor
            if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_STUDENT)
                $meeting->setIsApproved(0);

            $meeting->Save();

            # Redirect back to meetings
            header('Location: ' . SITE_URL . 'meetings/' . $id);
            die();
        }
        else
            # Redirect back to meetings
            header('Location: ' . SITE_URL . 'meetings');
    }

    public function revertRemoval($id)
    {
        $meeting = $this->model('Meeting',$id);

        # Check if user is allowed to perform this action
        $this->checkAuthIsApproved($meeting);
        $this->checkAuthProjectScope($meeting->getProjectId());

        $meeting->setIsDeleted(0);

        $meeting->Save();

        # Redirect back to actionpoints
        header('Location: ' . SITE_URL . 'meetings');
    }

    protected function checkAuthIsApproved($meeting)
    {
        # No access if:
        # 1. User is a student and a meeting has been approved
        # 2. Meeting has taken place
        if(($meeting->getIsApproved() && HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_STUDENT) || $meeting->getTakenPlace())
        {
            header('Location: ' . SITE_URL . 'meetings');
            # Do not execute code any longer
            die();
        } else
            return true;
    }

    protected function checkAuthIsNotApproved($meeting)
    {
        # No access if:
        # 1. Meeting is not approved
        # 2. Meeting has taken place
        if(!$meeting->getIsApproved() || $meeting->getTakenPlace())
        {
            header('Location: ' . SITE_URL . 'meetings');
            # Do not execute code any longer
            die();
        } else
            return true;
    }

}