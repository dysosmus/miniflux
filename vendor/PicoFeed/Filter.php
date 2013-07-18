<?php

namespace PicoFeed;

class Filter
{
    private $data = '';
    private $url = '';
    private $input = '';
    private $empty_tags = array();
    private $strip_content = false;

    public $allowed_tags = array(
        'dt' => array(),
        'dd' => array(),
        'dl' => array(),
        'table' => array(),
        'caption' => array(),
        'tr' => array(),
        'th' => array(),
        'td' => array(),
        'tbody' => array(),
        'thead' => array(),
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
        'img' => array('src'),
        'figure' => array(),
        'figcaption' => array(),
        'cite' => array(),
        'time' => array('datetime'),
        'abbr' => array('title'),
        'iframe' => array('width', 'height', 'frameborder', 'src'),
        'q' => array('cite')
    );

    public $strip_tags_content = array(
        'script'
    );

    public $allowed_protocols = array(
        'http://',
        'https://',
        'ftp://',
        'mailto:',
        '//',
        'data:image/png;base64,',
        'data:image/gif;base64,',
        'data:image/jpg;base64,'
    );

    public $protocol_attributes = array(
        'src',
        'href',
    );

    public $blacklist_media = array(
        'feeds.feedburner.com',
        'share.feedsportal.com',
        'da.feedsportal.com',
        'rss.feedsportal.com',
        'res.feedsportal.com',
        'res1.feedsportal.com',
        'res2.feedsportal.com',
        'res3.feedsportal.com',
        'pi.feedsportal.com',
        'rss.nytimes.com',
        'feeds.wordpress.com',
        'stats.wordpress.com',
        'rss.cnn.com',
        'twitter.com/home?status=',
        'twitter.com/share',
        'twitter_icon_large.png',
        'www.facebook.com/sharer.php',
        'facebook_icon_large.png',
        'plus.google.com/share',
        'www.gstatic.com/images/icons/gplus-16.png',
        'www.gstatic.com/images/icons/gplus-32.png',
        'www.gstatic.com/images/icons/gplus-64.png'
    );

    public $required_attributes = array(
        'a' => array('href'),
        'img' => array('src'),
        'iframe' => array('src')
    );

    public $add_attributes = array(
        'a' => 'rel="noreferrer" target="_blank"'
    );

    public $iframe_allowed_resources = array(
        '//www.youtube.com',
        'http://www.youtube.com/',
        'https://www.youtube.com/',
        'http://player.vimeo.com/',
        'https://player.vimeo.com/',
        '//player.vimeo.com/',
        'http://www.dailymotion.com',
        'https://www.dailymotion.com',
        '//www.dailymotion.com',
    );


    public function __construct($data, $site_url)
    {
        $this->url = $site_url;

        // Workaround for old libxml2 (Debian Lenny)
        if (LIBXML_DOTTED_VERSION === '2.6.32') {

            $entities = get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES|ENT_XHTML, 'UTF-8');

            unset($entities['&']);
            unset($entities['>']);
            unset($entities['<']);

            $data = str_replace(array_values($entities), array_keys($entities), $data);
        }

        // Convert bad formatted documents to XML
        $dom = new \DOMDocument;
        $dom->loadHTML('<?xml version="1.0" encoding="UTF-8">'.$data);
        $this->input = $dom->saveXML($dom->getElementsByTagName('body')->item(0));

        // Workaround for old libxml2 (Debian Lenny)
        if (LIBXML_DOTTED_VERSION === '2.6.32') $this->input = utf8_decode($this->input);
    }


    public function execute()
    {
        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, 'startTag', 'endTag');
        xml_set_character_data_handler($parser, 'dataTag');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        xml_parse($parser, $this->input, true); // We ignore parsing error (for old libxml)
        xml_parser_free($parser);

        return $this->data;
    }


    public function startTag($parser, $name, $attributes)
    {
        $empty_tag = false;
        $this->strip_content = false;

        if ($this->isPixelTracker($name, $attributes)) {

            $empty_tag = true;
        }
        else if ($this->isAllowedTag($name)) {

            $attr_data = '';
            $used_attributes = array();

            foreach ($attributes as $attribute => $value) {

                if ($value != '' && $this->isAllowedAttribute($name, $attribute)) {

                    if ($this->isResource($attribute)) {

                        if ($name === 'iframe') {

                            if ($this->isAllowedIframeResource($value)) {

                                $attr_data .= ' '.$attribute.'="'.$value.'"';
                                $used_attributes[] = $attribute;
                            }
                        }
                        else if ($this->isRelativePath($value)) {

                            $attr_data .= ' '.$attribute.'="'.$this->getAbsoluteUrl($value, $this->url).'"';
                            $used_attributes[] = $attribute;
                        }
                        else if ($this->isAllowedProtocol($value) && ! $this->isBlacklistMedia($value)) {

                            if ($attribute == 'src' &&
                                isset($attributes['data-src']) &&
                                $this->isAllowedProtocol($attributes['data-src']) &&
                                ! $this->isBlacklistMedia($attributes['data-src'])) {

                                $value = $attributes['data-src'];
                            }

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

            // Check for required attributes
            if (isset($this->required_attributes[$name])) {

                foreach ($this->required_attributes[$name] as $required_attribute) {

                    if (! in_array($required_attribute, $used_attributes)) {

                        $empty_tag = true;
                        break;
                    }
                }
            }

            if (! $empty_tag) {

                $this->data .= '<'.$name.$attr_data;

                // Add custom attributes
                if (isset($this->add_attributes[$name])) {

                    $this->data .= ' '.$this->add_attributes[$name].' ';
                }

                // If img or br, we don't close it here
                if ($name !== 'img' && $name !== 'br') $this->data .= '>';
            }
        }

        if (in_array($name, $this->strip_tags_content)) {

            $this->strip_content = true;
        }

        $this->empty_tags[] = $empty_tag;
    }


    public function endTag($parser, $name)
    {
        if (! array_pop($this->empty_tags) && $this->isAllowedTag($name)) {

            $this->data .= $name !== 'img' && $name !== 'br' ? '</'.$name.'>' : '/>';
        }
    }


    public function dataTag($parser, $content)
    {
        if (! $this->strip_content) $this->data .= htmlspecialchars($content, ENT_QUOTES, 'UTF-8', false);
    }


    public function getAbsoluteUrl($path, $url)
    {
        //if (! filter_var($url, FILTER_VALIDATE_URL)) return '';

        $components = parse_url($url);

        if (! isset($components['scheme'])) $components['scheme'] = 'http';

        if (! isset($components['host'])) {

            if ($url) {

                $components['host'] = $url;
                $components['path'] = '/';
            }
            else {

                return '';
            }
        }

        if ($path{0} === '/') {

            // Absolute path
            return $components['scheme'].'://'.$components['host'].$path;
        }
        else {

            // Relative path
            $url_path = isset($components['path']) && ! empty($components['path']) ? $components['path'] : '/';
            $length = strlen($url_path);

            if ($length > 1 && $url_path{$length - 1} !== '/') {

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
        if (strpos($value, 'data:') === 0) return false;

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


    public function isAllowedIframeResource($value)
    {
        foreach ($this->iframe_allowed_resources as $url) {

            if (strpos($value, $url) === 0) {

                return true;
            }
        }

        return false;
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
