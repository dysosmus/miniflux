<?php

namespace PicoFeed;

require_once __DIR__.'/Client.php';
require_once __DIR__.'/Encoding.php';
require_once __DIR__.'/Logging.php';

class Grabber
{
    public $content = '';
    public $html = '';

    // Order is important
    public $candidatesAttributes = array(
        'article',
        'articleBody',
        'articlebody',
        'articleContent',
        'articlecontent',
        'articlePage',
        'post-content',
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

            Logging::log(\get_called_class().' HTML fetched');

            $rules = $this->getRules();

            \libxml_use_internal_errors(true);
            $dom = new \DOMDocument;
            $dom->loadHTML($this->html);

            if (is_array($rules)) {
                Logging::log(\get_called_class().' Parse content with rules');
                $this->parseContentWithRules($dom, $rules);
            }
            else {

                Logging::log(\get_called_class().' Parse content with candidates');
                $this->parseContentWithCandidates($dom);

                if (strlen($this->content) < 50) {
                    Logging::log(\get_called_class().' No enought content fetched, get the full body');
                    $this->content = $dom->saveXML($dom->firstChild);
                }

                Logging::log(\get_called_class().' Strip garbage');
                $this->stripGarbage();
            }
        }
        else {

            Logging::log(\get_called_class().' No content fetched');
        }

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


    public function parseContentWithRules($dom, array $rules)
    {
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

        if (isset($rules['strip_id_or_class']) && is_array($rules['strip_id_or_class'])) {

            foreach ($rules['strip_id_or_class'] as $pattern) {

                $pattern = strtr($pattern, array("'" => '', '"' => ''));
                $nodes = $xpath->query("//*[contains(@class, '$pattern') or contains(@id, '$pattern')]");

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


    public function parseContentWithCandidates($dom)
    {
        $xpath = new \DOMXPath($dom);

        // Try to fetch <article/>
        $nodes = $xpath->query('//article');

        if ($nodes !== false && $nodes->length > 0) {
            $this->content = $dom->saveXML($nodes->item(0));
            return;
        }

        // Try to lookup in each <div/>
        foreach ($this->candidatesAttributes as $candidate) {

            $nodes = $xpath->query('//div[(contains(@class, "'.$candidate.'") or @id="'.$candidate.'") and not (contains(@class, "nav") or contains(@class, "page"))]');

            if ($nodes !== false && $nodes->length > 0) {
                $this->content = $dom->saveXML($nodes->item(0));
                return;
            }
        }
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
                foreach ($nodes as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        foreach ($this->stripAttributes as $attribute) {

            $nodes = $xpath->query('//*[contains(@class, "'.$attribute.'") or contains(@id, "'.$attribute.'")]');

            if ($nodes !== false && $nodes->length > 0) {
                foreach ($nodes as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        $this->content = '';

        foreach($dom->childNodes as $node) {
            $this->content .= $dom->saveXML($node);
        }
    }
}
