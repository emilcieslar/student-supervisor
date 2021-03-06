<?php

/**
 * Controls all the functionality associated with Notes
 */
class Notes extends Controller
{
    public function index($id = null, $delete = null)
    {
        # Get notes from database
        $notes = $this->model('NoteFactory');
        $notes = $notes->getNotes();

        # Get meetings for the filtering feature (only those that taken place)
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true, true);

        # Set values
        $data = array();
        $data['notes'] = $notes;
        $data['meetings'] = $meetings;

        # If delete is set
        if($delete)
            $data['delete'] = $id;

        # Display the view
        $this->view('notes/index', $data);
    }

    /**
     * A method to filter notes by a meeting
     * @param int $id the meeting id
     */
    public function meeting($id = null)
    {
        # Get notes based on meeting id
        $notes = $this->model('NoteFactory');
        $notes = $notes->getNotes($id);

        # Get meetings to display the list
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true, true);

        # Display the view
        $this->view('notes/index', ['notes'=>$notes, 'meeting'=>$id, 'meetings'=>$meetings]);
    }

    /**
     * A method to create a note
     * @param bool $agenda if it's agenda note
     */
    public function create($agenda = false)
    {
        # Get meetings for the list
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true, true);

        # Set values
        $data['meetings'] = $meetings;
        # If it's agenda note being created
        if($agenda)
            $data['agenda'] = true;

        # Display the view
        $this->view('notes/create', $data);
    }

    /**
     * A method to process POST request for adding a new note
     * @param null $post the $_POST array
     */
    public function createPost($post = null)
    {
        if($post)
        {
            # Get values from post
            $title = $post['title'];
            # If we're adding agenda note, we don't have a meeting id
            if(!isset($post['isAgenda']))
                $meetingId = $post['meetingId'];

            $isPrivate = 0;
            if(isset($post['isPrivate']))
                $isPrivate = 1;

            $text = $post['text'];

            # Create an empty note
            $note = $this->model('Note');

            # Set note with provided values
            $note->setTitle($title);
            $note->setText($text);
            $note->setUserId(HTTPSession::getInstance()->GetUserID());
            $note->setProjectId(HTTPSession::getInstance()->PROJECT_ID);

            # If we're adding agenda note
            if(isset($post['isAgenda']))
            {
                $note->setIsAgenda(1);
                # Get the next meeting
                $nextMeeting = $this->model('MeetingFactory')->getNextMeeting();
                # If the next meeting exists
                if($nextMeeting)
                    $nextMeeting = $nextMeeting->getID();
                else
                    $nextMeeting = 0;

                # Set the next meeting
                $note->setMeetingId($nextMeeting);
            } else
            # If it's a normal note
            {
                $note->setMeetingId($meetingId);
                $note->setIsPrivate($isPrivate);
            }

            # Save the note
            $note->Save();

            # If it's not a private note, create a notification
            if(!$isPrivate)
                new NotificationNote($note->getID(),NotificationNote::ADDED);

            # If it was agenda note, redirect back to agenda
            if(isset($post['isAgenda']))
                # Redirect back to agenda
                header('Location: ' . SITE_URL . 'agenda');
            else
                # Redirect back to notes
                header('Location: ' . SITE_URL . 'notes');

            die();

        }
    }

    /**
     * A method to display a note
     * @param int $id the note id
     */
    public function note($id)
    {
        # Get the note from db
        $note = $this->model('Note',$id);

        # Check whether user is authorized to view the note
        # Specifically here it means if this note is private, is the user the one, who
        # created the note? However if the note is not private, we don't wanna checkAuth
        # because the note should be visible to the user
        if($note->getIsPrivate())
            $this->checkAuth($note->getUserId(), $note->getProjectId());

        # Display the view
        $this->view('notes/note', ['note'=>$note]);
    }

    /**
     * A method to edit an existing note
     * @param int $id the note id
     * @param bool $agenda if it's agenda note
     */
    public function edit($id, $agenda = false)
    {
        # Get the note from db
        $note = $this->model('Note',$id);

        # Check whether user is authorized to view an edit screen
        $this->checkAuth($note->getUserId(), $note->getProjectId());

        # Get the meetings list
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true, true);

        # Set values
        $data['note'] = $note;
        $data['meetings'] = $meetings;

        if($agenda)
            $data['agenda'] = true;

        # Display the view
        $this->view('notes/edit', $data);
    }

    /**
     * A method to process POST request for editing an existing note
     * @param null $post the $_POST array
     */
    public function editPost($post = null)
    {
        if($post)
        {
            # Get values from post
            $title = $post['title'];
            # If we're adding agenda note, we don't have a meeting id
            if(!isset($post['isAgenda']))
                $meetingId = $post['meetingId'];

            $isPrivate = 0;
            if(isset($post['isPrivate']))
                $isPrivate = 1;
            $text = $post['text'];

            $id = $post['id'];

            # Create a note object from provided ID
            $note = $this->model('Note',$id);

            # Check whether user is authorized to edit a note
            $this->checkAuth($note->getUserId(), $note->getProjectId());

            # Set values
            $note->setTitle($title);
            $note->setText($text);

            # If we're not editing agenda note
            if(!isset($post['isAgenda']))
            {
                $note->setMeetingId($meetingId);
                $note->setIsPrivate($isPrivate);
            }

            # Save the note
            $note->Save();

            # If it was agenda note, go back to agenda
            if(isset($post['isAgenda']))
                # Redirect back to agenda
                header('Location: ' . SITE_URL . 'agenda');
            else
                # Redirect back to notes
                header('Location: ' . SITE_URL . 'notes');

            die();

        }
    }

    /**
     * Check ID against the logged in user if the user is authorized to view/edit the Note
     * - only user that created the note can edit it
     * - private notes are visible only to user who creates them
     * - notes are visible only within a scope of a project
     * @param $noteUserId
     * @param $noteProjectId
     */
    private function checkAuth($noteUserId, $noteProjectId)
    {

        if($noteUserId != HTTPSession::getInstance()->GetUserID() || $noteProjectId != HTTPSession::getInstance()->PROJECT_ID)
        {
            # Redirect back to notes if user is not authorized
            header('Location: ' . SITE_URL . 'notes');
            # Do not continue to execute code
            die();
        }
    }

    /**
     * A method to remove an existing note
     * @param int $id the note id
     * @param bool $agenda if it's agenda note
     */
    public function remove($id, $agenda = false)
    {
        # Get the note object
        $note = $this->model('Note',$id);

        # Check whether user is authorized to delete a note
        $this->checkAuth($note->getUserId(), $note->getProjectId());

        $note->Delete();

        if($agenda)
            header('Location: ' . SITE_URL . 'agenda/' . $id . '/deleted');
        else
            header('Location: ' . SITE_URL . 'notes/' . $id . '/deleted');
    }

    /**
     * A method to revert the removal of a note
     * @param int $id the note id
     * @param bool $agenda if it's agenda note
     */
    public function revertRemoval($id, $agenda = false)
    {
        # Get the removed note object
        $note = $this->model('Note',$id);

        # Check whether user is authorized to revert back a note
        $this->checkAuth($note->getUserId(), $note->getProjectId());

        # Revert removal
        $note->setIsDeleted(0);

        # Save changes
        $note->Save();

        # If it was agenda note, return back to agenda
        if($agenda)
            # Redirect back to agenda
            header('Location: ' . SITE_URL . 'agenda');
        else
            # Redirect back to notes
            header('Location: ' . SITE_URL . 'notes');
    }
}