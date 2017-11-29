<?php

namespace App\Core;

// TODO: This file shouldn't exist in the future

class Server
{
    // URL('/example', 'page=example', false)
    public static function url($path, $params = array(), $get_current = false)
    {
        $new_scheme = 'http://';
        $new_host = 'localhost';
        $new_port = 80;
        $new_path = '/';
        $new_query = '';
        $new_fragment = '';

        // get current uri
        if ($get_current === true) {
            $current_uri = BrowserInfo::fullUrl();
            $new_scheme = parse_url($current_uri, PHP_URL_SCHEME) . '://';
            $new_host = parse_url($current_uri, PHP_URL_HOST);
            $new_port = (int)parse_url($current_uri, PHP_URL_PORT);
            $new_path = parse_url($current_uri, PHP_URL_PATH);
            $new_query = parse_url($current_uri, PHP_URL_QUERY);
            $new_fragment = parse_url($current_uri, PHP_URL_FRAGMENT);
        }
        // change path
        if (!is_null($path)) {
            $new_path = $path;
        }
        // change get parameters
        if (!is_null($params) && count($params) !== 0) {
            if (is_array($params)) {
                // get current query (if $get_current is false it wont get any data - just a clear string)
                $current_query = array();
                if ($get_current && count($new_query) > 0) {
                    $query_vars = explode('&', $new_query);
                    foreach ($query_vars as $string) {
                        $var_string = explode('=', $string);
                        $current_query[$var_string[0]] = $var_string[1]; // "page=example" now $current_query["page"] = "example"
                    }
                }

                // insert new query
                foreach ($params as $key => $value) {
                    $current_query[$key] = $value;
                }

                // save new query
                $new_var_string = array();
                foreach ($current_query as $key => $value) {
                    $new_var_string[] = implode('=', array($key, $value));
                }
                $new_query = implode('&', $new_var_string);
            } elseif (is_string($params)) {
                $new_query = $params;
            }
        }

        // build url
        $new_uri = $new_scheme . $new_host . ':' . $new_port . $new_path . '?' . $new_query . '#' . $new_fragment;
        return $new_uri;
    }
}
