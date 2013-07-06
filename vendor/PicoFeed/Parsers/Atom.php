<?php

namespace PicoFeed\Parsers;

class Atom extends \PicoFeed\Parser
{
    public function execute()
    {
        \libxml_use_internal_errors(true);
        $xml = \simplexml_load_string($this->content);

        if ($xml === false) {

            \PicoFeed\Logging::log($this->getXmlErrors());
            return false;
        }

        $this->url = $this->getUrl($xml);
        $this->title = $this->stripWhiteSpace((string) $xml->title);
        $this->id = (string) $xml->id;
        $this->updated = strtotime((string) $xml->updated);
        $author = (string) $xml->author->name;

        foreach ($xml->entry as $entry) {

            if (isset($entry->author->name)) {

                $author = $entry->author->name;
            }

            $item = new \StdClass;
            $item->id = (string) $entry->id;
            $item->title = $this->stripWhiteSpace((string) $entry->title);
            $item->url = $this->getUrl($entry);
            $item->updated = strtotime((string) $entry->updated);
            $item->author = $author;
            $item->content = $this->filterHtml($this->getContent($entry), $item->url);

            if (empty($item->title)) $item->title = $item->url;

            $this->items[] = $item;
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

            if ((string) $link['type'] === 'text/html' || (string) $link['type'] === 'application/xhtml+xml') {

                return (string) $link['href'];
            }
        }

        return (string) $xml->link['href'];
    }
}