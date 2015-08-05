<?php

class Agenda extends Controller
{
    public function index($delete = false)
    {
        # Get the next meeting object
        $nextMeeting = $this->model('MeetingFactory')->getNextMeeting();

        # Set default values
        $actionPoints = null;
        $notes = null;

        # If there is a meeting scheduled
        if($nextMeeting)
        {
            # Get action points for the next meeting
            $actionPoints = $this->model('ActionPointFactory')->getActionPointsForAgenda();
            # Get agenda notes for the next meeting
            $notes = $this->model('NoteFactory')->getNotes($nextMeeting->getID(), true);
        }

        # Init the status and statistics of the project
        $rag = $this->model('RedAmberGreen');

        # Get info about project
        $project = $this->model('Project',HTTPSession::getInstance()->PROJECT_ID);
        $projectUsers = $this->model('ProjectFactory')->getAllUsersForProject(HTTPSession::getInstance()->PROJECT_ID);

        # Assign values
        $data['nextMeeting'] = $nextMeeting;
        $data['actionPoints'] = $actionPoints;
        $data['notes'] = $notes;
        $data['rag'] = $rag;
        $data['project'] = $project;
        $data['projectUsers'] = $projectUsers;

        # In case we want to display a message that an agenda note has been deleted
        if($delete)
            $data['delete'] = $delete;

        # Display agenda
        $this->view('agenda/index', $data);
    }
}