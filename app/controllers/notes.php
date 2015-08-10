<?php

class Notes extends Controller
{
    public function index($id = null, $delete = null)
    {
        $notes = $this->model('NoteFactory');
        $notes = $notes->getNotes();

        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true);

        $data = array();
        $data['notes'] = $notes;
        $data['meetings'] = $meetings;

        # If delete is set
        if($delete)
            $data['delete'] = $id;

        $this->view('notes/index', $data);
    }

    public function meeting($id = null)
    {
        $notes = $this->model('NoteFactory');
        $notes = $notes->getNotes($id);

        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true);

        $this->view('notes/index', ['notes'=>$notes, 'meeting'=>$id, 'meetings'=>$meetings]);
    }

    public function create($agenda = false)
    {
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true);

        $data['meetings'] = $meetings;

        if($agenda)
            $data['agenda'] = true;

        $this->view('notes/create', $data);
    }

    public function createPost($post = null)
    {
        if($post)
        {
            # Get values from post
            $title = $post['title'];
            $meetingId = $post['meetingId'];

            $isPrivate = 0;
            if(isset($post['isPrivate']))
                $isPrivate = 1;

            $text = $post['text'];

            # Create an empty note
            $note = $this->model('Note');

            # Set values
            $note->setTitle($title);
            $note->setText($text);
            $note->setUserId(HTTPSession::getInstance()->GetUserID());
            $note->setProjectId(HTTPSession::getInstance()->PROJECT_ID);

            # If we're adding agenda note
            if(isset($post['isAgenda']))
            {
                $note->setIsAgenda(1);
                $nextMeeting = $this->model('MeetingFactory')->getNextMeeting();
                if($nextMeeting)
                    $nextMeeting = $nextMeeting->getID();
                else
                    $nextMeeting = 0;

                $note->setMeetingId($nextMeeting);
            } else
            # If it's a normal note
            {
                $note->setMeetingId($meetingId);
                $note->setIsPrivate($isPrivate);
            }

            $note->Save();

            # If it's not a private note, create a notification
            if(!$isPrivate)
                new NotificationNote($note->getID(),NotificationNote::ADDED);

            if(isset($post['isAgenda']))
                # Redirect back to agenda
                header('Location: ' . SITE_URL . 'agenda');
            else
                # Redirect back to notes
                header('Location: ' . SITE_URL . 'notes');

            die();

        }
    }

    public function note($id)
    {
        $note = $this->model('Note',$id);

        # Check whether user is authorized to view the note
        # Specifically here it means if this note is private, is the user the one, who
        # created the note? However if the note is not private, we don't wanna checkAuth
        # because the note should be visible to the user
        if($note->getIsPrivate())
            $this->checkAuth($note->getUserId(), $note->getProjectId());

        $this->view('notes/note', ['note'=>$note]);
    }

    public function edit($id, $agenda = false)
    {
        $note = $this->model('Note',$id);

        # Check whether user is authorized to view an edit screen
        $this->checkAuth($note->getUserId(), $note->getProjectId());

        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true);

        $data['note'] = $note;
        $data['meetings'] = $meetings;

        if($agenda)
            $data['agenda'] = true;

        $this->view('notes/edit', $data);
    }

    public function editPost($post = null)
    {
        if($post)
        {
            # Get values from post
            $title = $post['title'];
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

            $note->Save();

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

    public function remove($id, $agenda = false)
    {
        $note = $this->model('Note',$id);

        # Check whether user is authorized to delete a note
        $this->checkAuth($note->getUserId(), $note->getProjectId());

        $note->Delete();

        if($agenda)
            header('Location: ' . SITE_URL . 'agenda/' . $id . '/deleted');
        else
            header('Location: ' . SITE_URL . 'notes/' . $id . '/deleted');
    }

    public function revertRemoval($id, $agenda = false)
    {
        $note = $this->model('Note',$id);

        # Check whether user is authorized to revert back a note
        $this->checkAuth($note->getUserId(), $note->getProjectId());

        $note->setIsDeleted(0);

        $note->Save();

        if($agenda)
            # Redirect back to agenda
            header('Location: ' . SITE_URL . 'agenda');
        else
            # Redirect back to notes
            header('Location: ' . SITE_URL . 'notes');
    }
}