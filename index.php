<?php

require 'common.php';
require 'vendor/PicoTools/Template.php';
require 'vendor/PicoTools/Helper.php';
require 'vendor/PicoFarad/Response.php';
require 'vendor/PicoFarad/Request.php';
require 'vendor/PicoFarad/Session.php';
require 'vendor/PicoFarad/Router.php';

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoTools\Template;


Session\open(dirname($_SERVER['PHP_SELF']));


Router\before(function($action) {

    if ($action !== 'login' && ! isset($_SESSION['user'])) {

        Response\redirect('?action=login');
    }

    $language = 'en_US';

    if (isset($_SESSION['user']['language'])) {

        $language = $_SESSION['user']['language'];
    }
    else if (isset($_COOKIE['language'])) {

        $language = $_COOKIE['language'];
    }

    if ($language !== 'en_US') {

        PicoTools\Translator\load($language);
    }

    setcookie('language', $language, time()+365*24*3600, dirname($_SERVER['PHP_SELF']));

    Response\csp(array(
        'img-src' => '*',
        'frame-src' => 'http://www.youtube.com https://www.youtube.com http://player.vimeo.com https://player.vimeo.com'
    ));

    Response\xframe();
    Response\xss();
    Response\nosniff();
});


Router\get_action('logout', function() {

    Session\close();

    Response\redirect('?action=login');
});


Router\get_action('login', function() {

    if (isset($_SESSION['user'])) {

        Response\redirect('./index.php');
    }

    Response\html(Template\load('login', array(
        'errors' => array(),
        'values' => array()
    )));
});


Router\post_action('login', function() {

    $values = Request\values();
    list($valid, $errors) = Model\validate_login($values);

    if ($valid) {

        Response\redirect('?action=default');
    }

    Response\html(Template\load('login', array(
        'errors' => $errors,
        'values' => $values
    )));
});


Router\get_action('show', function() {

    $id = Model\decode_item_id(Request\param('id'));

    Response\html(Template\layout('read_item', array(
        'item' => Model\get_item($id)
    )));
});


Router\get_action('show_starred_item', function() {
    $id = Model\decode_item_id(Request\param('id'));

    Response\html(Template\layout('starred_item', array(
        'item' => Model\get_item($id)
    )));
});



Router\get_action('read', function() {

    $id = Model\decode_item_id(Request\param('id'));
    $item = Model\get_item($id);
    $nav = Model\get_nav_item($item); // must be placed before set_item_read()

    Model\set_item_read($id);

    Response\html(Template\layout('read_item', array(
        'item' => $item,
        'item_nav' => $nav
    )));
});


Router\get_action('read_starred', function() {

    $id = Model\decode_item_id(Request\param('id'));
    $item = Model\get_item($id);
    $nav = Model\get_nav_starred_item($item); // must be placed before set_item_read()

    Model\set_item_read($id);

    Response\html(Template\layout('starred_item', array(
        'item' => $item,
        'item_nav' => $nav
    )));
});


Router\get_action('mark-item-read', function() {

    $id = Model\decode_item_id(Request\param('id'));
    Model\set_item_read($id);
    Response\Redirect('?action='.$_SESSION['MODE']);
});


Router\get_action('mark-item-unread', function() {

    $id = Model\decode_item_id(Request\param('id'));
    Model\set_item_unread($id);
    Response\Redirect('?action='.$_SESSION['MODE']);
});


Router\get_action('mark-item-removed', function() {

    $id = Model\decode_item_id(Request\param('id'));
    Model\set_item_removed($id);
    Response\Redirect('?action='.$_SESSION['MODE']);
});


Router\post_action('mark-item-read', function() {

    $id = Model\decode_item_id(Request\param('id'));
    Model\set_item_read($id);
    Response\json(array('Ok'));
});


Router\post_action('mark-item-unread', function() {

    $id = Model\decode_item_id(Request\param('id'));
    Model\set_item_unread($id);
    Response\json(array('Ok'));
});


Router\get_action('mark-item-starred', function() {

    $id = Model\decode_item_id(Request\param('id'));
    Model\set_item_starred($id);
    Response\Redirect('?action='.$_SESSION['MODE']);
});


Router\get_action('mark-item-unstarred', function() {

    $id = Model\decode_item_id(Request\param('id'));
    Model\set_item_unstarred($id);
    Response\Redirect('?action='.$_SESSION['MODE']);
});


Router\post_action('change-item-status', function() {

    $id = Model\decode_item_id(Request\param('id'));

    Response\json(array(
        'item_id' => urlencode($id),
        'status' => Model\switch_item_status($id)
    ));
});


Router\get_action('history', function() {
	$_SESSION['MODE']='history';
    Response\html(Template\layout('history', array(
        'items' => Model\get_read_items(),
        'menu' => 'history'
    )));
});


Router\get_action('starred', function() {
	$_SESSION['MODE']='starred';
    Response\html(Template\layout('starred', array(
        'items' => Model\get_starred_items(),
        'menu' => 'starred'
    )));
});



Router\get_action('confirm-remove', function() {

    $id = Request\int_param('feed_id');

    Response\html(Template\layout('confirm_remove_feed', array(
        'feed' => Model\get_feed($id),
        'menu' => 'feeds'
    )));
});


Router\get_action('remove', function() {

    $id = Request\int_param('feed_id');

    if ($id && Model\remove_feed($id)) {

        Session\flash(t('This subscription has been removed successfully.'));
    }
    else {

        Session\flash_error(t('Unable to remove this subscription.'));
    }

    Response\redirect('?action=feeds');
});


Router\get_action('refresh-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id) {

        Model\update_feed($id);
    }

    Response\redirect('?action=unread');
});


Router\post_action('refresh-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id) {

        Response\json(array('feed_id' => $id, 'result' => Model\update_feed($id)));
    }

    Response\json(array('feed_id' => 0, 'result' => false));
});


Router\get_action('mark-as-read', function() {

    Model\mark_as_read();
    Response\redirect('?action=unread');
});


Router\get_action('confirm-flush-history', function() {

    Response\html(Template\layout('confirm_flush_items', array(
        'menu' => 'history'
    )));
});


Router\get_action('flush-history', function() {

    Model\mark_as_removed();
    Response\redirect('?action=history');
});


Router\get_action('refresh-all', function() {

    Model\update_feeds();
    Session\flash(t('Your subscriptions are updated'));
    Response\redirect('?action=unread');
});


Router\get_action('feeds', function() {

    Response\html(Template\layout('feeds', array(
        'feeds' => Model\get_feeds(),
        'nothing_to_read' => Request\int_param('nothing_to_read'),
        'menu' => 'feeds'
    )));
});


Router\get_action('add', function() {

    Response\html(Template\layout('add', array(
        'values' => array(),
        'errors' => array(),
        'menu' => 'feeds'
    )));
});


Router\post_action('add', function() {

    if (Model\import_feed($_POST['url'])) {

        Session\flash(t('Subscription added successfully.'));
        Response\redirect('?action=feeds');
    }
    else {

        Session\flash_error(t('Unable to find a subscription.'));
    }

    Response\html(Template\layout('add', array(
        'values' => array('url' => $_POST['url']),
        'menu' => 'feeds'
    )));
});


Router\get_action('optimize-db', function() {

    \PicoTools\singleton('db')->getConnection()->exec('VACUUM');
    Response\redirect('?action=config');
});


Router\get_action('download-db', function() {

    Response\force_download('db.sqlite.gz');
    Response\binary(gzencode(file_get_contents(get_db_filename())));
});


Router\get_action('export', function() {

    Response\force_download('feeds.opml');
    Response\xml(Model\export_feeds());
});


Router\get_action('import', function() {

    Response\html(Template\layout('import', array(
        'errors' => array(),
        'menu' => 'feeds'
    )));
});


Router\post_action('import', function() {

    if (Model\import_feeds(Request\file_content('file'))) {

        Session\flash(t('Your feeds have been imported.'));
        Response\redirect('?action=feeds');
    }
    else {

        Session\flash_error(t('Unable to import your OPML file.'));
        Response\redirect('?action=import');
    }
});


Router\get_action('config', function() {

    Response\html(Template\layout('config', array(
        'errors' => array(),
        'values' => Model\get_config(),
        'db_size' => filesize(get_db_filename()),
        'languages' => Model\get_languages(),
        'autoflush_options' => Model\get_autoflush_options(),
        'menu' => 'config'
    )));
});


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
        'db_size' => filesize(get_db_filename()),
        'languages' => Model\get_languages(),
        'autoflush_options' => Model\get_autoflush_options(),
        'menu' => 'config'
    )));
});


Router\notfound(function() {
	$_SESSION['MODE']='default';

    Model\autoflush();

    $items = Model\get_unread_items();

    if (empty($items)) {

        Response\redirect('?action=feeds&nothing_to_read=1');
    }

    Response\html(Template\layout('unread_items', array(
        'items' => $items,
        'menu' => 'unread'
    )));
});
