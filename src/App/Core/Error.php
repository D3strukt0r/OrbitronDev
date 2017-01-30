<?php

namespace App\Core;

class Error
{
    private $aMessages = array();

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public static function setMessage($key, $value)
    {
        $_SESSION['messages'][$key] = $value;

        if (isset($_SESSION['messages'][$key])) {
            return true;
        }
        return false;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public static function getMessage($key)
    {
        return $_SESSION['messages'][$key];
    }

    /**
     * @param $key
     *
     * @return null
     */
    public static function gotMessage($key)
    {
        if (isset($_SESSION['messages'][$key])) {
            $value = $_SESSION['messages'][$key];
            unset($_SESSION['messages'][$key]);
            return $value;
        }
        return null;
    }
}