<?php

namespace App\Core;

class Router
{
    /**
     * CONFIG SETUP BRANCHES
     * /ACCESS_URL;CONTROLLER.php;HEADER;FOOTER
     * /store;app/data/router/store.php;__global__;__global__
     *
     * CONFIG SETUP TEMPLATE
     * return array(
     *     'save' => 'FILE', // 'FILE' or 'DB'
     *     'sql'  => array(
     *         'hostname' => '',
     *         'username' => '',
     *         'password' => '',
     *         'database' => '',
     *         'prefix'   => '',
     *      ),
     *
     *     'gheader'  => 'navigation',
     *     'gfooter'  => 'footer',
     *     'notfound' => array(
     *         'page'    => 'home/404',
     *         'gheader' => false,
     *         'gfooter' => false,
     *     ),
     * );
     *
     *
     * @param array       $config       Requires an array of settings (see above)
     * @param string      $request_uri  Use parse_url(\Core\BrowserInfo::fullUrl(), PHP_URL_PATH)
     * @param string|null $request_host Use parse_url(\Core\BrowserInfo::fullUrl(), PHP_URL_HOST)
     *
     * @return mixed
     */
    public static function init($config, $request_uri, $request_host = null)
    {
        if ($request_uri == '/') {
            $request_uri = '';
        }
        $request_vars = explode('/', $request_uri);
        if (!is_null($request_host)) {
            $parsed_host_url = explode(".", $request_host);
            $return['request']['branch'] = $parsed_host_url[0];
            unset($request_vars[0]);
        } else {
            $return['request']['branch'] = '/' . $request_vars[1];
            unset($request_vars[0], $request_vars[1]);
        }

        // Request variables
        foreach ($request_vars as $key => $value) {
            if (strlen($value) !== 0) {
                $return['request']['var'][] = $value;
            }
        }

        // Get branches
        if ($config['save'] == 'FILE') {
            $branch_list = file('./app/config/branches.txt');
            $branches = array();

            foreach ($branch_list as $value) {
                $branches[] = explode(';', $value);
            }

            foreach ($branches as $value) {

                if (ltrim($value[0], '/') == $return['request']['branch']) {
                    $template['target'] = $value[1];
                    $template['header'] = $value[2];
                    $template['footer'] = $value[3];
                }
            }

            if (!isset($template['target'])) {

                $template = new \App\Template\Template();
                $template->setParam('PageTitle', _('Page not found!'));
                $template->setParam('PageHeader', _('OrbitronDev'));
                if ($config['notfound']['gheader']) {
                    $template->addGeneric($config['gheader']);
                }
                $template->addGeneric($config['notfound']['page']);
                if ($config['notfound']['gfooter']) {
                    $template->addGeneric($config['gfooter']);
                }
                echo $template;
                exit;
            }
        } elseif ($config['save'] == 'DB') {
            echo 'MySQL isn\'t supported yet.';
            exit;
        } else {
            echo 'Config error';
            exit;
        }


        $_ACCESS['urlarray'] = explode('/', $template['target']);
        unset($_ACCESS['urlarray'][ count($_ACCESS['urlarray']) - 1 ]);
        $_ACCESS['basedir'] = implode('', $_ACCESS['urlarray']);
        if (substr($_ACCESS['basedir'], 0, 1) != '/') {
            $return['server']['basedir'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . '/' . $_ACCESS['basedir'];
        }

        /*
         * Acessible:
         * - $request_data['request']['branch']
         * - $request_data['request']['var']
         * - $request_data['server']['basedir']
         * - $request_data['template']['target']
         */

        //Content
        $return['template']['target'] = $template['target'];
        return $return;
    }
}