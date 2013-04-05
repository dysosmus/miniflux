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

        $ns = $xml->getNamespaces(true);

        $this->title = (string) $xml->channel->title;
        $this->url = (string) $xml->channel->link;
        $this->id = $this->url;
        $this->updated = isset($xml->channel->pubDate) ? (string) $xml->channel->pubDate : (string) $xml->channel->lastBuildDate;

        if ($this->updated) {

            $this->updated = strtotime($this->updated);
        }
        else {

            $this->updated = time();
        }

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

            if ($author === '') {

                if (isset($entry->author)) {

                    $author = (string) $entry->author;
                }
                else if (isset($xml->channel->webMaster)) {

                    $author = (string) $xml->channel->webMaster;
                }
            }

            $item = new \StdClass;
            $item->title = (string) $entry->title;
            $item->url = $link ?: (string) $entry->link;
            $item->id = isset($entry->guid) ? (string) $entry->guid : $item->url;
            $item->updated = strtotime($pubdate ?: (string) $entry->pubDate) ?: $this->updated;
            $item->content = $this->filterHtml($content, $item->url);
            $item->author = $author;

            $this->items[] = $item;
        }

        return $this;
    }
}