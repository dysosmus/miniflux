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

        PicoFarad\Response\redirect('?action=login');
    }

    Response\csp(array(
        'img-src' => '*'
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


Router\get_action('read', function() {

    $id = Request\param('id');

    Model\set_item_read($id);

    Response\html(Template\layout('read_item', array(
        'item' => Model\get_item($id)
    )));
});


Router\post_action('read', function() {

    $id = Request\param('id');

    Model\set_item_read($id);

    Response\json(array('Ok'));
});


Router\get_action('history', function() {

    Response\html(Template\layout('read_items', array(
        'items' => Model\get_read_items(),
        'menu' => 'history'
    )));
});


Router\get_action('confirm-remove', function() {

    $id = Request\int_param('feed_id');

    Response\html(Template\layout('confirm_remove', array(
        'feed' => Model\get_feed($id),
        'menu' => 'feeds'
    )));
});


Router\get_action('remove', function() {

    $id = Request\int_param('feed_id');

    if ($id && Model\remove_feed($id)) {

        Session\flash('This subscription has been removed successfully');
    }
    else {

        Session\flash_error('Unable to remove this subscription');
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


Router\get_action('ajax-refresh-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id) {

        Response\json(array('feed_id' => $id, 'result' => Model\update_feed($id)));
    }

    Response\json(array('feed_id' => 0, 'result' => false));
});


Router\get_action('flush-unread', function() {

    Model\flush_unread();
    Response\redirect('?action=unread');
});


Router\get_action('flush-history', function() {

    Model\flush_read();
    Response\redirect('?action=history');
});


Router\get_action('refresh-all', function() {

    Model\update_feeds();
    Session\flash('Your subscriptions are updated');
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

        Session\flash('Subscription added successfully.');
        Response\redirect('?action=feeds');
    }
    else {

        Session\flash_error('Unable to find a subscription.');
    }

    Response\html(Template\layout('add', array(
        'values' => array('url' => $_POST['url']),
        'errors' => array('url' => 'Unable to find a news feed.'),
        'menu' => 'feeds'
    )));
});


Router\get_action('download-db', function() {

    Response\force_download('db.sqlite.gz');
    Response\binary(gzencode(file_get_contents('data/db.sqlite')));
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

        Session\flash('Your feeds are imported.');
    }
    else {

        Session\flash_error('Unable to import your OPML file.');
    }

    Response\redirect('?action=feeds');
});


Router\get_action('config', function() {

    Response\html(Template\layout('config', array(
        'errors' => array(),
        'values' => Model\get_config(),
        'menu' => 'config'
    )));
});


Router\post_action('config', function() {

    $values = Request\values();
    list($valid, $errors) = Model\validate_config_update($values);

    if ($valid) {

        if (Model\save_config($values)) {

            Session\flash('Your preferences are updated.');
        }
        else {

            Session\flash_error('Unable to update your preferences.');
        }

        Response\redirect('?action=config');
    }

    Response\html(Template\layout('config', array(
        'errors' => $errors,
        'values' => $values,
        'menu' => 'config'
    )));
});


Router\notfound(function() {

    $items = Model\get_unread_items();

    if (empty($items)) {

        Response\redirect('?action=feeds&nothing_to_read=1');
    }

    Response\html(Template\layout('unread_items', array(
        'items' => $items,
        'menu' => 'unread'
    )));
});