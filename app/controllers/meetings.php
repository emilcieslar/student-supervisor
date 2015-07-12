<?php

class Meetings extends Controller
{
    public function index($id = null, $month = null, $year = null)
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

        $this->view('meetings/index', ['meetings'=>$meetings, 'id'=>$id]);
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
        # If it's a post request, we'll have parameters passed
        if($post)
        {
            # Create an empty meeting
            $meeting = $this->model('Meeting');

            # Get details from post request
            $deadline = $post['deadline'];
            $deadline_time_hours = $post['deadline_time_hours'];
            $deadline_time_minutes = $post['deadline_time_minutes'];

            $isRepeating = 0;
            $repeatUntil = 0;
            $dateTimeRepeatUntil = null;
            if(isset($post['isRepeating']))
            {
                $isRepeating = 1;
                $repeatUntil = $post['repeatUntil'];
                $dateTimeRepeatUntil = DateTime::createFromFormat('d-m-Y H:i', $repeatUntil . " 23:59");
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


        }

        # Redirect back to action points
        Header('Location: ' . SITE_URL . 'meetings');
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
        header('Location: '.SITE_URL.'meetings');

        die();
    }
}