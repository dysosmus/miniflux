<?php

namespace Model;

require_once 'vendor/PicoFeed/Export.php';
require_once 'vendor/PicoFeed/Import.php';
require_once 'vendor/PicoFeed/Parser.php';
require_once 'vendor/PicoFeed/Reader.php';
require_once 'vendor/SimpleValidator/Validator.php';
require_once 'vendor/SimpleValidator/Base.php';
require_once 'vendor/SimpleValidator/Validators/Required.php';
require_once 'vendor/SimpleValidator/Validators/Unique.php';
require_once 'vendor/SimpleValidator/Validators/MaxLength.php';
require_once 'vendor/SimpleValidator/Validators/MinLength.php';
require_once 'vendor/SimpleValidator/Validators/Integer.php';
require_once 'vendor/SimpleValidator/Validators/Equals.php';

use SimpleValidator\Validator;
use SimpleValidator\Validators;
use PicoFeed\Import;
use PicoFeed\Reader;
use PicoFeed\Export;


function export_feeds()
{
    $opml = new Export(get_feeds());
    return $opml->execute();
}


function import_feeds($content)
{
    $import = new Import($content);
    $feeds = $import->execute();

    if ($feeds) {

        $db = \PicoTools\singleton('db');

        $db->startTransaction();

        foreach ($feeds as $feed) {

            if (! $db->table('feeds')->eq('feed_url', $feed->feed_url)->count()) {

                $db->table('feeds')->save(array(
                    'title' => $feed->title,
                    'site_url' => $feed->site_url,
                    'feed_url' => $feed->feed_url
                ));
            }
        }

        $db->closeTransaction();

        return true;
    }

    return false;
}


function import_feed($url)
{
    $reader = new Reader;
    $resource = $reader->download($url, '', '', HTTP_TIMEOUT, APP_USERAGENT);

    $parser = $reader->getParser();

    if ($parser !== false) {

        $feed = $parser->execute();

        if ($feed === false) return false;
        if (! $feed->title || ! $feed->url) return false;

        $db = \PicoTools\singleton('db');

        if (! $db->table('feeds')->eq('feed_url', $reader->getUrl())->count()) {

            // Etag and LastModified are added the next update
            $rs = $db->table('feeds')->save(array(
                'title' => $feed->title,
                'site_url' => $feed->url,
                'feed_url' => $reader->getUrl()
            ));

            if ($rs) {

                $feed_id = $db->getConnection()->getLastId();
                update_items($feed_id, $feed->items);
            }
        }

        return true;
    }

    return false;
}


function update_feeds()
{
    foreach (get_feeds_id() as $feed_id) {

        update_feed($feed_id);
    }
}


function update_feed($feed_id)
{
    $feed = get_feed($feed_id);

    $reader = new Reader;

    $resource = $reader->download(
        $feed['feed_url'],
        $feed['last_modified'],
        $feed['etag'],
        HTTP_TIMEOUT,
        APP_USERAGENT
    );

    if (! $resource->isModified()) {

        return true;
    }

    $parser = $reader->getParser();

    if ($parser !== false) {

        $feed = $parser->execute();

        if ($feed !== false) {

            update_feed_cache_infos($feed_id, $resource->getLastModified(), $resource->getEtag());
            update_items($feed_id, $feed->items);

            return true;
        }
    }

    return false;
}


function get_feeds_id()
{
    return \PicoTools\singleton('db')
        ->table('feeds')
        ->asc('updated')
        ->listing('id', 'id');
}


function get_feeds()
{
    return \PicoTools\singleton('db')
        ->table('feeds')
        ->asc('title')
        ->findAll();
}


function get_feed($feed_id)
{
    return \PicoTools\singleton('db')
        ->table('feeds')
        ->eq('id', $feed_id)
        ->findOne();
}


function update_feed_cache_infos($feed_id, $last_modified, $etag)
{
    \PicoTools\singleton('db')
        ->table('feeds')
        ->eq('id', $feed_id)
        ->save(array(
            'last_modified' => $last_modified,
            'etag' => $etag
        ));
}


function remove_feed($feed_id)
{
    $db = \PicoTools\singleton('db');
    $db->table('items')->eq('feed_id', $feed_id)->remove();

    return $db->table('feeds')->eq('id', $feed_id)->remove();
}


function get_unread_items()
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->columns('items.id', 'items.title', 'items.updated', 'items.url', 'feeds.site_url', 'items.content')
        ->join('feeds', 'id', 'feed_id')
        ->eq('status', 'unread')
        ->desc('updated')
        ->findAll();
}


function get_read_items()
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->columns('items.id', 'items.title', 'items.updated', 'items.url', 'feeds.site_url')
        ->join('feeds', 'id', 'feed_id')
        ->eq('status', 'read')
        ->desc('updated')
        ->findAll();
}


function get_item($id)
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('id', $id)
        ->findOne();
}


function get_nav_item($item)
{
    $next_item = \PicoTools\singleton('db')
        ->table('items')
        ->columns('items.id')
        ->eq('status', 'unread')
        ->lt('updated', $item['updated'])
        ->desc('updated')
        ->findOne();

    $previous_item = \PicoTools\singleton('db')
        ->table('items')
        ->columns('items.id')
        ->eq('status', 'unread')
        ->gt('updated', $item['updated'])
        ->asc('updated')
        ->findOne();

    return array(
        'next' => $next_item,
        'previous' => $previous_item
    );
}


function set_item_read($id)
{
    \PicoTools\singleton('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'read'));
}


function set_item_unread($id)
{
    \PicoTools\singleton('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'unread'));
}


function switch_item_status($id)
{
    $item = \PicoTools\singleton('db')
        ->table('items')
        ->columns('status')
        ->eq('id', $id)
        ->findOne();

    if ($item['status'] == 'unread') {

        \PicoTools\singleton('db')
            ->table('items')
            ->eq('id', $id)
            ->save(array('status' => 'read'));

        return 'read';
    }
    else {

        \PicoTools\singleton('db')
            ->table('items')
            ->eq('id', $id)
            ->save(array('status' => 'unread'));

        return 'unread';
    }

    return '';
}


function mark_as_read()
{
    \PicoTools\singleton('db')
        ->table('items')
        ->eq('status', 'unread')
        ->save(array('status' => 'read'));
}


function flush_unread()
{
    \PicoTools\singleton('db')
        ->table('items')
        ->eq('status', 'unread')
        ->save(array('status' => 'removed'));
}


function flush_read()
{
    \PicoTools\singleton('db')
        ->table('items')
        ->eq('status', 'read')
        ->save(array('status' => 'removed'));
}


function update_items($feed_id, array $items)
{
    $db = \PicoTools\singleton('db');

    $db->startTransaction();

    foreach ($items as $item) {

        if ($item->id && ! $db->table('items')->eq('id', $item->id)->count()) {

            $db->table('items')->save(array(
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->url,
                'updated' => $item->updated,
                'author' => $item->author,
                'content' => $item->content,
                'status' => 'unread',
                'feed_id' => $feed_id
            ));
        }
    }

    $db->closeTransaction();
}


function get_config()
{
    return \PicoTools\singleton('db')
        ->table('config')
        ->columns('username', 'history')
        ->findOne();
}


function get_user()
{
    return \PicoTools\singleton('db')
        ->table('config')
        ->columns('username', 'password')
        ->findOne();
}


function validate_login(array $values)
{
    $v = new Validator($values, array(
        new Validators\Required('username', 'The user name is required'),
        new Validators\MaxLength('username', 'The maximum length is 50 characters', 50),
        new Validators\Required('password', 'The password is required')
    ));

    $result = $v->execute();
    $errors = $v->getErrors();

    if ($result) {

        $user = get_user();

        if ($user && \password_verify($values['password'], $user['password'])) {

            $_SESSION['user'] = $user;
        }
        else {

            $result = false;
            $errors['login'] = 'Bad username or password';
        }
    }

    return array(
        $result,
        $errors
    );
}


function validate_config_update(array $values)
{
    if (! empty($values['password'])) {

        $v = new Validator($values, array(
            new Validators\Required('username', 'The user name is required'),
            new Validators\MaxLength('username', 'The maximum length is 50 characters', 50),
            new Validators\Required('password', 'The password is required'),
            new Validators\MinLength('password', 'The minimum length is 6 characters', 6),
            new Validators\Required('confirmation', 'The confirmation is required'),
            new Validators\Equals('password', 'confirmation', 'Passwords doesn\'t match')
        ));
    }
    else {

        $v = new Validator($values, array(
            new Validators\Required('username', 'The user name is required'),
            new Validators\MaxLength('username', 'The maximum length is 50 characters', 50)
        ));
    }

    return array(
        $v->execute(),
        $v->getErrors()
    );
}


function save_config(array $values)
{
    if (! empty($values['password'])) {

        $values['password'] = \password_hash($values['password'], PASSWORD_BCRYPT);
    }
    else {

        unset($values['password']);
    }

    unset($values['confirmation']);

    return \PicoTools\singleton('db')->table('config')->update($values);
}
