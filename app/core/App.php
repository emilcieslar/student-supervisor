<?php

/**
 * Class App
 * This class takes care of basic routing.
 * It is inspired by PHP Academy build a basic PHP MVC APP
 * that is available here https://www.youtube.com/watch?v=OsCTzGASImQ
 */

class App
{
    # Default controller
    protected $controller = 'home';
    # Default method
    protected $method = 'index';
    # Default params
    protected $params = [];

    # Constants to avoid magic numbers - they define position
    # in an array that parsed from URL
    const CONTROLLER = 0;
    const METHOD = 1;

    public function __construct()
    {
        # Get sanitized URL
        $url = $this->parseUrl();

        # Check if controller that's in the URL exists
        if (file_exists('app/controllers/' . $url[self::CONTROLLER] . '.php')) {
            # Set controller to the one provided by the user
            $this->controller = $url[self::CONTROLLER];
            # Unset the controller from the array so we can later pass only
            # remaining parameters if any
            unset($url[self::CONTROLLER]);
        }

        # Load the controller
        require_once 'app/controllers/' . $this->controller . '.php';

        # Create instance of the controller
        $this->controller = new $this->controller;

        # Check if there is any requested method in the url
        # If not, it will just go with the default index method
        if (isset($url[self::METHOD])) {
            # Check if that method exists within specified controller
            if (method_exists($this->controller, $url[self::METHOD])) {
                $this->method = $url[self::METHOD];
                unset($url[self::METHOD]);
            }
        }

        # Check if there are any parameters in the url left
        # array_values will rebase the indexes after unsetting the controller and the method
        $this->params = $url ? array_values($url) : [];

        # Check whether it's a POST request and handle it
        # The post request is different in that it passes the whole POST array as a parameter
        if($_SERVER['REQUEST_METHOD'] == 'POST')
            call_user_func([$this->controller, $this->method], $_POST);
        else
            # Call the method in the specified controller and pass parameters if any (this is a GET request)
            # This will be performed only if it wasn't a POST request
            call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Gets the URL and explodes it by '/' into an array
     * If the url is empty, empty array is returned
     * @return array the sanitized URL
     */
    protected function parseUrl()
    {
        if(isset($_GET['url']))
        {
            return $url = explode('/',filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }

        # If there's nothing, return an empty array and default will take place
        return array();
    }

}