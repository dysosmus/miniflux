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

            if ($xml->getName() !== 'opml') {

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
        foreach ($tree->outline as $item) {

            if (isset($item['type']) && strtolower($item['type']) === 'folder' && isset($item->outline)) {

                $this->parseEntries($item);
            }
            else if (isset($item['type']) && strtolower($item['type']) === 'rss') {

                $entry = new \StdClass;
                $entry->title = (string) $item['text'];
                $entry->site_url = (string) $item['htmlUrl'];
                $entry->feed_url = (string) $item['xmlUrl'];
                $entry->type = isset($item['version']) ? (string) $item['version'] : (string) $item['type'];
                $entry->description = (string) $item['description'];
                $this->items[] = $entry;
            }
        }
    }
}