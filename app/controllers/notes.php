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

    public function create()
    {
        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true);

        $this->view('notes/create', ['meetings'=>$meetings]);
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
            $note->setMeetingId($meetingId);
            $note->setIsPrivate($isPrivate);
            $note->setText($text);
            $note->setUserId(HTTPSession::getInstance()->GetUserID());
            $note->setProjectId(HTTPSession::getInstance()->PROJECT_ID);

            $note->Save();

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

    public function edit($id)
    {
        $note = $this->model('Note',$id);

        # Check whether user is authorized to view an edit screen
        $this->checkAuth($note->getUserId(), $note->getProjectId());

        $meetings = $this->model('MeetingFactory');
        $meetings = $meetings->getMeetingsForProject(HTTPSession::getInstance()->PROJECT_ID, true);
        $this->view('notes/edit', ['note'=>$note, 'meetings'=>$meetings]);
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
            $note->setMeetingId($meetingId);
            $note->setIsPrivate($isPrivate);
            $note->setText($text);

            $note->Save();

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

    public function remove($id)
    {
        $note = $this->model('Note',$id);

        # Check whether user is authorized to delete a note
        $this->checkAuth($note->getUserId(), $note->getProjectId());

        $note->Delete();
        header('Location: ' . SITE_URL . 'notes/' . $id . '/deleted');
    }

    public function revertRemoval($id)
    {
        $note = $this->model('Note',$id);

        # Check whether user is authorized to revert back a note
        $this->checkAuth($note->getUserId(), $note->getProjectId());

        $note->setIsDeleted(0);

        $note->Save();
        header('Location: ' . SITE_URL . 'notes');
    }
}