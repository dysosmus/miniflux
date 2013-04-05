<?php

namespace PicoFeed;

class Rss10 extends Parser
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

        return $this;
    }
}