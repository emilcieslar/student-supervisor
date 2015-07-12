<?php

class DatetimeConverter
{
    public static function getUserFriendlyDateTimeFormat($datetime, $format = 'd-m-Y H:i')
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $date->format($format);
    }

    public static function getGoogleAuthDateTimeFormat($datetime)
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $date->format("Y-m-d\TH:i:s");
    }
}