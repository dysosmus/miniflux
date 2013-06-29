<?php

namespace PicoFeed\Writers;

require_once __DIR__.'/../Writer.php';

class Atom extends \PicoFeed\Writer
{
    protected $required_properties = array(
        'title',
        'site_url',
        'feed_url'
    );


    public function execute($filename = '')
    {
        $this->checkRequiredProperties();

        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // <feed/>
        $feed = $dom->createElement('feed');
        $feed->setAttributeNodeNS(new \DomAttr('xmlns', 'http://www.w3.org/2005/Atom'));

        // <generator/>
        $generator = $dom->createElement('generator', 'PicoFeed');
        $generator->setAttribute('url', 'https://github.com/fguillot/picoFeed');
        $feed->appendChild($generator);

        // <title/>
        $feed->appendChild($dom->createElement('title', $this->title));

        // <updated/>
        $feed->appendChild($dom->createElement('updated', date(DATE_ATOM, isset($this->updated) ? $this->updated : time())));

        // <link rel="alternate" type="text/html" href="http://example.org/"/>
        $link = $dom->createElement('link');
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('type', 'text/html');
        $link->setAttribute('href', $this->site_url);
        $feed->appendChild($link);

        // <link rel="self" type="application/atom+xml" href="http://example.org/feed.atom"/>
        $link = $dom->createElement('link');
        $link->setAttribute('rel', 'self');
        $link->setAttribute('type', 'application/atom+xml');
        $link->setAttribute('href', $this->feed_url);
        $feed->appendChild($link);

        // <author/>
        if (isset($this->author)) {

            $name = $dom->createElement('name', $this->author);

            $author = $dom->createElement('author');
            $author->appendChild($name);
            $feed->appendChild($author);
        }

        // <entry/>
        foreach ($this->items as $item) {

            $entry = $dom->createElement('entry');

            // <title/>
            $entry->appendChild($dom->createElement('title', $item['title']));

            // <updated/>
            $entry->appendChild($dom->createElement('updated', date(DATE_ATOM, isset($item['updated']) ? $item['updated'] : time())));

            // <published/>
            if (isset($item['published'])) {
                $entry->appendChild($dom->createElement('published', date(DATE_ATOM, $item['published'])));
            }

            // <link rel="alternate" type="text/html" href="http://example.org/"/>
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'alternate');
            $link->setAttribute('type', 'text/html');
            $link->setAttribute('href', $item['url']);
            $entry->appendChild($link);

            // <summary/>
            if (isset($item['summary'])) {
                $entry->appendChild($dom->createElement('summary', $item['summary']));
            }

            // <content/>
            if (isset($item['content'])) {
                $content = $dom->createElement('content');
                $content->setAttribute('type', 'html');
                $content->appendChild($dom->createCDATASection($item['content']));
                $entry->appendChild($content);
            }

            // <author/>
            if (isset($item['author'])) {

                $name = $dom->createElement('name', $item['author']);

                $author = $dom->createElement('author');
                $author->appendChild($name);

                $entry->appendChild($author);
            }

            $feed->appendChild($entry);
        }

        $dom->appendChild($feed);

        if ($filename) {
            $dom->save($filename);
        }
        else {
            return $dom->saveXML();
        }
    }
}