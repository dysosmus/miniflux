<?php

namespace PicoFeed;

require_once __DIR__.'/Filter.php';


abstract class Parser
{
    protected $content = '';

    public $id = '';
    public $url = '';
    public $title = '';
    public $updated = '';
    public $items = array();


    abstract public function execute();


    public function __construct($content)
    {
        $this->content = $content;
    }


    public function filterHtml($str, $item_url)
    {
        $content = '';

        if ($str) {

            $filter = new Filter($str, $item_url);
            $content = $filter->execute();
        }

        return $content;
    }
}


class Atom extends Parser
{
    public function execute()
    {
        try {

            \libxml_use_internal_errors(true);

            $xml = new \SimpleXMLElement($this->content);

            $this->url = $this->getUrl($xml);
            $this->title = (string) $xml->title;
            $this->id = (string) $xml->id;
            $this->updated = strtotime((string) $xml->updated);
            $author = (string) $xml->author->name;

            foreach ($xml->entry as $entry) {

                if (isset($entry->author->name)) {

                    $author = $entry->author->name;
                }

                $item = new \StdClass;
                $item->id = (string) $entry->id;
                $item->title = (string) $entry->title;
                $item->url = $this->getUrl($entry);
                $item->updated = strtotime((string) $entry->updated);
                $item->author = $author;
                $item->content = $this->filterHtml($this->getContent($entry), $item->url);

                $this->items[] = $item;
            }
        }
        catch (\Exception $e) {

        }

        return $this;
    }


    public function getContent($entry)
    {
        if (isset($entry->content) && ! empty($entry->content)) {

            if (count($entry->content->children())) {

                return (string) $entry->content->asXML();
            }
            else {

                return (string) $entry->content;
            }
        }
        else if (isset($entry->summary) && ! empty($entry->summary)) {

            return (string) $entry->summary;
        }

        return '';
    }


    public function getUrl($xml)
    {
        foreach ($xml->link as $link) {

            if ((string) $link['type'] === 'text/html') {

                return (string) $link['href'];
            }
        }

        return (string) $xml->link['href'];
    }
}


class Rss20 extends Parser
{
    public function execute()
    {
        try {

            \libxml_use_internal_errors(true);

            $xml = new \SimpleXMLElement($this->content);
            $ns = $xml->getNamespaces(true);

            $this->title = (string) $xml->channel->title;
            $this->url = (string) $xml->channel->link;
            $this->id = $this->url;
            $this->updated = isset($xml->channel->pubDate) ? (string) $xml->channel->pubDate : (string) $xml->channel->lastBuildDate;
            $this->updated = strtotime($this->updated);

            foreach ($xml->channel->item as $entry) {

                $author = '';
                $content = '';
                $pubdate = '';
                $link = '';

                if (isset($ns['feedburner'])) {

                    $ns_fb = $entry->children($ns['feedburner']);
                    $link = $ns_fb->origLink;
                }

                if (isset($ns['dc'])) {

                    $ns_dc = $entry->children($ns['dc']);
                    $author = (string) $ns_dc->creator;
                    $pubdate = (string) $ns_dc->date;
                }

                if (isset($ns['content'])) {

                    $ns_content = $entry->children($ns['content']);
                    $content = (string) $ns_content->encoded;
                }

                if ($content === '' && isset($entry->description)) {

                    $content = (string) $entry->description;
                }

                $item = new \StdClass;
                $item->id = (string) $entry->guid;
                $item->title = (string) $entry->title;
                $item->url = $link ?: (string) $entry->link;
                $item->updated = strtotime($pubdate ?: (string) $entry->pubDate) ?: $this->updated;
                $item->content = $this->filterHtml($content, $item->url);
                $item->author = $author ?: (string) $xml->channel->webMaster;

                $this->items[] = $item;
            }
        }
        catch (\Exception $e) {

        }

        return $this;
    }
}


class Rss10 extends Parser
{
    public function execute()
    {
        try {

            \libxml_use_internal_errors(true);

            $xml = new \SimpleXMLElement($this->content);
            $ns = $xml->getNamespaces(true);

            $this->title = (string) $xml->channel->title;
            $this->url = (string) $xml->channel->link;
            $this->id = $this->url;

            if (isset($ns['dc'])) {

                $ns_dc = $xml->channel->children($ns['dc']);
                $this->updated = isset($ns_dc->date) ? strtotime($ns_dc->date) : time();
            }
            else {

                $this->updated = time();
            }

            foreach ($xml->item as $entry) {

                $author = '';
                $content = '';
                $pubdate = '';
                $link = '';

                if (isset($ns['feedburner'])) {

                    $ns_fb = $entry->children($ns['feedburner']);
                    $link = $ns_fb->origLink;
                }

                if (isset($ns['dc'])) {

                    $ns_dc = $entry->children($ns['dc']);
                    $author = (string) $ns_dc->creator;
                    $pubdate = (string) $ns_dc->date;
                }

                if (isset($ns['content'])) {

                    $ns_content = $entry->children($ns['content']);
                    $content = (string) $ns_content->encoded;
                }

                if ($content === '' && isset($entry->description)) {

                    $content = (string) $entry->description;
                }

                $item = new \StdClass;
                $item->title = (string) $entry->title;
                $item->url = $link ?: (string) $entry->link;
                $item->id = $item->url;
                $item->updated = $pubdate ? strtotime($pubdate) : time();
                $item->content = $this->filterHtml($content, $item->url);
                $item->author = $author ?: (string) $xml->channel->webMaster;

                $this->items[] = $item;
            }
        }
        catch (\Exception $e) {

        }

        return $this;
    }
}
