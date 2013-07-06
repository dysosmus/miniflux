<?php

namespace PicoFeed;

class Logging
{
    public static $messages = array();

    public static function log($message)
    {
        self::$messages[] = $message;
    }
}