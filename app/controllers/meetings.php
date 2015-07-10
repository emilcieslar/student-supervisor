<?php

class Meetings extends Controller
{
    public function index($id = null, $month = null, $year = null)
    {
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID);

        # Is there a specific ID to display or display default?
        if($id)
            # Create Action Point from provided ID
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
}