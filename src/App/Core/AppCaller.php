<?php

namespace App\Core;

class AppCaller
{
    private static $init_called_apps = array();

    /**
     * @param string $app
     * @param string $call
     *
     * @return bool
     */
    static function call($app, $call)
    {
        $sCallDir = 'data/' . $app . '/calls/' . $call . '.php';
        if (file_exists($sCallDir)) {
            include $sCallDir;
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    static function onInit()
    {
        $init_name = 'APPMANAGER_CALLS_ONINIT';
        if (!isset($init_name) || isset($init_name) && !$$init_name) {
            define($init_name, true);
            foreach (scandir('src/') as $app) {
                if (self::call($app, 'onInit')) {
                    array_merge(self::$init_called_apps, $app);
                }
            }
            return true;
        }
        return false;
    }
}