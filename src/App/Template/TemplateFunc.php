<?php

namespace App\Template;

class TemplateFunc
{
    /**
     * @param      $string
     * @param bool $ignoreHtml
     * @param bool $nl2br
     *
     * @return string
     */
    public static function cleanStringForOutput($string, $ignoreHtml = false, $nl2br = false)
    {
        $string = stripslashes(trim($string));

        if (!$ignoreHtml) {
            $string = htmlentities($string);
        }

        if ($nl2br) {
            $string = nl2br($string);
        }

        return $string;
    }

    /**
     * @param      $string
     * @param bool $allowLB
     *
     * @return mixed
     */
    public static function filterSpecialChars($string, $allowLB = false)
    {
        $string = str_replace(chr(1), ' ', $string);
        $string = str_replace(chr(2), ' ', $string);
        $string = str_replace(chr(3), ' ', $string);
        $string = str_replace(chr(9), ' ', $string);

        if (!$allowLB) {
            $string = str_replace(chr(13), ' ', $string);
        }

        return $string;
    }

    /**
     * @param $string
     *
     * @return string
     */
    public static function filterInputString($string)
    {
        return stripslashes(trim($string));
    }
}
