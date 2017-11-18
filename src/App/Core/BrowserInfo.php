<?php

namespace App\Core;

class BrowserInfo
{
    /**
     * @param bool $bUseForwardedHost
     *
     * @return string
     */
    public static function urlOrigin($bUseForwardedHost = false)
    {
        $server = \Kernel::getIntent()->getRequest()->server;

        $bSSL = (($server->has('HTTPS') && $server->get('HTTPS') == 'on') ? true : false);

        $sProtocol = strtolower($server->get('SERVER_PROTOCOL'));
        $sProtocol = substr($sProtocol, 0, strpos($sProtocol, '/')).(($bSSL) ? 's' : '');

        $iPort = $server->get('SERVER_PORT');
        $iPort = ((!$bSSL && $iPort == '80') || ($bSSL && $iPort == '443') ? '' : ':'.$iPort);

        $sHost = (($bUseForwardedHost && $server->has('HTTP_X_FORWARDED_HOST')) ? $server->get('HTTP_X_FORWARDED_HOST') : ($server->has('HTTP_HOST') ? $server->get('HTTP_HOST') : null));
        $sHost = (isset($sHost) ? $sHost : $server->get('SERVER_NAME').$iPort);

        return $sProtocol.'://'.$sHost;
    }

    /**
     * @param bool $bUseForwardedHost
     *
     * @return string
     */
    public static function fullUrl($bUseForwardedHost = false)
    {
        $server = \Kernel::getIntent()->getRequest()->server;

        return self::urlOrigin($bUseForwardedHost).$server->get('REQUEST_URI');
    }

    /**
     * @return string
     */
    public static function checkPhoneType()
    {
        $server = \Kernel::getIntent()->getRequest()->server;

        $bIsTablet = false;
        $bIsMobile = false;

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($server->get('HTTP_USER_AGENT')))) {
            $bIsTablet = true;
        }
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($server->get('HTTP_USER_AGENT')))) {
            $bIsMobile = true;
        }
        if ((strpos(strtolower($server->get('HTTP_ACCEPT')), 'application/vnd.wap.xhtml+xml') > 0) || (($server->has('HTTP_X_WAP_PROFILE') || $server->has('HTTP_PROFILE')))) {
            $bIsMobile = true;
        }

        $sUserAgent = strtolower(substr($server->get('HTTP_USER_AGENT'), 0, 4));
        $aMobileAgents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox', 'qwap',
            'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-',
            'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-', 'tosh',
            'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr',
            'webc', 'winw', 'winw', 'xda ', 'xda-',
        );
        if (in_array($sUserAgent, $aMobileAgents)) {
            $bIsMobile = true;
        }
        if (strpos(strtolower($server->get('HTTP_USER_AGENT')), 'opera mini') > 0) {
            $bIsMobile = true;
            //Check for tablets on opera mini alternative headers
            $sStockUserAgent = strtolower($server->has('HTTP_X_OPERAMINI_PHONE_UA') ? $server->get('HTTP_X_OPERAMINI_PHONE_UA') : ($server->has('HTTP_DEVICE_STOCK_UA') ? $server->get('HTTP_DEVICE_STOCK_UA') : ''));
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
