<?php

namespace App\Account;

use Kernel;

class AccountAcp
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
        $sLibDir = Kernel::$rootDir2 . '/src/App/Account/addons';

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

    // TODO: This is a new function, integrate it in HTML
    /**
     * @param string $page_name
     *
     * @return string
     */
    public static function changePage($page_name)
    {
        $page_changer = 'url_js'; // 'href', 'hash', 'url', 'url_js'

        if ($page_changer === 'js') {
            return 'href="javascript:ControlPanel.changePage(\'' . $page_name . '\')" data-toggle="page"';
        } elseif ($page_changer === 'url_js') {
            return 'href="javascript:ControlPanel.changePage(\'' . $page_name . '\', true)" data-toggle="page"';
        } elseif ($page_changer === 'hash') {
            return 'href="#/' . $page_name . '"';
        } elseif ($page_changer === 'url') {
            return 'href="https://account.orbitrondev.org/panel/' . $page_name . '"';
        } else {
            return '';
        }
    }
}
