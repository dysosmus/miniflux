<?php

namespace PicoFeed;

class Rss20 extends Parser
{
    public function execute()
    {
        $this->content = $this->normalizeData($this->content);

        \libxml_use_internal_errors(true);
        $xml = \simplexml_load_string($this->content);

        if ($xml === false) {

            if ($this->debug) $this->displayXmlErrors();
            return false;
        }

        $namespaces = $xml->getNamespaces(true);

        if ($xml->channel->link->count() > 1) {

            foreach ($xml->channel->link as $xml_link) {

                $link = (string) $xml_link;

                if ($link !== '') {

                    $this->url = (string) $link;
                    break;
                }
            }
        }
        else {

            $this->url = (string) $xml->channel->link;
        }

        $this->title = (string) $xml->channel->title;
        $this->id = $this->url;
        $this->updated = isset($xml->channel->pubDate) ? (string) $xml->channel->pubDate : (string) $xml->channel->lastBuildDate;
        $this->updated = $this->updated ? strtotime($this->updated) : time();

        foreach ($xml->channel->item as $entry) {

            $item = new \StdClass;
            $item->title = (string) $entry->title;
            $item->url = '';
            $item->author= '';
            $item->updated = '';
            $item->content = '';

            foreach ($namespaces as $name => $url) {

                $namespace = $entry->children($namespaces[$name]);

                if (! $item->url && ! empty($namespace->origLink)) $item->url = (string) $namespace->origLink;
                if (! $item->author && ! empty($namespace->creator)) $item->author = (string) $namespace->creator;
                if (! $item->updated && ! empty($namespace->date)) $item->updated = strtotime((string) $namespace->date);
                if (! $item->updated && ! empty($namespace->updated)) $item->updated = strtotime((string) $namespace->updated);
                if (! $item->content && ! empty($namespace->encoded)) $item->content = (string) $namespace->encoded;
            }

            if (empty($item->url)) $item->url = (string) $entry->link;
            if (empty($item->updated)) $item->updated = strtotime((string) $entry->pubDate) ?: $this->updated;

            if (empty($item->content)) {

                $item->content = isset($entry->description) ? (string) $entry->description : '';
            }

            if (empty($item->author)) {

                if (isset($entry->author)) {

                    $item->author = (string) $entry->author;
                }
                else if (isset($xml->channel->webMaster)) {

                    $item->author = (string) $xml->channel->webMaster;
                }
            }

            if (isset($entry->guid) && isset($entry->guid['isPermaLink']) && (string) $entry->guid['isPermaLink'] != 'false') {

                $item->id = (string) $entry->guid;
            }
            else {

                $item->id = $item->url;
            }

            if (empty($item->title)) $item->title = $item->url;

            $item->content = $this->filterHtml($item->content, $item->url);
            $this->items[] = $item;
        }

        return $this;
    }
}