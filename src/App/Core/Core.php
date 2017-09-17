<?php

namespace App\Core;

use Container\DatabaseContainer;

class Core
{
    /**
     * @return bool
     * @throws \Exception
     */
    public static function getMaintenanceStatus()
    {
        $database = DatabaseContainer::getDatabase();

        $oGetMaintenance = $database->prepare('SELECT `val` FROM `app_config` WHERE `setting`=\'maintenance\' LIMIT 1');
        $oGetMaintenance->execute();
        $oGetMaintenanceData = $oGetMaintenance->fetchAll(\PDO::FETCH_ASSOC);
        $bMaintenance = ($oGetMaintenanceData[0]['maintenance'] == '0' ? false : true);
        return $bMaintenance;
    }

    /**
     * @param $seed
     *
     * @return string
     */
    public static function generateTicket($seed)
    {
        $ticket = 'ST-';
        $ticket .= sha1($seed . 'OrbitronDev' . rand(118, 283));
        $ticket .= '-' . rand(100, 255);
        $ticket .= '-orbitrondev-fe' . rand(0, 5);
        return $ticket;
    }

    /**
     * @param $array
     *
     * @return bool
     */
    public static function shuffleAssoc(&$array)
    {
        $keys = array_keys($array);

        shuffle($keys);

        $new = array();
        foreach ($keys as $key) {
            $new[$key] = $array[$key];
        }

        $array = $new;

        return true;
    }

}
