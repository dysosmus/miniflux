<?php

namespace Model;

require_once 'vendor/PicoFeed/Filter.php';
require_once 'vendor/PicoFeed/Client.php';
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
require_once 'vendor/SimpleValidator/Validators/Integer.php';

use SimpleValidator\Validator;
use SimpleValidator\Validators;
use PicoFeed\Import;
use PicoFeed\Reader;
use PicoFeed\Export;


const DB_VERSION     = 15;
const HTTP_USERAGENT = 'Miniflux - http://miniflux.net';
const HTTP_FAKE_USERAGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36';
const LIMIT_ALL      = -1;


function get_languages()
{
    return array(
        'cs_CZ' => t('Czech'),
        'en_US' => t('English'),
        'fr_FR' => t('French'),
        'de_DE' => t('German'),
        'it_IT' => t('Italian'),
        'zh_CN' => t('Simplified Chinese'),
    );
}


function get_themes()
{
    $themes = array(
        'original' => t('Original')
    );

    if (file_exists(THEME_DIRECTORY)) {

        $dir = new \DirectoryIterator(THEME_DIRECTORY);

        foreach ($dir as $fileinfo) {

            if (! $fileinfo->isDot() && $fileinfo->isDir()) {
                $themes[$dir->getFilename()] = ucfirst($dir->getFilename());
            }
        }
    }

    return $themes;
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


function get_paging_options()
{
    return array(
        50 => 50,
        100 => 100,
        150 => 150,
        200 => 200,
        250 => 250,
    );
}


function write_debug()
{
    if (DEBUG) {

        $data = '';

        foreach (\PicoFeed\Logging::$messages as $line) {
            $data .= $line.PHP_EOL;
        }

        file_put_contents(DEBUG_FILENAME, $data);
    }
}


function generate_token()
{
    if (ini_get('open_basedir') === '') {
        return substr(base64_encode(file_get_contents('/dev/urandom', false, null, 0, 20)), 0, 15);
    }
    else {
        return substr(base64_encode(uniqid(mt_rand(), true)), 0, 20);
    }
}


function new_tokens()
{
    $values = array(
        'api_token' => generate_token(),
        'feed_token' => generate_token(),
    );

    return \PicoTools\singleton('db')->table('config')->update($values);
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

        write_debug();

        return true;
    }

    write_debug();

    return false;
}


function import_feed($url, $grabber = false)
{
    $reader = new Reader;
    $resource = $reader->download($url, '', '', HTTP_TIMEOUT, HTTP_USERAGENT);

    $parser = $reader->getParser();

    if ($parser !== false) {

        $parser->grabber = $grabber;
        $feed = $parser->execute();

        if ($feed === false || ! $feed->title || ! $feed->url) {
            write_debug();
            return false;
        }

        $db = \PicoTools\singleton('db');

        if (! $db->table('feeds')->eq('feed_url', $reader->getUrl())->count()) {

            // Etag and LastModified are added the next update
            $rs = $db->table('feeds')->save(array(
                'title' => $feed->title,
                'site_url' => $feed->url,
                'feed_url' => $reader->getUrl(),
                'download_content' => $grabber ? 1 : 0
            ));

            if ($rs) {

                $feed_id = $db->getConnection()->getLastId();
                update_items($feed_id, $feed->items, $grabber);
                write_debug();

                return (int) $feed_id;
            }
        }
    }

    write_debug();

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

    return true;
}


function update_feed($feed_id)
{
    $feed = get_feed($feed_id);
    if (empty($feed)) return false;

    $reader = new Reader;

    $resource = $reader->download(
        $feed['feed_url'],
        $feed['last_modified'],
        $feed['etag'],
        HTTP_TIMEOUT,
        HTTP_USERAGENT
    );

    // Update the `last_checked` column each time, HTTP cache or not
    update_feed_last_checked($feed_id);

    if (! $resource->isModified()) {
        write_debug();
        return true;
    }

    $parser = $reader->getParser();

    if ($parser !== false) {

        if ($feed['download_content']) {

            // Don't fetch previous items, only new one
            $parser->grabber_ignore_urls = \PicoTools\singleton('db')
                                                ->table('items')
                                                ->eq('feed_id', $feed_id)
                                                ->findAllByColumn('url');

            $parser->grabber = true;
            $parser->grabber_timeout = HTTP_TIMEOUT;
            $parser->grabber_user_agent = HTTP_FAKE_USERAGENT;
        }

        $result = $parser->execute();

        if ($result !== false) {

            update_feed_cache_infos($feed_id, $resource->getLastModified(), $resource->getEtag());
            update_items($feed_id, $result->items, $parser->grabber);
            write_debug();

            return true;
        }
    }

    write_debug();

    return false;
}


function get_feeds_id($limit = LIMIT_ALL)
{
    $table_feeds = \PicoTools\singleton('db')->table('feeds')
                                             ->eq('enabled', 1)
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


function get_empty_feeds()
{
    $feeds = \PicoTools\singleton('db')
        ->table('feeds')
        ->columns('feeds.id', 'feeds.title', 'COUNT(items.id) AS nb_items')
        ->join('items', 'feed_id', 'id')
        ->isNull('feeds.last_checked')
        ->groupBy('feeds.id')
        ->findAll();

    foreach ($feeds as $key => &$feed) {

        if ($feed['nb_items'] > 0) {
            unset($feeds[$key]);
        }
    }

    return $feeds;
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


function parse_content_with_readability($content, $url)
{
    require_once 'vendor/Readability/Readability.php';
    require_once 'vendor/PicoFeed/Encoding.php';

    if (! empty($content)) {

        $content = \PicoFeed\Encoding::toUTF8($content);
        $readability = new \Readability($content, $url);

        if ($readability->init()) {
            return $readability->getContent()->innerHTML;
        }
    }

    return '';
}


function download_content($url)
{
    require_once 'vendor/PicoFeed/Grabber.php';

    $client = \PicoFeed\Client::create();
    $client->url = $url;
    $client->timeout = HTTP_TIMEOUT;
    $client->user_agent = HTTP_FAKE_USERAGENT;
    $client->execute();

    $html = $client->getContent();

    if (! empty($html)) {

        // Try first with PicoFeed grabber and with Readability after
        $grabber = new \PicoFeed\Grabber($url);
        $grabber->html = $html;

        if ($grabber->parse()) {
            $content = $grabber->content;
        }

        if (empty($content)) {
            $content = parse_content_with_readability($html, $url);
        }

        // Filter content
        $filter = new \PicoFeed\Filter($content, $url);
        return $filter->execute();
    }

    return '';
}


function download_item($item_id)
{
    $item = get_item($item_id);
    $content = download_content($item['url']);

    if (! empty($content)) {

        if (! get_config_value('nocontent')) {

            // Save content
            \PicoTools\singleton('db')
                ->table('items')
                ->eq('id', $item['id'])
                ->save(array('content' => $content));
        }

        return array(
            'result' => true,
            'content' => $content
        );
    }

    return array(
        'result' => false,
        'content' => ''
    );
}


function remove_feed($feed_id)
{
    // Items are removed by a sql constraint
    return \PicoTools\singleton('db')->table('feeds')->eq('id', $feed_id)->remove();
}


function remove_feeds()
{
    return \PicoTools\singleton('db')->table('feeds')->remove();
}


function enable_feed($feed_id)
{
    return \PicoTools\singleton('db')->table('feeds')->eq('id', $feed_id)->save((array('enabled' => 1)));
}


function disable_feed($feed_id)
{
    return \PicoTools\singleton('db')->table('feeds')->eq('id', $feed_id)->save((array('enabled' => 0)));
}


function enable_grabber_feed($feed_id)
{
    return \PicoTools\singleton('db')->table('feeds')->eq('id', $feed_id)->save((array('download_content' => 1)));
}


function disable_grabber_feed($feed_id)
{
    return \PicoTools\singleton('db')->table('feeds')->eq('id', $feed_id)->save((array('download_content' => 0)));
}


function get_items($status, $offset = null, $limit = null)
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.bookmark',
            'items.feed_id',
            'items.status',
            'items.content',
            'feeds.site_url',
            'feeds.title AS feed_title'
        )
        ->join('feeds', 'id', 'feed_id')
        ->eq('status', $status)
        ->desc('updated')
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}


function count_items($status)
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('status', $status)
        ->count();
}


function count_bookmarks()
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('bookmark', 1)
        ->in('status', array('read', 'unread'))
        ->count();
}


function get_bookmarks($offset = null, $limit = null)
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.status',
            'items.content',
            'items.feed_id',
            'feeds.site_url',
            'feeds.title AS feed_title'
        )
        ->join('feeds', 'id', 'feed_id')
        ->in('status', array('read', 'unread'))
        ->eq('bookmark', 1)
        ->desc('updated')
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}


function count_feed_items($feed_id)
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('feed_id', $feed_id)
        ->in('status', array('read', 'unread'))
        ->count();
}


function get_feed_items($feed_id, $offset = null, $limit = null)
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.feed_id',
            'items.status',
            'items.content',
            'items.bookmark',
            'feeds.site_url'
        )
        ->join('feeds', 'id', 'feed_id')
        ->in('status', array('read', 'unread'))
        ->eq('feed_id', $feed_id)
        ->desc('updated')
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}


function get_item($id)
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('id', $id)
        ->findOne();
}


function get_nav_item($item, $status = array('unread'), $bookmark = array(1, 0), $feed_id = null)
{
    $query = \PicoTools\singleton('db')
        ->table('items')
        ->columns('id', 'status', 'title', 'bookmark')
        ->neq('status', 'removed')
        ->desc('updated');

    if ($feed_id) $query->eq('feed_id', $feed_id);

    $items = $query->findAll();

    $next_item = null;
    $previous_item = null;

    for ($i = 0, $ilen = count($items); $i < $ilen; $i++) {

        if ($items[$i]['id'] == $item['id']) {

            if ($i > 0) {

                $j = $i - 1;

                while ($j >= 0) {

                    if (in_array($items[$j]['status'], $status) && in_array($items[$j]['bookmark'], $bookmark)) {
                        $previous_item = $items[$j];
                        break;
                    }

                    $j--;
                }
            }

            if ($i < ($ilen - 1)) {

                $j = $i + 1;

                while ($j < $ilen) {

                    if (in_array($items[$j]['status'], $status) && in_array($items[$j]['bookmark'], $bookmark)) {
                        $next_item = $items[$j];
                        break;
                    }

                    $j++;
                }
            }

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
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'removed', 'content' => ''));
}


function set_item_read($id)
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'read'));
}


function set_item_unread($id)
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'unread'));
}


function set_items_status($status, array $items)
{
    if (! in_array($status, array('read', 'unread', 'removed'))) return false;

    return \PicoTools\singleton('db')
        ->table('items')
        ->in('id', $items)
        ->save(array('status' => $status));
}


function set_bookmark_value($id, $value)
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('bookmark' => $value));
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


// Mark all items as read
function mark_as_read()
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('status', 'unread')
        ->save(array('status' => 'read'));
}


// Mark only specified items as read
function mark_items_as_read(array $items_id)
{
    \PicoTools\singleton('db')->startTransaction();

    foreach ($items_id as $id) {
        set_item_read($id);
    }

    \PicoTools\singleton('db')->closeTransaction();
}


function mark_as_removed()
{
    return \PicoTools\singleton('db')
        ->table('items')
        ->eq('status', 'read')
        ->eq('bookmark', 0)
        ->save(array('status' => 'removed', 'content' => ''));
}


function autoflush()
{
    $autoflush = get_config_value('autoflush');

    if ($autoflush) {

        \PicoTools\singleton('db')
            ->table('items')
            ->eq('bookmark', 0)
            ->eq('status', 'read')
            ->lt('updated', strtotime('-'.$autoflush.'day'))
            ->save(array('status' => 'removed', 'content' => ''));
    }
}


function update_items($feed_id, array $items, $grabber = false)
{
    $nocontent = (bool) get_config_value('nocontent');

    $items_in_feed = array();
    $db = \PicoTools\singleton('db');

    $db->startTransaction();

    foreach ($items as $item) {

        // Item parsed correctly?
        if ($item->id) {

            // Insert only new item
            if ($db->table('items')->eq('id', $item->id)->count() !== 1) {

                if (! $item->content && ! $nocontent && $grabber) {
                    $item->content = download_content($item->url);
                }

                $db->table('items')->save(array(
                    'id' => $item->id,
                    'title' => $item->title,
                    'url' => $item->url,
                    'updated' => $item->updated,
                    'author' => $item->author,
                    'content' => $nocontent ? '' : $item->content,
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

        $removed_items = \PicoTools\singleton('db')
            ->table('items')
            ->columns('id')
            ->notin('id', $items_in_feed)
            ->eq('status', 'removed')
            ->eq('feed_id', $feed_id)
            ->desc('updated')
            ->findAllByColumn('id');

        // Keep a buffer of 2 items
        // It's workaround for buggy feeds (cache issue with some Wordpress plugins)
        if (is_array($removed_items)) {

            $items_to_remove = array_slice($removed_items, 2);

            if (! empty($items_to_remove)) {

                \PicoTools\singleton('db')
                    ->table('items')
                    ->in('id', $items_to_remove)
                    ->eq('status', 'removed')
                    ->eq('feed_id', $feed_id)
                    ->remove();
            }
        }
    }

    $db->closeTransaction();
}


function get_config_value($name)
{
    if (! isset($_SESSION)) {

        return \PicoTools\singleton('db')->table('config')->findOneColumn($name);
    }
    else {

        if (! isset($_SESSION['config'])) {
            $_SESSION['config'] = get_config();
        }

        if (isset($_SESSION['config'][$name])) {
            return $_SESSION['config'][$name];
        }
    }

    return null;
}


function get_config()
{
    return \PicoTools\singleton('db')
        ->table('config')
        ->columns('username', 'language', 'autoflush', 'nocontent', 'items_per_page', 'theme', 'api_token', 'feed_token')
        ->findOne();
}


function get_user($username)
{
    return \PicoTools\singleton('db')
        ->table('config')
        ->columns('username', 'password', 'language')
        ->eq('username', $username)
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

        $user = get_user($values['username']);

        if ($user && \password_verify($values['password'], $user['password'])) {

            unset($user['password']);

            $_SESSION['user'] = $user;
            $_SESSION['config'] = get_config();
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
            new Validators\Required('autoflush', t('Value required')),
            new Validators\Required('items_per_page', t('Value required')),
            new Validators\Integer('items_per_page', t('Must be an integer')),
            new Validators\Required('theme', t('Value required')),
        ));
    }
    else {

        $v = new Validator($values, array(
            new Validators\Required('username', t('The user name is required')),
            new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
            new Validators\Required('autoflush', t('Value required')),
            new Validators\Required('items_per_page', t('Value required')),
            new Validators\Integer('items_per_page', t('Must be an integer')),
            new Validators\Required('theme', t('Value required')),
        ));
    }

    return array(
        $v->execute(),
        $v->getErrors()
    );
}


function save_config(array $values)
{
    // Update the password if needed
    if (! empty($values['password'])) {

        $values['password'] = \password_hash($values['password'], PASSWORD_BCRYPT);

    } else {

        unset($values['password']);
    }

    unset($values['confirmation']);

    // Reload configuration in session
    $_SESSION['config'] = $values;

    // Reload translations for flash session message
    \PicoTools\Translator\load($values['language']);

    // If the user does not want content of feeds, remove it in previous ones
    if (isset($values['nocontent']) && (bool) $values['nocontent']) {
        \PicoTools\singleton('db')->table('items')->update(array('content' => ''));
    }

    return \PicoTools\singleton('db')->table('config')->update($values);
}
