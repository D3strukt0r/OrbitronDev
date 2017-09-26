<?php

namespace App\Template;

class Template
{
    private $output_data = '';
    public $params = array();
    private $include_files = array();

    const INCLUDE_SETS_DIR = './app/data/template/include_sets';
    const VIEWS_DIR = './app/views';

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->setParam('PageTitle', ' ');
        $this->SetParam('www', '');
        $this->SetParam('cdn', '');
    }

    /**
     * @param string $template_name
     */
    public function addGeneric($template_name)
    {
        $template = new TemplateLoad($template_name, $this->params);
        $this->output_data .= $template->__getHtml();
    }

    /**
     * @param \App\Template\TemplateLoad $template
     */
    public function addTemplate($template)
    {
        $this->output_data .= $template->__getHtml();
    }

    /**
     * @param string $template_name
     *
     * @return bool
     */
    public static function templateExists($template_name)
    {
        $sTemplateFileDir = self::VIEWS_DIR . '/' . $template_name . '.phtml';

        if (file_exists($sTemplateFileDir)) {
            return true;
        }
        return false;
    }

    /**
     * Uses .txt as reference to get all sources at once
     *
     * Create a "example.txt" file in the "include_sets" directory
     *
     * add following example entity's:
     * text/css;{{cdn}}/bootstrap/current/css/bootstrap.min.css;stylesheet;
     * text/javascript;{{cdn}}/jquery/current/js/jquery.min.js;;
     *
     * @param string $include_set
     */
    function addIncludeSet($include_set)
    {
        if (file_exists(self::INCLUDE_SETS_DIR . '/' . $include_set . '.txt')) {
            $includes = file(self::INCLUDE_SETS_DIR . '/' . $include_set . '.txt');
            foreach ($includes as $include_info) {
                $include_info = explode(';', $include_info);
                $this->addIncludeFile($include_info[0], $include_info[1], $include_info[2], $include_info[3]);
            }
        }
    }

    /**
     * @param string $type
     * @param string $src
     * @param string $rel
     * @param string $name
     */
    function addIncludeFile($type, $src, $rel = '', $name = '')
    {
        $this->include_files[] = array('type' => $type, 'src' => $src, 'rel' => $rel, 'name' => $name);
    }

    function writeIncludeFiles()
    {
        foreach ($this->include_files as $inc) {
            switch ($inc['type']) {
                case 'application/rss+xml':

                    $this->write('<link rel="' . $inc['rel'] . '" href="' . $inc['src'] . '" type="' . $inc['type'] . '" title="' . $inc['name'] . '" />');
                    break;

                case 'text/javascript':

                    $this->write('<script src="' . $inc['src'] . '" type="text/javascript"></script>');
                    break;

                case 'text/css':

                    $this->write('<link rel="' . $inc['rel'] . '" href="' . $inc['src'] . '" type="' . $inc['type'] . '" />');
                    break;

                default:

                    break;
            }
        }
        $this->include_files = array();
    }

    /**
     * @param string $param
     * @param string $value
     */
    function setParam($param, $value)
    {
        $this->params[$param] = (is_object($value) ? $value->fetch() : $value);
    }

    /**
     * @param string $param
     */
    function unsetParam($param)
    {
        unset($this->params[$param]);
    }

    /**
     * @param string $string
     */
    function write($string)
    {
        $this->output_data .= $string;
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    function filterParams($string)
    {
        foreach ($this->params as $param => $value) {
            if (!is_string($value) || !is_numeric($value)) {
                continue;
            }
            $string = str_ireplace('{{' . $param . '}}', $value, $string);
        }
        return $string;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return (string)$this->filterParams($this->output_data);
    }
}
