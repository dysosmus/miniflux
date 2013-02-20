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


    public function getParser()
    {
        $first_lines = substr($this->content, 0, 1024);

        if (stripos($first_lines, 'html') !== false) {

            if ($this->discover()) {

                $first_lines = substr($this->content, 0, 1024);
            }
            else {

                return false;
            }
        }

        if (strpos($first_lines, '<feed ') !== false) {

            return new Atom($this->content);
        }
        else if (strpos($first_lines, '<rss ') !== false && strpos($first_lines, 'version="2.0"') !== false) {

            return new Rss20($this->content);
        }/*
        else if (strpos($first_lines, '<rdf') !== false && strpos($first_lines, 'xmlns="http://purl.org/rss/1.0/"') !== false) {

            return new Rss10($this->content);
        }*/

        return false;
    }


    public function discover()
    {
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
