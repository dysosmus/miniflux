<?php

require 'common.php';
require 'vendor/PicoTools/Template.php';
require 'vendor/PicoTools/Helper.php';
require 'vendor/PicoFarad/Response.php';
require 'vendor/PicoFarad/Request.php';
require 'vendor/PicoFarad/Session.php';
require 'vendor/PicoFarad/Router.php';
require 'helpers.php';

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoTools\Template;


if (SESSION_SAVE_PATH !== '') session_save_path(SESSION_SAVE_PATH);
Session\open(dirname($_SERVER['PHP_SELF']));


// Called before each action
Router\before(function($action) {

    if ($action !== 'login' && ! isset($_SESSION['user'])) {

        Response\redirect('?action=login');
    }

    // Load translations
    $language = Model\get_config_value('language') ?: 'en_US';
    if ($language !== 'en_US') PicoTools\Translator\load($language);

    // HTTP secure headers
    Response\csp(array(
        'media-src' => '*',
        'img-src' => '*',
        'frame-src' => implode(' ', array(
            'http://www.youtube.com',
            'https://www.youtube.com',
            'http://player.vimeo.com',
            'https://player.vimeo.com',
            'http://www.dailymotion.com',
            'https://www.dailymotion.com',
        ))
    ));

    Response\xframe();
    Response\xss();
    Response\nosniff();
});


// Logout and destroy session
Router\get_action('logout', function() {

    Session\close();
    Response\redirect('?action=login');
});


// Display form login
Router\get_action('login', function() {

    if (isset($_SESSION['user'])) Response\redirect('index.php');

    Response\html(Template\load('login', array(
        'errors' => array(),
        'values' => array()
    )));
});


// Check credentials and redirect to unread items
Router\post_action('login', function() {

    $values = Request\values();
    list($valid, $errors) = Model\validate_login($values);

    if ($valid) Response\redirect('?action=unread');

    Response\html(Template\load('login', array(
        'errors' => $errors,
        'values' => $values
    )));
});


// Show help
Router\get_action('show-help', function() {

    Response\html(Template\load('show_help'));
});


// Show item without bottom nav
Router\get_action('show', function() {

    $id = Model\decode_item_id(Request\param('id'));
    $item = Model\get_item($id);
    $feed = Model\get_feed($item['feed_id']);

    Model\set_item_read($id);

    Response\html(Template\layout('show_item', array(
        'item' => $item,
        'feed' => $feed,
        'menu' => 'show'
    )));
});


// Show item with bottom nav
Router\get_action('read', function() {

    $id = Model\decode_item_id(Request\param('id'));
    $item = Model\get_item($id);
    $feed = Model\get_feed($item['feed_id']);
    $nav = Model\get_nav_item($item); // must be placed before set_item_read()

    Model\set_item_read($id);

    Response\html(Template\layout('show_item', array(
        'item' => $item,
        'feed' => $feed,
        'item_nav' => $nav,
        'menu' => 'read'
    )));
});


// Mark item as read and redirect to the listing page
Router\get_action('mark-item-read', function() {

    $id = Model\decode_item_id(Request\param('id'));
    $redirect = Request\param('redirect', 'unread');
    $offset = Request\int_param('offset', 0);

    Model\set_item_read($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset);
});


// Mark item as unread and redirect to the listing page
Router\get_action('mark-item-unread', function() {

    $id = Model\decode_item_id(Request\param('id'));
    $redirect = Request\param('redirect', 'history');
    $offset = Request\int_param('offset', 0);

    Model\set_item_unread($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset);
});


// Mark item as removed and redirect to the listing page
Router\get_action('mark-item-removed', function() {

    $id = Model\decode_item_id(Request\param('id'));
    $redirect = Request\param('redirect', 'history');
    $offset = Request\int_param('offset', 0);

    Model\set_item_removed($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset);
});


// Ajax call to mark item read
Router\post_action('mark-item-read', function() {

    $id = Model\decode_item_id(Request\param('id'));
    Model\set_item_read($id);
    Response\json(array('Ok'));
});


// Ajax call to mark item unread
Router\post_action('mark-item-unread', function() {

    $id = Model\decode_item_id(Request\param('id'));
    Model\set_item_unread($id);
    Response\json(array('Ok'));
});


// Ajax call to bookmark an item
Router\post_action('bookmark-item', function() {

    $id = Model\decode_item_id(Request\param('id'));
    Model\bookmark_item($id);
    Response\json(array('Ok'));
});


// Ajax call change item status
Router\post_action('change-item-status', function() {

    $id = Model\decode_item_id(Request\param('id'));

    Response\json(array(
        'item_id' => Model\encode_item_id($id),
        'status' => Model\switch_item_status($id)
    ));
});


// Add new bookmark
Router\get_action('bookmark', function() {

    $param_id = Request\param('id');
    $id = Model\decode_item_id($param_id);
    $redirect = Request\param('redirect', 'unread');
    $offset = Request\int_param('offset', 0);

    Model\set_bookmark_value($id, Request\int_param('value'));

    if ($redirect === 'show') {

        Response\Redirect('?action=show&id='.$param_id);
    }
    else if ($redirect === 'read') {

        Response\Redirect('?action=read&id='.$param_id);
    }

    Response\Redirect('?action='.$redirect.'&offset='.$offset);
});


// Display history page
Router\get_action('history', function() {

    $offset = Request\int_param('offset', 0);
    $nb_items = Model\count_items('read');

    Response\html(Template\layout('history', array(
        'items' => Model\get_read_items($offset, Model\get_config_value('items_per_page')),
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\get_config_value('items_per_page'),
        'menu' => 'history',
        'title' => t('History').' ('.$nb_items.')'
    )));
});


// Display feed items page
Router\get_action('feed-items', function() {

    $feed_id = Request\int_param('feed_id', 0);
    $offset = Request\int_param('offset', 0);
    $nb_items = Model\count_feed_items($feed_id);
    $feed = Model\get_feed($feed_id);

    Response\html(Template\layout('feed_items', array(
        'feed' => $feed,
        'items' => Model\get_feed_items($feed_id, $offset, Model\get_config_value('items_per_page')),
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\get_config_value('items_per_page'),
        'menu' => 'feeds',
        'title' => '('.$nb_items.') '.$feed['title']
    )));
});


// Display bookmarks page
Router\get_action('bookmarks', function() {

    $offset = Request\int_param('offset', 0);
    $nb_items = Model\count_bookmarks();

    Response\html(Template\layout('bookmarks', array(
        'items' => Model\get_bookmarks($offset, Model\get_config_value('items_per_page')),
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\get_config_value('items_per_page'),
        'menu' => 'bookmarks',
        'title' => t('Bookmarks').' ('.$nb_items.')'
    )));
});


// Confirmation box to remove a feed
Router\get_action('confirm-remove-feed', function() {

    $id = Request\int_param('feed_id');

    Response\html(Template\layout('confirm_remove_feed', array(
        'feed' => Model\get_feed($id),
        'menu' => 'feeds',
        'title' => t('Confirmation')
    )));
});


// Remove a feed
Router\get_action('remove-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id && Model\remove_feed($id)) {

        Session\flash(t('This subscription has been removed successfully.'));
    }
    else {

        Session\flash_error(t('Unable to remove this subscription.'));
    }

    Response\redirect('?action=feeds');
});


// Refresh one feed and redirect to unread items
Router\get_action('refresh-feed', function() {

    $id = Request\int_param('feed_id');
    if ($id) Model\update_feed($id);
    Model\write_debug();
    Response\redirect('?action=unread');
});


// Ajax call to refresh one feed
Router\post_action('refresh-feed', function() {

    $id = Request\int_param('feed_id', 0);

    if ($id) {

        $result = Model\update_feed($id);
        Model\write_debug();
    }

    Response\json(array('feed_id' => $id, 'result' => $result));
});


// Mark all unread items as read
Router\get_action('mark-as-read', function() {

    Model\mark_as_read();
    Response\redirect('?action=unread');
});


// Mark sent items id as read (Ajax request)
Router\post_action('mark-items-as-read', function(){

    Model\mark_items_as_read(Request\values());
    Response\json(array('OK'));
});


// Confirmation box to flush history
Router\get_action('confirm-flush-history', function() {

    Response\html(Template\layout('confirm_flush_items', array(
        'menu' => 'history',
        'title' => t('Confirmation')
    )));
});


// Flush history
Router\get_action('flush-history', function() {

    Model\mark_as_removed();
    Response\redirect('?action=history');
});


// Refresh all feeds, used when Javascript is disabled
Router\get_action('refresh-all', function() {

    Model\update_feeds();
    Model\write_debug();

    Session\flash(t('Your subscriptions are updated'));
    Response\redirect('?action=unread');
});


// Display all feeds
Router\get_action('feeds', function() {

    if (! Request\int_param('disable_empty_feeds_check')) {

        $empty_feeds = Model\get_empty_feeds();

        if (! empty($empty_feeds)) {

            $listing = array();

            foreach ($empty_feeds as &$feed) {
                $listing[] = '"'.$feed['title'].'"';
            }

            $message = t(
                'There is %d empty feeds, there is maybe an error: %s...',
                count($empty_feeds),
                implode(', ', array_slice($listing, 0, 5))
            );

            Session\flash_error($message);
        }
    }

    Response\html(Template\layout('feeds', array(
        'feeds' => Model\get_feeds(),
        'nothing_to_read' => Request\int_param('nothing_to_read'),
        'menu' => 'feeds',
        'title' => t('Subscriptions')
    )));
});


// Display form to add one feed
Router\get_action('add', function() {

    Response\html(Template\layout('add', array(
        'values' => array(),
        'errors' => array(),
        'menu' => 'feeds',
        'title' => t('New subscription')
    )));
});


// Add the feed
Router\post_action('add', function() {

    $result = Model\import_feed(trim($_POST['url']));
    Model\write_debug();

    if ($result) {

        Session\flash(t('Subscription added successfully.'));
        Response\redirect('?action=feeds');
    }
    else {

        Session\flash_error(t('Unable to find a subscription.'));
    }

    Response\html(Template\layout('add', array(
        'values' => array('url' => $_POST['url']),
        'menu' => 'feeds',
        'title' => t('Subscriptions')
    )));
});


// Optimize the database manually
Router\get_action('optimize-db', function() {

    \PicoTools\singleton('db')->getConnection()->exec('VACUUM');
    Response\redirect('?action=config');
});


// Download the compressed database
Router\get_action('download-db', function() {

    Response\force_download('db.sqlite.gz');
    Response\binary(gzencode(file_get_contents(DB_FILENAME)));
});


// OPML export
Router\get_action('export', function() {

    Response\force_download('feeds.opml');
    Response\xml(Model\export_feeds());
});


// OPML import form
Router\get_action('import', function() {

    Response\html(Template\layout('import', array(
        'errors' => array(),
        'menu' => 'feeds',
        'title' => t('OPML Import')
    )));
});


// OPML importation
Router\post_action('import', function() {

    if (Model\import_feeds(Request\file_content('file'))) {

        Session\flash(t('Your feeds have been imported.'));
        Response\redirect('?action=feeds&disable_empty_feeds_check=1');
    }
    else {

        Session\flash_error(t('Unable to import your OPML file.'));
        Response\redirect('?action=import');
    }
});


// Display preferences page
Router\get_action('config', function() {

    Response\html(Template\layout('config', array(
        'errors' => array(),
        'values' => Model\get_config(),
        'db_size' => filesize(DB_FILENAME),
        'languages' => Model\get_languages(),
        'autoflush_options' => Model\get_autoflush_options(),
        'paging_options' => Model\get_paging_options(),
        'theme_options' => Model\get_themes(),
        'menu' => 'config',
        'title' => t('Preferences')
    )));
});


// Update preferences
Router\post_action('config', function() {

    $values = Request\values() + array('nocontent' => 0);
    list($valid, $errors) = Model\validate_config_update($values);

    if ($valid) {

        if (Model\save_config($values)) {

            Session\flash(t('Your preferences are updated.'));
        }
        else {

            Session\flash_error(t('Unable to update your preferences.'));
        }

        Response\redirect('?action=config');
    }

    Response\html(Template\layout('config', array(
        'errors' => $errors,
        'values' => $values,
        'db_size' => filesize(DB_FILENAME),
        'languages' => Model\get_languages(),
        'autoflush_options' => Model\get_autoflush_options(),
        'paging_options' => Model\get_paging_options(),
        'theme_options' => Model\get_themes(),
        'menu' => 'config',
        'title' => t('Preferences')
    )));
});


// Display unread items
Router\notfound(function() {

    Model\autoflush();

    $offset = Request\int_param('offset', 0);
    $items = Model\get_unread_items($offset, Model\get_config_value('items_per_page'));
    $nb_items = Model\count_items('unread');;

    if ($nb_items === 0) Response\redirect('?action=feeds&nothing_to_read=1');

    Response\html(Template\layout('unread_items', array(
        'items' => $items,
        'nb_items' => $nb_items,
        'nb_unread_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\get_config_value('items_per_page'),
        'title' => 'miniflux ('.$nb_items.')',
        'menu' => 'unread'
    )));
});
