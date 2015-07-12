<?php

class GoogleAuth
{
    protected $client;
    public static $auth;

    private static $instance;

    private function __construct()
    {
        # Load config
        $file = file_get_contents("app/config.json");
        $config = json_decode($file);

        # Start up the login
        require_once 'app/google-api-php-client/src/Google/autoload.php';
        # Create google client
        $client = new Google_Client();

        $this->client = $client;
        $this->client->setClientId($config->{'google_auth'}->{'client_id'});
        $this->client->setClientSecret($config->{'google_auth'}->{'client_secret'});
        $this->client->setDeveloperKey($config->{'google_auth'}->{'developer_key'});

        $this->client->setRedirectUri(SITE_URL.$config->{'google_auth'}->{'redirect_uri'});

        $this->client->addScope("https://www.googleapis.com/auth/calendar");
        $this->client->addScope("https://www.googleapis.com/auth/userinfo.email");
        $this->client->setAccessType('offline');
    }

    public function checkIfExpired()
    {
        if($this->client->isAccessTokenExpired())
        {
            // Get a new token based on the refresh token acquired
            // during the first authentication
            #$google_token = json_decode($_SESSION['access_token']);
            #$this->client->refreshToken($google_token->refresh_token);

            // Save new token to SESSION
            #$_SESSION['access_token']= $client->getAccessToken();
            return true;
        }
        else
            return false;
    }

    public static function getInstance()
    {
        if(!isset(GoogleAuth::$instance))
            GoogleAuth::$instance = new GoogleAuth();

        # Set auth code
        GoogleAuth::$auth = HTTPSession::getInstance()->ACCESS_TOKEN;

        # If auth code is not empty, it means that user was successfully signed in
        # using Google sign in, therefore set token for client and authorise using HTTPSession
        if(!empty(GoogleAuth::$auth))
        {
            GoogleAuth::$instance->setToken();

            # TODO: First we need to check if session is still valid
            if(GoogleAuth::$instance->checkIfExpired())
            {
                echo "Google session expired";
                die();
            }

            HTTPSession::getInstance()->LoginGoogle(GoogleAuth::$instance->getUserEmail());
        }

        return GoogleAuth::$instance;
    }

    public function checkCode($code)
    {
        $this->client->authenticate($_GET['code']);
        HTTPSession::getInstance()->ACCESS_TOKEN = $this->client->getAccessToken();
        $this->setToken();
    }

    /**
     * Saves refresh token to database in order to retrieve it
     * when token expires
     * @return mixed
     */
    public function saveRefreshToken()
    {
        $google_token = json_decode(HTTPSession::getInstance()->ACCESS_TOKEN);
        return $google_token->refresh_token;
    }

    private function setToken()
    {
        $this->client->setAccessToken(HTTPSession::getInstance()->ACCESS_TOKEN);
    }

    public function getUserEmail()
    {
        # Get ID token from session
        $idToken = json_decode(HTTPSession::getInstance()->ACCESS_TOKEN)->{'id_token'};

        # Get user payload
        $payload = $this->client->verifyIdToken($idToken)->getAttributes()['payload'];

        # And email from it
        $email = $payload['email'];

        return $email;
    }

    public function isLoggedIn()
    {
        if(!empty(self::$auth)) {
            $idToken = json_decode(self::$auth)->{'id_token'};
            # If ID token is verified correctly, user is still logged in
            if ($this->client->verifyIdToken($idToken))
                return true;
            else
                return false;
        }
        else
            return false;
    }

    public function getAuthLink()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * @param $users
     * @param string $datetimeStart
     * @param string $datetimeEnd
     * @param array $attendees
     * @param null $recurrence
     * @param string $timeZone
     * @return String eventID
     */
    public function addEventToCalendar($project, $users, $datetimeStart = "0000-00-00T00:00:00", $datetimeEnd = "0000-00-00T00:00:00", $attendees = array(array('email' => 'cieslaremil@gmail.com')), $recurrence = null, $timeZone = "Europe/London") {

        # If we want the event to reoccur
        if($recurrence)
            $recurrence = 'RRULE:FREQ=WEEKLY;COUNT='.$recurrence;
        else
            $recurrence = array();

        $service = new Google_Service_Calendar($this->client);

        $event = new Google_Service_Calendar_Event(array(
            'summary' => 'GU Project Meeting ('.$project.'): '.$users,
            'location' => '',
            'description' => '',
            'start' => array(
                'dateTime' => $datetimeStart,
                'timeZone' => $timeZone,
            ),
            'end' => array(
                'dateTime' => $datetimeEnd,
                'timeZone' => $timeZone,
            ),
            'recurrence' => array(
                $recurrence
            ),
            'attendees' => $attendees,
            'reminders' => array(
                'useDefault' => FALSE,
                'overrides' => array(
                    array('method' => 'email', 'minutes' => 24 * 60)
                ),
            ),
        ));

        # Add event, provide calendar ID, which is email and $event object
        $event = $service->events->insert($this->getUserEmail(), $event);

        #printf('Event created: %s\n', $event->htmlLink);

        return $event->id;
    }

    public function editEventInCalendar($eventId, $datetimeStart = "0000-00-00T00:00:00", $datetimeEnd = "0000-00-00T00:00:00", $recurrence = null, $timeZone = "Europe/London")
    {
        # If we want the event to reoccur
        if($recurrence)
            $recurrence = 'RRULE:FREQ=WEEKLY;COUNT='.$recurrence;
        else
            $recurrence = array();

        $service = new Google_Service_Calendar($this->client);

        $event = $service->events->get($this->getUserEmail(), $eventId);

        $eventDatetimeStart = new Google_Service_Calendar_EventDateTime();

        $eventDatetimeStart->setDateTime($datetimeStart);
        $eventDatetimeStart->setTimeZone($timeZone);

        $event->setStart($eventDatetimeStart);

        $eventDatetimeEnd = new Google_Service_Calendar_EventDateTime();

        $eventDatetimeEnd->setDateTime($datetimeEnd);
        $eventDatetimeEnd->setTimeZone($timeZone);

        $event->setEnd($eventDatetimeEnd);

        $event->setRecurrence(array($recurrence));

        $updatedEvent = $service->events->update($this->getUserEmail(), $event->getId(), $event);

        return $updatedEvent->id;

    }
}
