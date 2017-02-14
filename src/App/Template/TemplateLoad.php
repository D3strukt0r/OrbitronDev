<?php

namespace App\Template;

use App\Core\TranslatorService;

class TemplateLoad
{
    private $template_name = '';
    private $templateData = null;
    private $params = array();

    /**
     * TemplateLoad constructor.
     *
     * @param       $template_name
     * @param array $params
     */
    public function __construct($template_name, $params = array())
    {
        $this->template_name = $template_name;
        $this->params = $params;
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public function __filterParams($string)
    {
        foreach ($this->params as $param => $value) {
            if (is_object($value)) {
                continue;
            }
            $string = str_ireplace('{{' . $param . '}}', $value, $string);
        }
        return $string;
    }

    /**
     * @return mixed
     */
    public function __getHtml()
    {
        if (!is_null($this->templateData)) {
            return $this->__filterParams($this->templateData);
        }

        $template_dir = './app/views/' . $this->template_name . '.phtml';

        if (!file_exists($template_dir)) {
            throw new \InvalidArgumentException('[Template][Fatal Error]: ' . 'Could not load template: ' . $this->template_name);
        }

        ob_start();
        require($template_dir);
        $template_data = ob_get_contents();
        ob_end_clean();

        return $this->__filterParams($template_data);
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $templateData = $html;
        if (is_callable($html)) {
            ob_start();
            $html($this->template_name);
            $templateData = ob_get_contents();
            ob_end_clean();
        }
        $this->templateData = $templateData;
    }

    /**
     * @param $param
     * @param $value
     */
    public function __setParam($param, $value)
    {
        $this->params[$param] = is_object($value) ? $value->fetch() : $value;
    }

    /**
     * @param $param
     */
    public function __unsetParam($param)
    {
        unset($this->params[$param]);
    }

    /************ TEMPLATE API ****************/
    /**
     * @param $text
     *
     * @return string
     */
    public function translate($text)
    {
        /** @var \Symfony\Component\Translation\Translator $translator */
        $translator = TranslatorService::$service;
        return $translator->trans($text);
    }

    /**
     * @return string
     */
    public function doctype()
    {
        return '<!DOCTYPE html>';
    }

    /**
     * @param      $url
     * @param null $domain
     *
     * @return string
     */
    public function basePath($url, $domain = null)
    {
        $uri = '';
        if (!is_null($domain)) {
            $uri .= $domain;
        }
        $uri .= '/' . $url;
        return $uri;
    }

    /**
     * @param      $url
     * @param null $domain
     *
     * @return string
     */
    public function url($url, $domain = null)
    {
        $uri = '';
        if (!is_null($domain)) {
            $uri .= $domain;
        }
        $uri .= '/' . $url;
        return $uri;
    }

    /**
     * @param $text
     *
     * @return string
     */
    public function escapeHtml($text)
    {
        return htmlentities($text);
    }

    /************ TEMPLATE FUNCTIONS ************/
    /**
     * @param $text
     */
    public function headTitle($text)
    {
        $this->title .= $text;
    }
}