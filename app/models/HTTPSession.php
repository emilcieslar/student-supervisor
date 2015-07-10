<?php

/**
 * Class HTTPSession
 * Used for more secure session management than the default one in PHP
 * Uses database to store session instead of a file
 *
 * 1. Instantiate the session – $objSession = new HTTPSession($objPDO);
 * 2. Impress the session (for session timeout purposes) – $objSession->Impress();
 * 3. Create new session variable – $objSession->TESTVAR = 'valuegoeshere';
 */
require_once('User.php');

class HTTPSession
{
    private $php_session_id;
    private $native_session_id;
    private $objPDO;
    private $logged_in;
    private $user_id;
    private $session_timeout = 3600;      # 10 minute inactivity timeout
    private $session_lifespan = 3600;    # 1 hour session duration

    private static $instance = null;

    /**
     * Using singleton pattern we can access session anywhere in the app
     * HTTPSession->getInstance() will return the only instance created in init.php
     * @return HTTPSession instance
     */
    public static function getInstance()
    {
        if(!isset(HTTPSession::$instance ))
            HTTPSession::$instance = new HTTPSession();

        return HTTPSession::$instance;
    }

    private function __construct()
    {
        # Set database connection
        $this->objPDO = PDOFactory::get();
        # Set up the handler
        session_set_save_handler(
            array(&$this, '_session_open_method'),
            array(&$this, '_session_close_method'),
            array(&$this, '_session_read_method'),
            array(&$this, '_session_write_method'),
            array(&$this, '_session_destroy_method'),
            array(&$this, '_session_gc_method')
        );

        # Check the cookie passed - if one is - if it looks wrong we'll scrub it right away
        $strUserAgent = $_SERVER["HTTP_USER_AGENT"];

        if (isset($_COOKIE["PHPSESSID"]))
        {
            # Security and age check
            $this->php_session_id = $_COOKIE["PHPSESSID"];

            # Prepare statement for database
            $strQuery = "SELECT id FROM http_session WHERE ascii_session_id = '" . $this->php_session_id . "' AND ((now() - created) < ' " . $this->session_lifespan . " seconds') AND user_agent='$strUserAgent" . "' AND ((now() - last_impression) <= '".$this->session_timeout." seconds' OR last_impression IS NULL)";

            # Execute statement
            $objStatement = $this->objPDO->query($strQuery);

            # Fetch it from the database
            $row = $objStatement->fetch(PDO::FETCH_ASSOC);

            # If such row doesn't exist...
            if (!$row)
            {
                # Set failed flag
                $failed = 1;
                # Delete from database - we do garbage cleanup at the same time
                $maxlifetime = $this->session_lifespan;
                $strQuery = "DELETE FROM http_session WHERE (ascii_session_id = '". $this->php_session_id . "') OR (now() - created > '$maxlifetime seconds')";
                unset($objStatement);
                $objStatement = $this->objPDO->query($strQuery);
                # Clean up stray session variables
                $strQuery = "DELETE FROM session_variable WHERE session_id NOT IN (SELECT id FROM http_session)";
                unset($objStatement);
                $objStatement = $this->objPDO->query($strQuery);
                # Get rid of this one... this will force PHP to give us another
                unset($_COOKIE["PHPSESSID"]);
            }
        }

        # Set the life time for the cookie
        session_set_cookie_params($this->session_lifespan);
        # Call the session_start method to get things started
        session_start();
    }

    public function Impress()
    {
        if ($this->native_session_id) {
            $strQuery = "UPDATE http_session SET last_impression = now() WHERE id = " . $this->native_session_id;
            $this->objPDO->query($strQuery);
        }
    }

    public function IsLoggedIn()
    {
        return($this->logged_in);
    }

    public function GetUserID()
    {
        if ($this->logged_in)
            return($this->user_id);
        else
            return(false);
    }

    public function GetUserObject()
    {
        if ($this->logged_in)
        {
            $objUser = new User($this->user_id);
            return($objUser);
        }
    }

    public function GetSessionIdentifier()
    {
        return($this->php_session_id);
    }

    public function Login($strUsername, $strPlainPassword)
    {
        $strMD5Password = md5($strPlainPassword);
        $strQuery = "SELECT id FROM User WHERE username = :username AND password = :pass";
        $objStatement = $this->objPDO->prepare($strQuery);

        $objStatement->bindValue(':username', $strUsername, PDO::PARAM_STR);
        $objStatement->bindValue(':pass', $strMD5Password, PDO::PARAM_STR);

        $objStatement->execute();

        $row = $objStatement->fetch(PDO::FETCH_ASSOC);

        if ($row) {

            $this->user_id = $row["id"];
            $this->logged_in = true;

            unset($objStatement);
            $strQuery = "UPDATE http_session SET logged_in = 1, user_id = " . $this->user_id . " WHERE id = " . $this->native_session_id;
            $objStatement = $this->objPDO->prepare($strQuery);
            $objStatement->execute();
            return(true);
        } else {
            return(false);
        }
    }

    public function LoginGoogle($email)
    {
        # Check if Google User is in the database
        $strQuery = "SELECT email,id FROM User WHERE email = :email";
        $objStatement = $this->objPDO->prepare($strQuery);
        $objStatement->bindValue(':email',$email,PDO::PARAM_STR);
        $objStatement->execute();

        $row = $objStatement->fetch(PDO::FETCH_ASSOC);

        # If there's a record with this user
        if($row)
        {
            # Set user ID and log in
            $this->user_id = $row['id'];
            $this->logged_in = true;

            # Update session in database
            unset($objStatement);
            $strQuery = "UPDATE http_session SET logged_in = 1, user_id = " . $this->user_id . " WHERE id = " . $this->native_session_id;
            $objStatement = $this->objPDO->prepare($strQuery);
            $objStatement->execute();

            # Get user object
            $user = HTTPSession::getInstance()->GetUserObject();
            # Set project id session
            HTTPSession::getInstance()->PROJECT_ID = $user->getProjectId();
            # Set user type session
            HTTPSession::getInstance()->USER_TYPE = $user->getType();
            # Set username session
            HTTPSession::getInstance()->USERNAME = $user->getUsername();
        }
        # If there's no record with such user
        else
        {
            # Create it in the database
            /*$strQuery = "INSERT INTO User(username, first_name, last_name, type, email) VALUES(:username,:first_name,:last_name,:user_type,:email)";
            unset($objStatement);
            $objStatement = $this->objPDO->prepare($strQuery);
            $objStatement->bindValue(':username',$email,PDO::PARAM_STR);
            $objStatement->bindValue(':first_name',$email,PDO::PARAM_STR);
            $objStatement->bindValue(':last_name',$email,PDO::PARAM_STR);
            $objStatement->bindValue(':user_type',1,PDO::PARAM_INT);
            $objStatement->bindValue(':email',$email,PDO::PARAM_STR);
            $objStatement->execute();

            $this->user_id = $this->objPDO->lastInsertId("User_id_seq");
            $this->logged_in = true;*/


            # OR Let user know that he/she doesn't have permissions to enter the site
            HTTPSession::getInstance()->ACCESS_TOKEN = null;
            header("Location: ".SITE_URL."login/permissionDenied");
        }


    }

    public function LogOut()
    {
        if ($this->logged_in == true) {
            $strQuery = "UPDATE http_session SET logged_in = 0, user_id = 0 WHERE id = " . $this->native_session_id;
            $objStatement = $this->objPDO->prepare($strQuery);
            $objStatement->execute();

            # In case it was a google sign in
            if(!empty(GoogleAuth::$auth))
                # Unset access token
                HTTPSession::getInstance()->ACCESS_TOKEN = null;

            $this->logged_in = false;
            $this->user_id = 0;
            return(true);
        } else {
            return(false);
        };
    }

    public function __get($nm)
    {
        $strQuery = "SELECT variable_value FROM session_variable WHERE session_id = " . $this->native_session_id . " AND variable_name = '" . $nm . "'";

        $objStatement = $this->objPDO->prepare($strQuery);

        $objStatement->execute();

        $row = $objStatement->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return(unserialize(base64_decode($row["variable_value"])));
        } else {
            return(false);
        }
    }

    public function __set($nm, $val)
    {
        # First remove existing value (if it has the same variable name during the same session)
        $strQuery = "DELETE FROM session_variable WHERE session_id = " . $this->native_session_id . " AND variable_name = '" . $nm . "'";
        $objStatement = $this->objPDO->query($strQuery);
        unset($objStatement);

        # Then insert it
        $strSer = base64_encode(serialize($val));
        $strQuery = "INSERT INTO session_variable(session_id, variable_name, variable_value) VALUES(" . $this->native_session_id . ", '$nm', '$strSer')";
        $objStatement = $this->objPDO->query($strQuery);
    }

    public function _session_open_method($save_path, $session_name)
    {
        # Do nothing
        return(true);
    }

    public function _session_close_method()
    {
        $this->objPDO = NULL;
        return(true);
    }

    public function _session_read_method($id)
    {
        # We use this to determine whether or not our session actually exists.
        $strUserAgent = $_SERVER["HTTP_USER_AGENT"];
        $this->php_session_id = $id;
        # Set failed flag to 1 for now
        $failed = 1;
        # See if this exists in the database or not.
        $strQuery = "SELECT id, logged_in, user_id FROM http_session WHERE ascii_session_id = '$id'";

        $objStatement = $this->objPDO->query($strQuery);

        $row = $objStatement->fetch(PDO::FETCH_ASSOC);

        if ($row)
        {
            $this->native_session_id = $row["id"];

            if ($row["logged_in"]==1)
            {
                $this->logged_in = true;
                $this->user_id = $row["user_id"];
            }
            else
            {
                $this->logged_in = false;
            }

        }
        else
        {
            $this->logged_in = false;
            # We need to create an entry in the database
            $strQuery = "INSERT INTO http_session(ascii_session_id, logged_in, user_id, created, user_agent) VALUES ('$id',0,0,now(),'$strUserAgent')";
            $objStatement = $this->objPDO->query($strQuery);

            # Now get the true ID
            $strQuery = "SELECT id FROM http_session WHERE ascii_session_id = '$id'";
            unset($objStatement);
            $objStatement = $this->objPDO->query($strQuery);

            $row = $objStatement->fetch(PDO::FETCH_ASSOC);
            $this->native_session_id = $row["id"];
        }

        # Just return empty string
        return("");
    }

    public function _session_write_method($id, $sess_data)
    {
        return(true);
    }

    public function _session_destroy_method($id)
    {
        $strQuery = "DELETE FROM http_session WHERE ascii_session_id = '$id'";
        $objStatement = $this->objPDO->query($strQuery);
        return($objStatement);
    }

    public function _session_gc_method($maxlifetime)
    {
        return(true);
    }

}
?>
