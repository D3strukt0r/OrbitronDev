<?php

namespace App\Template;

class Language
{
    private static $sLanguage = 'en';
    private static $sCountry = 'US';

    /**
     * @param null $default_cookie_var
     */
    public static function setupCookie($default_cookie_var = null)
    {
        if (isset($_COOKIE['lang'])) {
            self::update();
            self::setConstants(self::$sLanguage, self::$sCountry);
            return;
        } else {
            if (!is_null($default_cookie_var)) {
                self::defineLanguage('en_US', $default_cookie_var);
            } else {
                self::defineLanguage('en_US');
            }
        }
    }

    /**
     *
     */
    private static function update()
    {
        $aLangAndCountry = explode('-', $_COOKIE['lang']);
        self::$sLanguage = $aLangAndCountry[0];
        self::$sCountry = $aLangAndCountry[1];
    }

    /**
     * @param      $language
     * @param null $default_cookie_var
     */
    public static function defineLanguage($language, $default_cookie_var = null)
    {
        $language_array = explode('_', $language);
        $language = $language_array[0];
        $country = (isset($language_array[1]) ? $language_array[1] : 'US');

        if (!is_null($default_cookie_var)) {
            setcookie('lang', $language . '-' . $country, strtotime('+1 month'), $default_cookie_var['path'],
                $default_cookie_var['domain']);
        } else {
            setcookie('lang', $language . '-' . $country, strtotime('+1 month'));
        }
        self::$sLanguage = $language;
        self::$sCountry = $country;
        self::setConstants($language, $country);
    }

    /**
     * @param $language
     * @param $country
     */
    private static function setConstants($language, $country)
    {
        define('TEMPLATE_LANGUAGE', $language);
        define('TEMPLATE_COUNTRY', $country);
    }
}