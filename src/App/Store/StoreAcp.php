<?php

namespace App\Store;

class StoreAcp
{
    private static $aAcpGroupElements = array();
    private static $aAcpMenuElements = array();

    /**
     * @param $group_info
     */
    public static function addGroup($group_info)
    {
        $aDefaultInfo = array(
            'parent'  => 'root',
            'id'      => '',
            'title'   => '',
            'icon'    => '',
            'display' => 'true',
        );
        $aGroupInfo = array_replace($aDefaultInfo, $group_info);

        self::$aAcpGroupElements[] = $aGroupInfo;
    }

    /**
     * @param $menu_info
     */
    public static function addMenu($menu_info)
    {
        $aDefaultInfo = array(
            'parent' => '',
            'id'     => '',
            'title'  => '',
            'href'   => '',
            'icon'   => '',
            'meta'   => array(
                'tabindex' => -1,
            ),
        );
        $aMenuInfo = array_replace($aDefaultInfo, $menu_info);

        self::$aAcpMenuElements[] = $aMenuInfo;
    }

    /**
     *
     */
    public static function includeLibs()
    {

        $sLibDir = \Kernel::getIntent()->getRootDir().'/src/App/Store/addons';

        $aGetLibs = scandir($sLibDir);
        foreach ($aGetLibs as $ACPLibrary) {
            $sLibFile = $sLibDir . '/' . $ACPLibrary;

            if ($ACPLibrary !== '.' && $ACPLibrary !== '..' && is_file($sLibFile)) {
                include_once($sLibFile);
            }
        }
    }

    /**
     * @return array
     */
    public static function getAllGroups()
    {
        return self::$aAcpGroupElements;
    }

    /**
     * @param $group_id
     *
     * @return array
     */
    public static function getAllMenus($group_id)
    {
        $aReturnMenus = array();
        foreach (self::$aAcpMenuElements as $sMenu => $aMenuInfo) {
            if ($aMenuInfo['parent'] == $group_id) {
                $aReturnMenus[] = self::$aAcpMenuElements[$sMenu];
            }
        }
        return $aReturnMenus;
    }
}
