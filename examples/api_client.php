<?php

require '../vendor/JsonRPC/Client.php';

use JsonRPC\Client;

$client = new Client('http://webapps/miniflux/jsonrpc.php');
$client->authentication('admin', 'd4i/Tanb55426mi');

$result = $client->execute('feed.create', array('url' => 'http://bbc.co.uk/news'));
var_dump($result);

$result = $client->execute('feed.list');
print_r($result);

$feed_id = $result[0]['id'];

$result = $client->execute('feed.update', array('feed_id' => $feed_id));
var_dump($result);

$result = $client->execute('feed.info', array('feed_id' => $feed_id));
print_r($result);

$result = $client->execute('feed.delete', array('feed_id' => $feed_id));
var_dump($result);

$result = $client->execute('item.list_read');
print_r($result);

$result = $client->execute('item.list_unread', array('offset' => 5, 'limit' => 2));
print_r($result);

if (count($result)) {

    $result = $client->execute('item.bookmark.create', array('item_id' => $result[0]['id']));
    var_dump($result);
}

$result = $client->execute('item.bookmark.list');
print_r($result);

var_dump($client->execute('item.set_list_status', array('status' => 'read', 'items' => array('57cdb841', '8ef6744e'))));
