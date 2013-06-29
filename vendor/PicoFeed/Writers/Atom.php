<?php

namespace PicoFeed\Writers;

require_once __DIR__.'/../Writer.php';

class Atom extends \PicoFeed\Writer
{
    private $required_feed_properties = array(
        'title',
        'site_url',
        'feed_url',
    );

    private $required_item_properties = array(
        'title',
        'url',
    );


    public function execute($filename = '')
    {
        $this->checkRequiredProperties($this->required_feed_properties, $this);

        $this->dom = new \DomDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        // <feed/>
        $feed = $this->dom->createElement('feed');
        $feed->setAttributeNodeNS(new \DomAttr('xmlns', 'http://www.w3.org/2005/Atom'));

        // <generator/>
        $generator = $this->dom->createElement('generator', 'PicoFeed');
        $generator->setAttribute('url', 'https://github.com/fguillot/picoFeed');
        $feed->appendChild($generator);

        // <title/>
        $feed->appendChild($this->dom->createElement('title', $this->title));

        // <id/>
        $feed->appendChild($this->dom->createElement('id', $this->site_url));

        // <updated/>
        $this->addUpdated($feed, isset($this->updated) ? $this->updated : '');

        // <link rel="alternate" type="text/html" href="http://example.org/"/>
        $this->addLink($feed, $this->site_url);

        // <link rel="self" type="application/atom+xml" href="http://example.org/feed.atom"/>
        $this->addLink($feed, $this->feed_url, 'self', 'application/atom+xml');

        // <author/>
        if (isset($this->author)) $this->addAuthor($feed, $this->author);

        // <entry/>
        foreach ($this->items as $item) {

            $this->checkRequiredProperties($this->required_item_properties, $item);

            $entry = $this->dom->createElement('entry');

            // <title/>
            $entry->appendChild($this->dom->createElement('title', $item['title']));

            // <id/>
            $entry->appendChild($this->dom->createElement('id', $item['url']));

            // <updated/>
            $this->addUpdated($entry, isset($item['updated']) ? $item['updated'] : '');

            // <published/>
            if (isset($item['published'])) {
                $entry->appendChild($this->dom->createElement('published', date(DATE_ATOM, $item['published'])));
            }

            // <link rel="alternate" type="text/html" href="http://example.org/"/>
            $this->addLink($entry, $item['url']);

            // <summary/>
            if (isset($item['summary'])) {
                $entry->appendChild($this->dom->createElement('summary', $item['summary']));
            }

            // <content/>
            if (isset($item['content'])) {
                $content = $this->dom->createElement('content');
                $content->setAttribute('type', 'html');
                $content->appendChild($this->dom->createCDATASection($item['content']));
                $entry->appendChild($content);
            }

            // <author/>
            if (isset($item['author'])) $this->addAuthor($entry, $item['author']);

            $feed->appendChild($entry);
        }

        $this->dom->appendChild($feed);

        if ($filename) {
            $this->dom->save($filename);
        }
        else {
            return $this->dom->saveXML();
        }
    }


    public function addLink($xml, $url, $rel = 'alternate', $type = 'text/html')
    {
        $link = $this->dom->createElement('link');
        $link->setAttribute('rel', $rel);
        $link->setAttribute('type', $type);
        $link->setAttribute('href', $url);
        $xml->appendChild($link);
    }


    public function addUpdated($xml, $value = '')
    {
        $xml->appendChild($this->dom->createElement(
            'updated',
            date(DATE_ATOM, $value ?: time())
        ));
    }


    public function addAuthor($xml, array $values)
    {
        $author = $this->dom->createElement('author');

        if (isset($values['name'])) {
            $name = $this->dom->createElement('name', $values['name']);
            $author->appendChild($name);
        }

        if (isset($values['email'])) {
            $email = $this->dom->createElement('email', $values['email']);
            $author->appendChild($email);
        }

        if (isset($values['url'])) {
            $uri = $this->dom->createElement('uri', $values['url']);
            $author->appendChild($uri);
        }

        $xml->appendChild($author);
    }
}