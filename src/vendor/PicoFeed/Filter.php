<?php

namespace PicoFeed;

class Filter
{
    private $data = '';
    private $url = '';
    private $input = '';
    private $empty_tag = false;
    private $strip_content = false;

    public $ignored_tags = array();

    public $allowed_tags = array(
        'h2' => array(),
        'h3' => array(),
        'h4' => array(),
        'h5' => array(),
        'h6' => array(),
        'strong' => array(),
        'em' => array(),
        'code' => array(),
        'pre' => array(),
        'blockquote' => array(),
        'p' => array(),
        'ul' => array(),
        'li' => array(),
        'ol' => array(),
        'br' => array(),
        'del' => array(),
        'a' => array('href'),
        'img' => array('src', 'width', 'height')
    );

    public $strip_tags_content = array(
        'script'
    );

    public $allowed_protocols = array(
        'http://',
        'https://',
        'ftp://',
        'mailto://',
        '//'
    );

    public $protocol_attributes = array(
        'src',
        'href',
    );

    public $blacklist_media = array(
        'feeds.feedburner.com',
        'feedsportal.com',
        'rss.nytimes.com',
        'feeds.wordpress.com',
        'stats.wordpress.com'
    );

    public $required_attributes = array(
        'a' => array('href'),
        'img' => array('src')
    );


    public function __construct($data, $url)
    {
        $this->url = $url;

        // Convert bad formatted documents to XML
        $dom = new \DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8">'.$data);
        $this->input = $dom->saveXML($dom->getElementsByTagName('body')->item(0));
    }


    public function execute()
    {
        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, 'startTag', 'endTag');
        xml_set_character_data_handler($parser, 'dataTag');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);

        if (! xml_parse($parser, $this->input, true)) {

            var_dump($this->input);
            die(xml_get_current_line_number($parser).'|'.xml_error_string(xml_get_error_code($parser)));
        }

        xml_parser_free($parser);

        return $this->data;
    }


    public function startTag($parser, $name, $attributes)
    {
        $this->empty_tag = false;
        $this->strip_content = false;

        if ($this->isPixelTracker($name, $attributes)) {

            $this->empty_tag = true;
        }
        else if ($this->isAllowedTag($name)) {

            $attr_data = '';
            $used_attributes = array();

            foreach ($attributes as $attribute => $value) {

                if ($this->isAllowedAttribute($name, $attribute)) {

                    if ($this->isResource($attribute)) {

                        if ($this->isRelativePath($value)) {

                            $attr_data .= ' '.$attribute.'="'.$this->getAbsoluteUrl($value, $this->url).'"';
                            $used_attributes[] = $attribute;
                        }
                        else if ($this->isAllowedProtocol($value) && ! $this->isBlacklistMedia($value)) {

                            $attr_data .= ' '.$attribute.'="'.$value.'"';
                            $used_attributes[] = $attribute;
                        }
                    }
                    else {

                        $attr_data .= ' '.$attribute.'="'.$value.'"';
                        $used_attributes[] = $attribute;
                    }
                }
            }

            if (isset($this->required_attributes[$name])) {

                foreach ($this->required_attributes[$name] as $required_attribute) {

                    if (! in_array($required_attribute, $used_attributes)) {

                        $this->empty_tag = true;
                        break;
                    }
                }
            }

            if (! $this->empty_tag) {

                $this->data .= '<'.$name.$attr_data;

                if ($name !== 'img' && $name !== 'br') $this->data .= '>';
            }
        }
        else {

            $this->ignored_tags[] = $name;
        }

        if (in_array($name, $this->strip_tags_content)) {

            $this->strip_content = true;
        }
    }


    public function endTag($parser, $name)
    {
        if (! $this->empty_tag && $this->isAllowedTag($name)) {

            $this->data .= $name !== 'img' && $name !== 'br' ? '</'.$name.'>' : '/>';
        }
    }


    public function dataTag($parser, $content)
    {
        if (! $this->strip_content) $this->data .= htmlspecialchars($content, ENT_QUOTES, 'UTF-8', false);
    }


    public function getAbsoluteUrl($path, $url)
    {
        $components = parse_url($url);

        if ($path{0} === '/') {

            // Absolute path
            return $components['scheme'].'://'.$components['host'].$path;
        }
        else {

            // Relative path

            $url_path = $components['path'];

            if ($url_path{strlen($url_path) - 1} !== '/') {

                $url_path = dirname($url_path).'/';
            }

            if (substr($path, 0, 2) === './') {

                $path = substr($path, 2);
            }

            return $components['scheme'].'://'.$components['host'].$url_path.$path;
        }
    }


    public function isRelativePath($value)
    {
        return strpos($value, '://') === false && strpos($value, '//') !== 0;
    }


    public function isAllowedTag($name)
    {
        return isset($this->allowed_tags[$name]);
    }


    public function isAllowedAttribute($tag, $attribute)
    {
        return in_array($attribute, $this->allowed_tags[$tag]);
    }


    public function isResource($attribute)
    {
        return in_array($attribute, $this->protocol_attributes);
    }


    public function isAllowedProtocol($value)
    {
        foreach ($this->allowed_protocols as $protocol) {

            if (strpos($value, $protocol) === 0) {

                return true;
            }
        }

        return false;
    }


    public function isBlacklistMedia($resource)
    {
        foreach ($this->blacklist_media as $name) {

            if (strpos($resource, $name) !== false) {

                return true;
            }
        }

        return false;
    }


    public function isPixelTracker($tag, array $attributes)
    {
        return $tag === 'img' &&
                isset($attributes['height']) && isset($attributes['width']) &&
                $attributes['height'] == 1 && $attributes['width'] == 1;
    }
}
