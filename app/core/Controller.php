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
    protected function view($view, $data = [])
    {
        require_once 'public/header.php';
        require_once 'app/views/' . $view . '.php';
        require_once 'public/footer.php';
    }
}