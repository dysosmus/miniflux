<?php

namespace PicoFeed;

class Reader
{
    private $url = '';
    private $content = '';


    public function __construct($content = '')
    {
        $this->content = $content;

        return $this;
    }


    public function download($url)
    {
        if (strpos($url, 'http') !== 0) {

            $url = 'http://'.$url;
        }

        $this->url = $url;
        $this->content = @file_get_contents($this->url);

        return $this;
    }


    public function getContent()
    {
        return $this->content;
    }


    public function getUrl()
    {
        return $this->url;
    }


    public function getFirstTag($data)
    {
        // Strip HTML comments
        $data = preg_replace('/<!--(.*)-->/Uis', '', $data);

        // Find <?xml version....
        if (strpos($data, '<?xml') !== false) {

            $data = substr($data, strrpos($data, '?>') + 2);

            // Find the first tag
            $open_tag = strpos($data, '<');
            $close_tag = strpos($data, '>');

            return substr($data, $open_tag, $close_tag);
        }

        return $data;
    }


    public function getParser($discover = false)
    {
        $first_tag = $this->getFirstTag($this->content);

        if (strpos($first_tag, '<feed ') !== false) {

            require_once __DIR__.'/Parsers/Atom.php';
            return new Atom($this->content);
        }
        else if (strpos($first_tag, '<rss ') !== false &&
                (strpos($first_tag, 'version="2.0"') !== false || strpos($first_tag, 'version=\'2.0\'') !== false)) {

            require_once __DIR__.'/Parsers/Rss20.php';
            return new Rss20($this->content);
        }
        else if (strpos($first_tag, '<rss ') !== false &&
                (strpos($first_tag, 'version="0.92"') !== false || strpos($first_tag, 'version=\'0.92\'') !== false)) {

            require_once __DIR__.'/Parsers/Rss92.php';
            return new Rss92($this->content);
        }
        else if (strpos($first_tag, '<rss ') !== false &&
                (strpos($first_tag, 'version="0.91"') !== false || strpos($first_tag, 'version=\'0.91\'') !== false)) {

            require_once __DIR__.'/Parsers/Rss91.php';
            return new Rss91($this->content);
        }
        else if (strpos($first_tag, '<rdf:') !== false && strpos($first_tag, 'xmlns="http://purl.org/rss/1.0/"') !== false) {

            require_once __DIR__.'/Parsers/Rss10.php';
            return new Rss10($this->content);
        }
        else if ($discover === true) {

            return false;
        }
        else if ($this->discover()) {

            return $this->getParser(true);
        }

        return false;
    }


    public function discover()
    {
        if (! $this->content) {

            return false;
        }

        \libxml_use_internal_errors(true);

        $dom = new \DOMDocument;
        $dom->loadHTML($this->content);

        $xpath = new \DOMXPath($dom);

        $queries = array(
            "//link[@type='application/atom+xml']",
            "//link[@type='application/rss+xml']"
        );

        foreach ($queries as $query) {

            $nodes = $xpath->query($query);

            if ($nodes->length !== 0) {

                $link = $nodes->item(0)->getAttribute('href');

                // Relative links
                if (strpos($link, 'http') !== 0) {

                    if ($link{0} === '/') $link = substr($link, 1);
                    if ($this->url{strlen($this->url) - 1} !== '/') $this->url .= '/';

                    $link = $this->url.$link;
                }

                $this->download($link);

                return true;
            }
        }

        return false;
    }
}
