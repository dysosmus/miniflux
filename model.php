<?php

namespace Model;

require_once 'vendor/PicoFeed/Export.php';
require_once 'vendor/PicoFeed/Import.php';
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


function get_languages()
{
    return array(
        'en_US' => t('English'),
        'fr_FR' => t('French')
    );
}


function get_autoflush_options()
{
    return array(
        '0' => t('Never'),
        '1' => t('After %d day', 1),
        '5' => t('After %d days', 5),
        '15' => t('After %d days', 15),
        '30' => t('After %d days', 30)
    );
}


function encode_item_id($input)
{
    return strtr(base64_encode($input), '+/=', '-_,');
}


function decode_item_id($input)
{
    return base64_decode(strtr($input, '-_,', '+/='));
}


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


function update_feeds($limit = LIMIT_ALL)
{
    $feeds_id = get_feeds_id($limit);

    foreach ($feeds_id as $feed_id) {
        update_feed($feed_id);
    }

    // Auto-vacuum for people using the cronjob
    \PicoTools\singleton('db')->getConnection()->exec('VACUUM');
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

    // Update the `last_checked` column each time, HTTP cache or not
    update_feed_last_checked($feed_id);

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


function get_feeds_id($limit = LIMIT_ALL)
{
    $table_feeds = \PicoTools\singleton('db')->table('feeds')
                                             ->asc('last_checked');

    if ($limit !== LIMIT_ALL) {

        $table_feeds->limit((int)$limit);
    }

    return $table_feeds->listing('id', 'id');
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


function update_feed_last_checked($feed_id)
{
    \PicoTools\singleton('db')
        ->table('feeds')
        ->eq('id', $feed_id)
        ->save(array(
            'last_checked' => time()
        ));
}


function update_feed_cache_infos($feed_id, $last_modified, $etag)
{
    \PicoTools\singleton('db')
        ->table('feeds')
        ->eq('id', $feed_id)
        ->save(array(
            'last_modified' => $last_modified,
            'etag'          => $etag
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
    $unread_items = \PicoTools\singleton('db')
        ->table('items')
        ->columns('items.id')
        ->eq('status', 'unread')
        ->desc('updated')
        ->findAll();

    $next_item = null;
    $previous_item = null;

    for ($i = 0, $ilen = count($unread_items); $i < $ilen; $i++) {

        if ($unread_items[$i]['id'] == $item['id']) {

            if ($i > 0) $previous_item = $unread_items[$i - 1];
            if ($i < ($ilen - 1)) $next_item = $unread_items[$i + 1];
            break;
        }
    }

    return array(
        'next' => $next_item,
        'previous' => $previous_item
    );
}


function set_item_removed($id)
{
    \PicoTools\singleton('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'removed'));
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


function mark_as_removed()
{
    \PicoTools\singleton('db')
        ->table('items')
        ->eq('status', 'read')
        ->save(array('status' => 'removed'));
}


function autoflush()
{
    $autoflush = \PicoTools\singleton('db')->table('config')->findOneColumn('autoflush');

    if ($autoflush) {

        \PicoTools\singleton('db')
            ->table('items')
            ->eq('status', 'read')
            ->lt('updated', strtotime('-'.$autoflush.'day'))
            ->save(array('status' => 'removed'));
    }
}


function update_items($feed_id, array $items)
{
    $nocontent = (bool)\PicoTools\singleton('db')->table('config')->findOneColumn('nocontent');

    $items_in_feed = array();
    $db = \PicoTools\singleton('db');

    $db->startTransaction();

    foreach ($items as $item) {

        // Item parsed correctly?
        if ($item->id) {

            // Insert only new item
            if ($db->table('items')->eq('id', $item->id)->count() !== 1) {
                $content = $nocontent ? '' : $item->content;

                $db->table('items')->save(array(
                    'id' => $item->id,
                    'title' => $item->title,
                    'url' => $item->url,
                    'updated' => $item->updated,
                    'author' => $item->author,
                    'content' => $content,
                    'status' => 'unread',
                    'feed_id' => $feed_id
                ));
            }

            // Items inside this feed
            $items_in_feed[] = $item->id;
        }
    }

    // Remove from the database items marked as "removed"
    // and not present inside the feed
    if (! empty($items_in_feed)) {

        \PicoTools\singleton('db')
            ->table('items')
            ->notin('id', $items_in_feed)
            ->eq('status', 'removed')
            ->eq('feed_id', $feed_id)
            ->remove();
    }

    $db->closeTransaction();
}


function get_config()
{
    return \PicoTools\singleton('db')
        ->table('config')
        ->columns('username', 'language', 'autoflush', 'nocontent')
        ->findOne();
}


function get_user()
{
    return \PicoTools\singleton('db')
        ->table('config')
        ->columns('username', 'password', 'language')
        ->findOne();
}


function validate_login(array $values)
{
    $v = new Validator($values, array(
        new Validators\Required('username', t('The user name is required')),
        new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
        new Validators\Required('password', t('The password is required'))
    ));

    $result = $v->execute();
    $errors = $v->getErrors();

    if ($result) {

        $user = get_user();

        if ($user && \password_verify($values['password'], $user['password'])) {

            unset($user['password']);
            $_SESSION['user'] = $user;
        }
        else {

            $result = false;
            $errors['login'] = t('Bad username or password');
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
            new Validators\Required('username', t('The user name is required')),
            new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
            new Validators\Required('password', t('The password is required')),
            new Validators\MinLength('password', t('The minimum length is 6 characters'), 6),
            new Validators\Required('confirmation', t('The confirmation is required')),
            new Validators\Equals('password', 'confirmation', t('Passwords doesn\'t match')),
            new Validators\Required('autoflush', t('Value required'))
        ));
    }
    else {

        $v = new Validator($values, array(
            new Validators\Required('username', t('The user name is required')),
            new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50)
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

    $_SESSION['user']['language'] = $values['language'];
    unset($_COOKIE['language']);

    \PicoTools\Translator\load($values['language']);

    // if the user does not want content of feeds, remote it in previous ones
    if ((bool)$values['nocontent']) {
        \PicoTools\singleton('db')->table('items')->update(array('content'=>''));
    }

    return \PicoTools\singleton('db')->table('config')->update($values);
}
