<?php

class Controller
{
    public function __construct()
    {

    }

    /**
     * Generate a new model object
     * @param $model
     * @param $param
     * @return mixed
     */
    protected function model($model, $param = null)
    {
        # Require model file
        require_once 'app/models/' . $model . '.php';

        # Return the model as an object
        if($param)
            # If we want to create an existing class from database
            return new $model($param);
        else
            # Otherwise create an empty class
            return new $model();
    }

    /**
     * Display a view
     * @param $view
     * @param array $data
     */
    protected function view($view, $data = [], $dashboard = true)
    {
        require_once 'public/header.php';

        if($dashboard)
            require_once 'public/dashboard_header.php';

        require_once 'app/views/' . $view . '.php';

        if($dashboard)
            require_once 'public/dashboard_footer.php';

        require_once 'public/footer.php';
    }

    /**
     * Checks whether an object (this can be ActionPoint, Note, ...) a user is trying to display/edit/remove
     * has the same projectId associated with it as the one stored in the current logged in session
     * @param $objectProjectId int the ID of the associated project
     *
     * @return true if user has access
     */
    protected function checkAuthProjectScope($objectProjectId)
    {
        if($objectProjectId != HTTPSession::getInstance()->PROJECT_ID)
        {
            # Redirect to the warning page
            header('Location: ' . SITE_URL . 'accessDenied');
            # Do not continue to execute code
            die();
        } else
            return true;
    }
}