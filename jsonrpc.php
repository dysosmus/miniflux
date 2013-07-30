<?php

require 'common.php';
require 'vendor/JsonRPC/Server.php';

use JsonRPC\Server;

$server = new Server;
$server->authentication(array(
    Model\get_config_value('username') => Model\get_config_value('api_token')
));

// Get all feeds
$server->register('feed.list', function () {

    return Model\get_feeds();
});

// Get one feed
$server->register('feed.info', function ($feed_id) {

    return Model\get_feed($feed_id);
});

// Add a new feed
$server->register('feed.create', function($url) {

    $result = Model\import_feed($url);
    Model\write_debug();

    return $result;
});

// Delete a feed
$server->register('feed.delete', function($feed_id) {

    return Model\remove_feed($feed_id);
});

// Update a feed
$server->register('feed.update', function($feed_id) {

    return Model\update_feed($feed_id);
});

// Get all items for a specific feed
$server->register('item.feed.list', function ($feed_id, $offset = null, $limit = null) {

    return Model\get_feed_items($feed_id, $offset, $limit);
});

// Count all feed items
$server->register('item.feed.count', function ($feed_id) {

    return Model\count_feed_items($feed_id);
});

// Get all bookmark items
$server->register('item.bookmark.list', function ($offset = null, $limit = null) {

    return Model\get_bookmarks($offset, $limit);
});

// Count bookmarks
$server->register('item.bookmark.count', function () {

    return Model\count_bookmarks();
});

// Add a bookmark
$server->register('item.bookmark.create', function ($item_id) {

    return Model\set_bookmark_value($item_id, 1);
});

// Remove a bookmark
$server->register('item.bookmark.delete', function ($item_id) {

    return Model\set_bookmark_value($item_id, 0);
});

// Get all unread items
$server->register('item.list_unread', function ($offset = null, $limit = null) {

    return Model\get_unread_items($offset, $limit);
});

// Count all unread items
$server->register('item.count_unread', function () {

    return Model\count_items('unread');
});

// Get all read items
$server->register('item.list_read', function ($offset = null, $limit = null) {

    return Model\get_read_items($offset, $limit);
});

// Count all read items
$server->register('item.count_read', function () {

    return Model\count_items('read');
});

// Get one item
$server->register('item.info', function ($item_id) {

    return Model\get_item($item_id);
});

// Delete an item
$server->register('item.delete', function($item_id) {

    return Model\set_item_removed($item_id);
});

// Mark item as read
$server->register('item.mark_as_read', function($item_id) {

    return Model\set_item_read($item_id);
});

// Mark item as unread
$server->register('item.mark_as_unread', function($item_id) {

    return Model\set_item_unread($item_id);
});

// Flush all read items
$server->register('item.flush', function() {

    return Model\mark_as_removed();
});

// Mark all unread items as read
$server->register('item.mark_all_as_read', function() {

    return Model\mark_as_read();
});

echo $server->execute();