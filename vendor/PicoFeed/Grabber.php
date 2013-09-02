<?php

namespace PicoFeed;

require_once __DIR__.'/Client.php';
require_once __DIR__.'/Encoding.php';
require_once __DIR__.'/Logging.php';
require_once __DIR__.'/Filter.php';

class Grabber
{
    public $content = '';
    public $html = '';

    // Order is important, generic terms at the end
    public $candidatesAttributes = array(
        'articleBody',
        'articlebody',
        'article-body',
        'articleContent',
        'articlecontent',
        'article-content',
        'articlePage',
        'post-content',
        'entry-content',
        'main-content',
        'comic',
        'article',
        'content',
        'main',
    );

    public $stripAttributes = array(
        'comment',
        'share',
        'links',
        'toolbar',
        'fb',
        'footer',
        'credit',
        'bottom',
        'nav',
        'header',
        'social',
        'entry-utility',
    );

    public $stripTags = array(
        'script',
        'style',
        'nav',
        'header',
        'footer',
        'aside',
    );


    public function __construct($url)
    {
        $this->url = $url;
    }


    public function parse()
    {
        if ($this->html) {

            Logging::log(\get_called_class().' Fix encoding');
            $this->html = Filter::stripMetaTags($this->html);
            $this->html = Encoding::toUtf8($this->html);

            Logging::log(\get_called_class().' Try to find rules');
            $rules = $this->getRules();

            if (is_array($rules)) {
                Logging::log(\get_called_class().' Parse content with rules');
                $this->parseContentWithRules($rules);
            }
            else {
                Logging::log(\get_called_class().' Parse content with candidates');
                $this->parseContentWithCandidates();
            }
        }
        else {
            Logging::log(\get_called_class().' No content fetched');
        }

        Logging::log(\get_called_class().' Content length: '.strlen($this->content).' bytes');
        Logging::log(\get_called_class().' Grabber done');

        return $this->content !== '';
    }


    public function download($timeout = 5, $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36')
    {
        $client = Client::create();
        $client->url = $this->url;
        $client->timeout = $timeout;
        $client->user_agent = $user_agent;
        $client->execute();
        $this->html = $client->getContent();

        return $this->html;
    }


    public function getRules()
    {
        $hostname = parse_url($this->url, PHP_URL_HOST);
        $files = array($hostname);

        if (substr($hostname, 0, 4) == 'www.') $files[] = substr($hostname, 4);
        if (($pos = strpos($hostname, '.')) !== false) $files[] = substr($hostname, $pos);

        foreach ($files as $file) {

            $filename = __DIR__.'/Rules/'.$file.'.php';

            if (file_exists($filename)) {
                return include $filename;
            }
        }

        return false;
    }


    public function parseContentWithRules(array $rules)
    {
        \libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        $dom->loadHTML('<?xml version="1.0" encoding="UTF-8">'.$this->html);
        $xpath = new \DOMXPath($dom);

        if (isset($rules['strip']) && is_array($rules['strip'])) {

            foreach ($rules['strip'] as $pattern) {

                $nodes = $xpath->query($pattern);

                if ($nodes !== false && $nodes->length > 0) {
                    foreach ($nodes as $node) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }
        }

        if (isset($rules['body']) && is_array($rules['body'])) {

            foreach ($rules['body'] as $pattern) {

                $nodes = $xpath->query($pattern);

                if ($nodes !== false && $nodes->length > 0) {
                    foreach ($nodes as $node) {
                        $this->content .= $dom->saveXML($node);
                    }
                }
            }
        }
    }


    public function parseContentWithCandidates()
    {
        \libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        $dom->loadHTML('<?xml version="1.0" encoding="UTF-8">'.$this->html);
        $xpath = new \DOMXPath($dom);

        // Try to lookup in each tag
        foreach ($this->candidatesAttributes as $candidate) {

            Logging::log(\get_called_class().' Try this candidate: "'.$candidate.'"');

            $nodes = $xpath->query('//*[(contains(@class, "'.$candidate.'") or @id="'.$candidate.'") and not (contains(@class, "nav") or contains(@class, "page"))]');

            if ($nodes !== false && $nodes->length > 0) {
                $this->content = $dom->saveXML($nodes->item(0));
                Logging::log(\get_called_class().' Find candidate "'.$candidate.'" ('.strlen($this->content).' bytes)');
                break;
            }
        }

        // Try to fetch <article/>
        if (! $this->content) {

            $nodes = $xpath->query('//article');

            if ($nodes !== false && $nodes->length > 0) {
                $this->content = $dom->saveXML($nodes->item(0));
                Logging::log(\get_called_class().' Find <article/> tag ('.strlen($this->content).' bytes)');
            }
        }

        if (strlen($this->content) < 50) {
            Logging::log(\get_called_class().' No enought content fetched, get the full body');
            $this->content = $dom->saveXML($dom->firstChild);
        }

        Logging::log(\get_called_class().' Strip garbage');
        $this->stripGarbage();
    }


    public function stripGarbage()
    {
        \libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        $dom->loadXML($this->content);
        $xpath = new \DOMXPath($dom);

        foreach ($this->stripTags as $tag) {

            $nodes = $xpath->query('//'.$tag);

            if ($nodes !== false && $nodes->length > 0) {
                Logging::log(\get_called_class().' Strip tag: "'.$tag.'"');
                foreach ($nodes as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        foreach ($this->stripAttributes as $attribute) {

            $nodes = $xpath->query('//*[contains(@class, "'.$attribute.'") or contains(@id, "'.$attribute.'")]');

            if ($nodes !== false && $nodes->length > 0) {
                Logging::log(\get_called_class().' Strip attribute: "'.$tag.'"');
                foreach ($nodes as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        $this->content = $dom->saveXML($dom->documentElement);
    }
}
