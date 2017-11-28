<?php

namespace App\Template;

class Language
{
    private static $sLanguage = 'en';
    private static $sCountry = 'US';

    private static $cookieName = '_locale';

    /**
     * @param string|null $language
     * @param array|null  $default_cookie_var
     */
    public static function setupCookie($language = null, $default_cookie_var = null)
    {
        if (\Kernel::getIntent()->getRequest()->cookies->has(self::$cookieName)) {
            self::update($language, $default_cookie_var);
        } else {
            if (!is_null($language)) {
                self::defineLanguage($language, $default_cookie_var);
            } else {
                self::defineLanguage('en_US', $default_cookie_var);
            }
        }
    }

    /**
     * @param string|null $locale
     * @param array|null  $default_cookie_var
     */
    private static function update($locale = null, $default_cookie_var = null)
    {
        $localeInCookie = explode('-', \Kernel::getIntent()->getRequest()->cookies->get(self::$cookieName));

        if (!is_null($locale)) {
            $localeGiven = explode('-', $locale);
            if ($localeGiven[0] != $localeInCookie[0]) {
                self::defineLanguage($locale, $default_cookie_var);

                return;
            }
        }

        self::$sLanguage = $localeInCookie[0];
        self::$sCountry = $localeInCookie[1];
        self::setConstants(self::$sLanguage, self::$sCountry);
    }

    /**
     * @param string     $language
     * @param array|null $default_cookie_var
     */
    public static function defineLanguage($language, $default_cookie_var = null)
    {
        $language_array = explode('_', $language);
        $language = $language_array[0];
        $country = (isset($language_array[1]) ? $language_array[1] : 'US');
        $languageStr = $language.'-'.$country;

        if (!is_null($default_cookie_var) && is_array($default_cookie_var)) {
            setcookie(self::$cookieName, $languageStr, strtotime('+1 month'), $default_cookie_var['path'], $default_cookie_var['domain']);
        } else {
            setcookie(self::$cookieName, $languageStr, strtotime('+1 month'));
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
