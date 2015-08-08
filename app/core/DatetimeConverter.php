<?php

/**
 * Class DatetimeConverter
 * This class is used to convert datetime into a specific formats
 */
class DatetimeConverter
{
    /**
     * Converts $datetime into a provided format
     * @param $datetime the provided datetime
     * @param string $format the requested format
     * @return string the converted format of datetime
     */
    public static function getUserFriendlyDateTimeFormat($datetime, $format = 'j F Y, H:i')
    {
        # Old format (just as a reminder): $format = 'd-m-Y H:i'
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $date->format($format);
    }

    /**
     * Coverts $datetime into Google friendly format
     * @param $datetime the provided datetime
     * @return string the Google friendly datetime
     */
    public static function getGoogleAuthDateTimeFormat($datetime)
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $date->format("Y-m-d\TH:i:s");
    }

}