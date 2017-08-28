<?php

// TODO: Check if this works

namespace App\Template;

/**
 * Requires JSON Translator file. Check function "IncludeLanguageFile"
 *
 * Class LanguageAlternative
 *
 * @package App\Template
 */
class LanguageAlternative
{
    private static $aLanguage = array();

    const LOCALES_DIR = './app/data/locale';

    static function RedirIfNotSet($config)
    {
        if ($config['languageMode'] == 'get') {
            if (!isset($_GET['l'])) {
                if (!isset($_GET['c'])) {
                    header('Location: ' . ($config['https'] ? 'https://' : 'http://') . $config['forceSubDomain'] . '.' . $config['domain'] . '/?c=ww&l=en');
                    exit;
                }
                header('Location: ' . ($config['https'] ? 'https://' : 'http://') . $config['forceSubDomain'] . '.' . $config['domain'] . '/?c=' . $_GET['c'] . '&l=en');
                exit;
            }
            if (!isset($_GET['c'])) {
                if (!isset($_GET['l'])) {
                    header('Location: ' . ($config['https'] ? 'https://' : 'http://') . $config['forceSubDomain'] . '.' . $config['domain'] . '/?c=ww&l=en');
                    exit;
                }
                header('Location: ' . ($config['https'] ? 'https://' : 'http://') . $config['forceSubDomain'] . '.' . $config['domain'] . '/?c=ww&l=' . $_GET['l']);
                exit;
            }
        } elseif ($config['languageMode'] == 'cookie') {
            if (!isset($_COOKIE['language'])) {
                setcookie('language', 'en', time() + 31536000, '/', '.' . $config['domain']);
            }
            if (!isset($_COOKIE['country'])) {
                setcookie('country', 'ww', time() + 31536000, '/', '.' . $config['domain']);
            }
        }
    }

    static function DefineLanguage($config)
    {
        if ($config['languageMode'] == 'get') {
            define('LANGUAGE', $_GET['l']);
            define('COUNTRY', $_GET['c']);
        } elseif ($config['languageMode'] == 'cookie') {
            define('LANGUAGE', (isset($_COOKIE['language']) ? $_COOKIE['language'] : 'en'));
            define('COUNTRY', (isset($_COOKIE['country']) ? $_COOKIE['country'] : 'en'));
        }
    }

    /**
     * Include the language file
     *
     * Create a "en.json" file in the "locale" directory
     *
     * Add following example content:
     * {
     *     "page.hello_name" : "Hello {0}, how are you?",
     *     "page.pageIndicate" : "Page {0} from {1}"
     * }
     *
     *
     * @param $sLangCode
     *
     * @return mixed
     */
    static function IncludeLanguageFile($sLangCode)
    {
        $sLangFile = self::LOCALES_DIR . '/' . $sLangCode . '.json';
        if (file_exists($sLangFile)) {
            $aLanguage = json_decode(file_get_contents($sLangFile), true);
        } else {
            $aLanguage = json_decode(file_get_contents(self::LOCALES_DIR . '/en.json'),
                true);
        }
        self::$aLanguage = $aLanguage;
        return $aLanguage;
    }

    static function Get($sLangKey)
    {
        $sLangString = $sLangKey;
        if (isset(self::$aLanguage[$sLangKey])) {
            $sLangString = self::$aLanguage[$sLangKey];
        }

        $iNumArgs = func_num_args();

        if ($iNumArgs > 1) {
            $sFirstArg = func_get_arg(1);
            if (is_array($sFirstArg)) {
                foreach ($sFirstArg as $sLangKey => $sLangValue) {
                    $sLangString = str_replace('{' . $sLangKey . '}', $sLangValue, $sLangString);
                }
            } else {
                for ($i = 1; $i < $iNumArgs; $i++) {
                    $sGivenParam = func_get_arg($i);
                    $sLangString = str_replace('{' . ($i - 1) . '}', $sGivenParam, $sLangString);
                }
            }
        }

        return $sLangString;
    }
}