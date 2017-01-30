<?php

namespace App\Core;

class BrowserInfo
{
    /**
     * @param bool $bUseForwardedHost
     *
     * @return string
     */
    static function urlOrigin($bUseForwardedHost = false)
    {
        $bSSL = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false);

        $sProtocol = strtolower($_SERVER['SERVER_PROTOCOL']);
        $sProtocol = substr($sProtocol, 0, strpos($sProtocol, '/')) . (($bSSL) ? 's' : '');

        $iPort = $_SERVER['SERVER_PORT'];
        $iPort = ((!$bSSL && $iPort == '80') || ($bSSL && $iPort == '443') ? '' : ':' . $iPort);

        $sHost = (($bUseForwardedHost && isset($_SERVER['HTTP_X_FORWARDED_HOST'])) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null));
        $sHost = (isset($sHost) ? $sHost : $_SERVER['SERVER_NAME'] . $iPort);
        return $sProtocol . '://' . $sHost;
    }

    /**
     * @param bool $bUseForwardedHost
     *
     * @return string
     */
    static function fullUrl($bUseForwardedHost = false)
    {
        return self::urlOrigin($bUseForwardedHost) . $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string
     */
    static function checkPhoneType()
    {
        $bIsTablet = false;
        $bIsMobile = false;

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i',
            strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $bIsTablet = true;
        }
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i',
            strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $bIsMobile = true;
        }
        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),
                    'application/vnd.wap.xhtml+xml') > 0) || ((isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])))
        ) {
            $bIsMobile = true;
        }

        $sUserAgent = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $aMobileAgents = array(
            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
            'newt','noki','palm','pana','pant','phil','play','port','prox','qwap',
            'sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-',
            'siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh',
            'tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp','wapr',
            'webc','winw','winw','xda ','xda-'
        );
        if (in_array($sUserAgent, $aMobileAgents)) {
            $bIsMobile = true;
        }
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'opera mini') > 0) {
            $bIsMobile = true;
            //Check for tablets on opera mini alternative headers
            $sStockUserAgent = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : (isset($_SERVER['HTTP_DEVICE_STOCK_UA']) ? $_SERVER['HTTP_DEVICE_STOCK_UA'] : ''));
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $sStockUserAgent)) {
                $bIsTablet = true;
            }
        }

        if ($bIsTablet) {
            return 'tablet';
        } elseif ($bIsMobile) {
            return 'mobile';
        } else {
            return 'desktop';
        }
    }
}