<?php

/**
 * This class handles what view should be displayed,
 * or what model should be loaded, all controllers extend this class
 * It is inspired by PHP Academy build PHP MVC APP
 * that is available here https://www.youtube.com/watch?v=OsCTzGASImQ
 */
class Controller
{
    /**
     * Generate a new model object
     * @param $model the name of the model
     * @param $param the id of the object to be instantiated
     * @return mixed the instantiated object
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
     * Display a view and provide it with a $data array that can be
     * accessed in the html template
     * @param string $view the html template
     * @param array $data the data that are fed into the template
     * @param boolean $dashboard whether display a dashboard or not
     */
    protected function view($view, $data = [], $dashboard = true)
    {
        if($dashboard)
        {
            # Get info about the project if we are displaying dashboard
            $project = $this->model('Project',HTTPSession::getInstance()->PROJECT_ID);
            $projectUsers = $this->model('ProjectFactory')->getAllUsersForProject(HTTPSession::getInstance()->PROJECT_ID);

            $data['project'] = $project;
            $data['projectUsers'] = $projectUsers;
        }

        # Display header
        require_once 'public/header.php';

        # Display header of the dashboard
        if($dashboard)
            require_once 'public/dashboard_header.php';

        # Display the actual view
        require_once 'app/views/' . $view . '.php';

        # Display footer of the dashboard
        if($dashboard)
            require_once 'public/dashboard_footer.php';

        # Display footer
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