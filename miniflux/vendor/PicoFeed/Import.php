<?php

namespace PicoFeed;

class Import
{
    private $content = '';
    private $items = array();


    public function __construct($content)
    {
        $this->content = $content;
    }


    public function execute()
    {
        try {

            \libxml_use_internal_errors(true);

            $xml = new \SimpleXMLElement(trim($this->content));

            if ($xml->getName() !== 'opml' || ! isset($xml->body)) {

                return false;
            }

            $this->parseEntries($xml->body);
        }
        catch (\Exception $e) {

            return false;
        }

        return $this->items;
    }


    public function parseEntries($tree)
    {
        if (isset($tree->outline)) {

            foreach ($tree->outline as $item) {

                if (isset($item->outline)) {

                    $this->parseEntries($item);
                }
                else if (isset($item['text']) && isset($item['xmlUrl']) && isset($item['htmlUrl'])) {

                    $entry = new \StdClass;
                    $entry->title = isset($item['title']) ? (string) $item['title'] : (string) $item['text'];
                    $entry->site_url = (string) $item['htmlUrl'];
                    $entry->feed_url = (string) $item['xmlUrl'];
                    $entry->type = isset($item['version']) ? (string) $item['version'] : isset($item['type']) ? (string) $item['type'] : 'rss';
                    $entry->description = isset($item['description']) ? (string) $item['description'] : $entry->title;
                    $this->items[] = $entry;
                }
            }
        }
    }
}