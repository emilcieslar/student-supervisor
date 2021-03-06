<?php

/**
 * Controls all the functionality associated with Meetings
 */
class Meetings extends Controller
{
    public function index($id = null, $delete = null)
    {
        # Get all meetings from database
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

        # Display the view
        $this->view('meetings/index', $data);
    }

    /**
     * Displays a list of action points and a form to Add Meeting
     */
    public function add()
    {
        # Get meetings to display them
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID);

        # Display the view
        $this->view('meetings/index', ['meetings'=>$meetings, 'add'=>true]);
    }

    /**
     * A method to process POST request for adding a new meeting
     * @param null $post the $_POST array
     */
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

            # Getting more data from post request...
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

            # Set meeting with provided information
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

                # We can add it to the google calendar and save the id of this event
                $googleEventId = GoogleAuth::getInstance()->addEventToCalendar($project, $users, $datetimeGoogleStart, $datetimeGoogleEnd, $attendees);
            }

            # If it's a google event, set it in the meeting
            if(isset($googleEventId))
                $meeting->setGoogleEventId($googleEventId);

            # If the meeting is repeating we have to create more than one record in the database
            # in order to display them in the list
            # However other meetings won't be added to google since in this version only a single meeting
            # can be added to the google calendar
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
            {
                # If it's not repeating meeting, we want to display the only meeting we've created
                $header = 'Location: ' . SITE_URL . 'meetings/' . $meeting->getID();

                # Create a new notification only if it's not repeating meeting, creating a notification
                # for repeated meeting would be a future work
                new NotificationMeeting($meeting->getID(),NotificationMeeting::ADDED);
            }
        }

        # Redirect back to action points
        header($header);
        die();
    }

    /**
     * A method to edit a meeting
     * @param int $id the id used to identify the meeting to be edited
     */
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
            $this->checkAuthCancelled($id);
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

    /**
     * A method to process POST request for editing an existing meeting
     * @param null $post the $_POST array
     */
    public function editPost($post = null)
    {
        if(isset($post))
        {
            # Create an object of existing meeting
            $meeting = $this->model('Meeting',$post['id']);

            # Check if we have access to editing
            $this->checkAuthIsApproved($meeting);
            $this->checkAuthCancelled($meeting);
            $this->checkAuthProjectScope($meeting->getProjectId());

            # Set googleEventId to the value provided from database (if any)
            $googleEventId = $meeting->getGoogleEventId();

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

            $isCancelled = 0;
            if(isset($post['isCancelled']))
                $isCancelled = 1;

            # Set correct format of provided date
            $dateTime = DateTime::createFromFormat('d-m-Y H:i', $deadline . " " . $deadline_time_hours . ":" . $deadline_time_minutes);
            $date = $dateTime->format('Y-m-d H:i:s');

            # Set meeting with provided details
            $meeting->setDatetime($date);
            $meeting->setIsApproved($isApproved);
            $meeting->setTakenPlace($takenPlace);
            $meeting->setArrivedOnTime($arrivedOnTime);
            $meeting->setIsCancelled($isCancelled);

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

            # Save changes
            $meeting->Save();

            # If meeting has taken place, create a notification
            if($takenPlace)
                new NotificationMeeting($meeting->getID(),NotificationMeeting::TAKEN_PLACE);
        }

        # Redirect back to meetings
        header('Location: '.SITE_URL.'meetings/'.$post['id']);

        die();
    }

    /**
     * A method to approve the meeting proposed by a student
     * @param int $id the meeting id
     */
    public function approve($id)
    {
        # Only supervisor can approve, no one else has access to this method
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

    /**
     * A method to remove a meeting
     * @param int $id the meeting id
     */
    public function remove($id)
    {
        # Retrieve a meeting from database based on provided id
        $meeting = $this->model('Meeting',$id);

        # Check access rights for user
        $this->checkAuthIsApproved($meeting);
        $this->checkAuthCancelled($meeting);
        $this->checkAuthProjectScope($meeting->getProjectId());

        # Meeting will never actually be removed from database, we will keep it
        # for reference, it is only flagged as removed
        $meeting->Delete();

        # Redirect back to meetings
        header('Location: ' . SITE_URL . 'meetings/' . $id . '/deleted');
    }

    /**
     * A method to cancel a meeting
     * @param null $id the meeting id
     * @param null $error display error in case trying to cancel two meetings in a row
     */
    public function cancel($id = null, $error = null)
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
            $this->checkAuthTwoMeetingsInRow($id, $error);

            $this->view('meetings/index', ['id'=>$id, 'cancel'=>true, 'meetings'=>$meetings, 'error'=>$error]);
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
            $this->checkAuthTwoMeetingsInRow($meeting);

            # Set values
            $meeting->setIsCancelled(1);
            $meeting->setReasonForCancel($reason);

            # If it's a student, it needs approval from a supervisor
            if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_STUDENT)
                $meeting->setIsApproved(0);

            # Save the changes
            $meeting->Save();

            # Create a new notification
            new NotificationMeeting($meeting->getID(),NotificationMeeting::CANCELLED);

            # Redirect back to meetings
            header('Location: ' . SITE_URL . 'meetings/' . $id);
            die();
        }
        else
            # Redirect back to meetings
            header('Location: ' . SITE_URL . 'meetings');
    }

    /**
     * A method to revert removed meeting
     * @param $id the meeting id
     */
    public function revertRemoval($id)
    {
        # Create the meeting object
        $meeting = $this->model('Meeting',$id);

        # Check if user is allowed to perform this action
        $this->checkAuthIsApproved($meeting);
        $this->checkAuthProjectScope($meeting->getProjectId());

        # Revert the deletion
        $meeting->setIsDeleted(0);

        # Save changes
        $meeting->Save();

        # Redirect back to actionpoints
        header('Location: ' . SITE_URL . 'meetings');
    }

    /**
     * A method to check if user is allowed to perform certain actions on meeting
     * No access if:
     * 1. User is a student and a meeting has been approved
     * 2. Meeting has taken place
     * @param Meeting $meeting the meeting object
     * @return bool true if allowed
     */
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

    /**
     * A method to check if user is allowed to perform certain actions on meeting
     * No access if:
     * 1. Meeting is not approved
     * 2. Meeting has taken place
     * @param Meeting $meeting the meeting object
     * @return bool true if allowed
     */
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

    /**
     * A method to deny access if meeting was cancelled and approved
     * @param Meeting $meeting the meeting object
     */
    protected function checkAuthCancelled($meeting)
    {
        # If a meeting was cancelled and cancellation approved, no access
        if($meeting->getIsApproved() && $meeting->getIsCancelled())
        {
            header('Location: ' . SITE_URL . 'meetings');
            # Do not execute code any longer
            die();
        }
    }

    /**
     * A method to deny access if user is trying to cancel two meetings in a row
     * @param Meeting $meeting the meeting object
     * @param null $error if error should be displayed
     */
    protected function checkAuthTwoMeetingsInRow($meeting, $error = null)
    {
        # Two meetings in a row cannot be cancelled, if previous meeting was cancelled
        # then we cannot approve this meeting to be cancelled
        if($meeting->getPreviousMeeting() instanceof Meeting && $meeting->getPreviousMeeting()->getIsCancelled() && !$error)
            header('Location: '. SITE_URL . 'meetings/cancel/'.$meeting->getID().'/error');

    }

}