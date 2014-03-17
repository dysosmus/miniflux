<?php

namespace PicoFeed\Parsers;

/**
 * RSS 2.0 Parser
 *
 * @author  Frederic Guillot
 * @package parser
 */
class Rss20 extends \PicoFeed\Parser
{
    /**
     * Parse the document
     *
     * @access public
     * @return mixed   Rss20 instance or false
     */
    public function execute()
    {
        \PicoFeed\Logging::log(\get_called_class().': begin parsing');

        \libxml_use_internal_errors(true);
        $xml = \simplexml_load_string($this->content);

        if ($xml === false) {
            \PicoFeed\Logging::log(\get_called_class().': XML parsing error');
            \PicoFeed\Logging::log($this->getXmlErrors());
            return false;
        }

        $namespaces = $xml->getNamespaces(true);

        if ($xml->channel->link && $xml->channel->link->count() > 1) {

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

        $this->language = isset($xml->channel->language) ? (string) $xml->channel->language : '';
        $this->title = $this->stripWhiteSpace((string) $xml->channel->title) ?: $this->url;
        $this->id = $this->url;
        $this->updated = $this->parseDate(isset($xml->channel->pubDate) ? (string) $xml->channel->pubDate : (string) $xml->channel->lastBuildDate);

        \PicoFeed\Logging::log(\get_called_class().': Title => '.$this->title);
        \PicoFeed\Logging::log(\get_called_class().': Url => '.$this->url);

        // RSS feed might be empty
        if (! $xml->channel->item) {
            \PicoFeed\Logging::log(\get_called_class().': feed empty or malformed');
            return $this;
        }

        foreach ($xml->channel->item as $entry) {

            $item = new \StdClass;
            $item->title = $this->stripWhiteSpace((string) $entry->title);
            $item->url = '';
            $item->author= '';
            $item->updated = '';
            $item->content = '';
            $item->enclosure = '';
            $item->enclosure_type = '';
            $item->language = $this->language;

            foreach ($namespaces as $name => $url) {

                $namespace = $entry->children($namespaces[$name]);

                if (! $item->author && ! empty($namespace->creator)) $item->author = (string) $namespace->creator;
                if (! $item->updated && ! empty($namespace->date)) $item->updated = $this->parseDate((string) $namespace->date);
                if (! $item->updated && ! empty($namespace->updated)) $item->updated = $this->parseDate((string) $namespace->updated);
                if (! $item->content && ! empty($namespace->encoded)) $item->content = (string) $namespace->encoded;

                // Get FeedBurner original links
                if (! $item->url && ! empty($namespace->origLink)) $item->url = (string) $namespace->origLink;
                if (! $item->enclosure && ! empty($namespace->origEnclosureLink)) $item->enclosure = (string) $namespace->origEnclosureLink;
            }

            if (empty($item->url)) {

                if (isset($entry->link)) {
                    $item->url = (string) $entry->link;
                }
                else if (isset($entry->guid)) {
                    $item->url = (string) $entry->guid;
                }
            }

            if (empty($item->updated)) $item->updated = $this->parseDate((string) $entry->pubDate) ?: $this->updated;

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
                $id = (string) $entry->guid;
                $item->id = $this->generateId($id !== '' && $id !== $item->url ? $id : $item->url, $this->isExcludedFromId($this->url) ? '' : $this->url);
            }
            else {
                $item->id = $this->generateId($item->url, $this->isExcludedFromId($this->url) ? '' : $this->url);
            }

            if (empty($item->title)) $item->title = $item->url;

            // if optional enclosure tag with multimedia provided, capture here
            if (isset($entry->enclosure)) {

                if (! $item->enclosure) {
                    $item->enclosure = isset($entry->enclosure['url']) ? (string) $entry->enclosure['url'] : '';
                }

                $item->enclosure_type = isset($entry->enclosure['type']) ? (string) $entry->enclosure['type'] : '';

                if (\PicoFeed\Filter::isRelativePath($item->enclosure)) {
                    $item->enclosure = \PicoFeed\Filter::getAbsoluteUrl($item->enclosure, $this->url);
                }
            }

            $item->content = $this->filterHtml($item->content, $item->url);
            $this->items[] = $item;
        }

        \PicoFeed\Logging::log(\get_called_class().': parsing finished ('.count($this->items).' items)');

        return $this;
    }
}