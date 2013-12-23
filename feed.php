<?php

require 'common.php';
require 'vendor/PicoTools/Helper.php';
require 'vendor/PicoFarad/Response.php';
require 'vendor/PicoFarad/Request.php';
require 'vendor/PicoFeed/Writers/Atom.php';

use PicoFarad\Response;
use PicoFarad\Request;
use PicoFeed\Writers\Atom;

// Check token
$feed_token = Model\Config\get('feed_token');
$request_token = Request\param('token');

if ($feed_token !== $request_token) {
    Response\text('Access Forbidden', 403);
}

// Load translations
$language = Model\Config\get('language') ?: 'en_US';
if ($language !== 'en_US') PicoTools\Translator\load($language);

// Build Feed
$writer = new Atom;
$writer->title = t('Bookmarks').' - Miniflux';
$writer->site_url = Helper\get_current_base_url();
$writer->feed_url = $writer->site_url.'feed.php?token='.urlencode($feed_token);

$bookmarks = Model\Item\get_bookmarks();

foreach ($bookmarks as $bookmark) {

    $article = Model\Item\get($bookmark['id']);

    $writer->items[] = array(
        'id' => $article['id'],
        'title' => $article['title'],
        'updated' => $article['updated'],
        'url' => $article['url'],
        'content' => $article['content'],
    );
}

Response\xml($writer->execute());
