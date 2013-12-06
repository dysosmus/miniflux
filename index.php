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

    $ignore_actions = array('js', 'login', 'google-auth', 'google-redirect-auth', 'mozilla-auth');

    if (! isset($_SESSION['user']) && ! in_array($action, $ignore_actions)) {
        Response\redirect('?action=login');
    }

    // Load translations
    $language = Model\get_config_value('language') ?: 'en_US';
    if ($language !== 'en_US') PicoTools\Translator\load($language);

    // HTTP secure headers
    $frame_src = \PicoFeed\Filter::$iframe_whitelist;
    $frame_src[] = 'https://login.persona.org';

    Response\csp(array(
        'media-src' => '*',
        'img-src' => '*',
        'frame-src' => $frame_src
    ));

    Response\xframe();
    Response\xss();
    Response\nosniff();
});


// Javascript assets
Router\get_action('js', function() {

    $data = file_get_contents('assets/js/app.js');
    $data .= file_get_contents('assets/js/feed.js');
    $data .= file_get_contents('assets/js/item.js');
    $data .= file_get_contents('assets/js/event.js');
    $data .= file_get_contents('assets/js/nav.js');
    $data .= 'Miniflux.App.Run();';

    Response\js($data);
});


// Logout and destroy session
Router\get_action('logout', function() {

    Session\close();
    Response\redirect('?action=login');
});


// Display form login
Router\get_action('login', function() {

    if (isset($_SESSION['user'])) Response\redirect('?action=unread');

    Response\html(Template\load('login', array(
        'google_auth_enable' => Model\get_config_value('auth_google_token') !== '',
        'mozilla_auth_enable' => Model\get_config_value('auth_mozilla_token') !== '',
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
        'google_auth_enable' => Model\get_config_value('auth_google_token') !== '',
        'mozilla_auth_enable' => Model\get_config_value('auth_mozilla_token') !== '',
        'errors' => $errors,
        'values' => $values
    )));
});


// Show help
Router\get_action('show-help', function() {

    Response\html(Template\load('show_help'));
});


// Show item
Router\get_action('show', function() {

    $id = Request\param('id');
    $menu = Request\param('menu');
    $item = Model\get_item($id);
    $feed = Model\get_feed($item['feed_id']);

    Model\set_item_read($id);

    switch ($menu) {
        case 'unread':
            $nav = Model\get_nav_item($item);
            $nb_unread_items = Model\count_items('unread');
            break;
        case 'history':
            $nav = Model\get_nav_item($item, array('read'));
            break;
        case 'feed-items':
            $nav = Model\get_nav_item($item, array('unread', 'read'), array(1, 0), $item['feed_id']);
            break;
        case 'bookmarks':
            $nav = Model\get_nav_item($item, array('unread', 'read'), array(1));
            break;
    }

    Response\html(Template\layout('show_item', array(
        'nb_unread_items' => isset($nb_unread_items) ? $nb_unread_items : null,
        'item' => $item,
        'feed' => $feed,
        'item_nav' => isset($nav) ? $nav : null,
        'menu' => $menu,
        'title' => $item['title']
    )));
});


// Mark item as read and redirect to the listing page
Router\get_action('mark-item-read', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'unread');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\set_item_read($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'&feed_id='.$feed_id.'#item-'.$id);
});


// Mark item as unread and redirect to the listing page
Router\get_action('mark-item-unread', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'history');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\set_item_unread($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'&feed_id='.$feed_id.'#item-'.$id);
});


// Mark item as removed and redirect to the listing page
Router\get_action('mark-item-removed', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'history');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\set_item_removed($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'&feed_id='.$feed_id);
});


// Ajax call to download an item (fetch the full content from the original website)
Router\post_action('download-item', function() {

    Response\json(Model\download_item(Request\param('id')));
});


// Ajax call to mark item read
Router\post_action('mark-item-read', function() {

    $id = Request\param('id');
    Model\set_item_read($id);
    Response\json(array('Ok'));
});


// Ajax call to mark item unread
Router\post_action('mark-item-unread', function() {

    $id = Request\param('id');
    Model\set_item_unread($id);
    Response\json(array('Ok'));
});


// Ajax call change item status
Router\post_action('change-item-status', function() {

    $id = Request\param('id');

    Response\json(array(
        'item_id' => $id,
        'status' => Model\switch_item_status($id)
    ));
});


// Ajax call to add or remove a bookmark
Router\post_action('bookmark', function() {

    $id = Request\param('id');
    $value = Request\int_param('value');

    Model\set_bookmark_value($id, $value);

    Response\json(array('id' => $id, 'value' => $value));
});


// Add new bookmark
Router\get_action('bookmark', function() {

    $id = Request\param('id');
    $menu = Request\param('menu', 'unread');
    $source = Request\param('source', 'unread');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\set_bookmark_value($id, Request\int_param('value'));

    if ($source === 'show') {
        Response\Redirect('?action=show&menu='.$menu.'&id='.$id);
    }

    Response\Redirect('?action='.$menu.'&offset='.$offset.'&feed_id='.$feed_id.'#item-'.$id);
});


// Display history page
Router\get_action('history', function() {

    $offset = Request\int_param('offset', 0);
    $nb_items = Model\count_items('read');

    Response\html(Template\layout('history', array(
        'items' => Model\get_items(
            'read',
            $offset,
            Model\get_config_value('items_per_page'),
            'updated',
            Model\get_config_value('items_sorting_direction')
        ),
        'order' => '',
        'direction' => '',
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
    $order = Request\param('order', 'updated');
    $direction = Request\param('direction', Model\get_config_value('items_sorting_direction'));
    $items = Model\get_feed_items($feed_id, $offset, Model\get_config_value('items_per_page'), $order, $direction);

    Response\html(Template\layout('feed_items', array(
        'order' => $order,
        'direction' => $direction,
        'feed' => $feed,
        'items' => $items,
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\get_config_value('items_per_page'),
        'menu' => 'feed-items',
        'title' => '('.$nb_items.') '.$feed['title']
    )));
});


// Display bookmarks page
Router\get_action('bookmarks', function() {

    $offset = Request\int_param('offset', 0);
    $nb_items = Model\count_bookmarks();

    Response\html(Template\layout('bookmarks', array(
        'order' => '',
        'direction' => '',
        'items' => Model\get_bookmarks($offset, Model\get_config_value('items_per_page')),
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\get_config_value('items_per_page'),
        'menu' => 'bookmarks',
        'title' => t('Bookmarks').' ('.$nb_items.')'
    )));
});


// Mark all unread items as read
Router\get_action('mark-as-read', function() {

    Model\mark_as_read();
    Response\redirect('?action=unread');
});


// Mark all unread items as read for a specific feed
Router\get_action('mark-feed-as-read', function() {

    $feed_id = Request\int_param('feed_id');
    Model\mark_feed_as_read($feed_id);
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
    Session\flash(t('Your subscriptions are updated'));
    Response\redirect('?action=unread');
});


// Edit feed form
Router\get_action('edit-feed', function() {

    $id = Request\int_param('feed_id');

    Response\html(Template\layout('edit_feed', array(
        'values' => Model\get_feed($id),
        'errors' => array(),
        'menu' => 'feeds',
        'title' => t('Edit subscription')
    )));
});


// Submit edit feed form
Router\post_action('edit-feed', function() {

    $values = Request\values();
    list($valid, $errors) = Model\validate_feed_modification($values);

    if ($valid) {

        if (Model\save_feed($values)) {
            Session\flash(t('Your subscription has been updated.'));
        }
        else {
            Session\flash_error(t('Unable to edit your subscription.'));
        }

        Response\redirect('?action=feeds');
    }

    Response\html(Template\layout('edit_feed', array(
        'values' => $values,
        'errors' => $errors,
        'menu' => 'feeds',
        'title' => t('Edit subscription')
    )));
});


// Disable content grabber for a feed
Router\get_action('disable-grabber-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id && Model\disable_grabber_feed($id)) {
        Session\flash(t('The content grabber is disabled successfully.'));
    }
    else {
        Session\flash_error(t('Unable to disable the content grabber for this subscription.'));
    }

    Response\redirect('?action=feeds');
});


// Enable content grabber for a feed
Router\get_action('enable-grabber-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id && Model\enable_grabber_feed($id)) {
        Session\flash(t('The content grabber is enabled successfully.'));
    }
    else {
        Session\flash_error(t('Unable to activate the content grabber for this subscription.'));
    }

    Response\redirect('?action=feeds');
});


// Confirmation box to disable a feed
Router\get_action('confirm-disable-feed', function() {

    $id = Request\int_param('feed_id');

    Response\html(Template\layout('confirm_disable_feed', array(
        'feed' => Model\get_feed($id),
        'menu' => 'feeds',
        'title' => t('Confirmation')
    )));
});


// Disable a feed
Router\get_action('disable-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id && Model\disable_feed($id)) {
        Session\flash(t('This subscription has been disabled successfully.'));
    }
    else {
        Session\flash_error(t('Unable to disable this subscription.'));
    }

    Response\redirect('?action=feeds');
});


// Enable a feed
Router\get_action('enable-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id && Model\enable_feed($id)) {
        Session\flash(t('This subscription has been enabled successfully.'));
    }
    else {
        Session\flash_error(t('Unable to enable this subscription.'));
    }

    Response\redirect('?action=feeds');
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
    Response\redirect('?action=unread');
});


// Ajax call to refresh one feed
Router\post_action('refresh-feed', function() {

    $id = Request\int_param('feed_id', 0);

    if ($id) {
        $result = Model\update_feed($id);
    }

    Response\json(array('feed_id' => $id, 'result' => $result));
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


// Add a feed with the form or directly from the url, it can be used by a bookmarklet by example
Router\action('subscribe', function() {

    if (Request\param('url')) {
        $values = array();
        $url = Request\param('url');
    }
    else {
        $values = Request\values();
        $url = isset($values['url']) ? $values['url'] : '';
    }

    $values += array('download_content' => 0);
    $url = trim($url);
    $result = Model\import_feed($url, $values['download_content']);

    if ($result) {

        Session\flash(t('Subscription added successfully.'));
        Response\redirect('?action=feeds');
    }
    else {

        Session\flash_error(t('Unable to find a subscription.'));
    }

    Response\html(Template\layout('add', array(
        'values' => array('url' => $url),
        'menu' => 'feeds',
        'title' => t('Subscriptions')
    )));
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


// Re-generate tokens
Router\get_action('generate-tokens', function() {

    Model\new_tokens();
    Response\redirect('?action=config#api');
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


// Flush console messages
Router\get_action('flush-console', function() {

    @unlink(DEBUG_FILENAME);
    Response\redirect('?action=console');
});


// Display console
Router\get_action('console', function() {

    Response\html(Template\layout('console', array(
        'content' => @file_get_contents(DEBUG_FILENAME),
        'title' => t('Console')
    )));
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
        'sorting_options' => Model\get_sorting_directions(),
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
        'sorting_options' => Model\get_sorting_directions(),
        'menu' => 'config',
        'title' => t('Preferences')
    )));
});


// Link to a Google Account (redirect)
Router\get_action('google-redirect-link', function() {

    require 'vendor/PicoTools/AuthProvider.php';
    Response\Redirect(AuthProvider\google_get_url(Helper\get_current_base_url(), '?action=google-link'));
});


// Link to a Google Account (association)
Router\get_action('google-link', function() {

    require 'vendor/PicoTools/AuthProvider.php';

    list($valid, $token) = AuthProvider\google_validate();

    if ($valid) {
        Model\save_auth_token('google', $token);
        Session\flash(t('Your Google Account is linked to Miniflux.'));
    }
    else {
        Session\flash_error(t('Unable to link Miniflux to your Google Account.'));
    }

    Response\redirect('?action=config');
});


// Authenticate with a Google Account (redirect)
Router\get_action('google-redirect-auth', function() {

    require 'vendor/PicoTools/AuthProvider.php';
    Response\Redirect(AuthProvider\google_get_url(Helper\get_current_base_url(), '?action=google-auth'));
});


// Authenticate with a Google Account (callback url)
Router\get_action('google-auth', function() {

    require 'vendor/PicoTools/AuthProvider.php';

    list($valid, $token) = AuthProvider\google_validate();

    if ($valid && $token === Model\get_config_value('auth_google_token')) {

        $_SESSION['user'] = array(
            'username' => Model\get_config_value('username'),
            'language' => Model\get_config_value('language'),
        );

        Response\redirect('?action=unread');
    }
    else {

        Response\html(Template\load('login', array(
            'google_auth_enable' => Model\get_config_value('auth_google_token') !== '',
            'mozilla_auth_enable' => Model\get_config_value('auth_mozilla_token') !== '',
            'errors' => array('login' => t('Unable to authenticate with Google')),
            'values' => array()
        )));
    }
});


// Authenticate with a Mozilla Persona (ajax check)
Router\post_action('mozilla-auth', function() {

    require 'vendor/PicoTools/AuthProvider.php';

    list($valid, $token) = AuthProvider\mozilla_validate(Request\value('token'));

    if ($valid && $token === Model\get_config_value('auth_mozilla_token')) {

        $_SESSION['user'] = array(
            'username' => Model\get_config_value('username'),
            'language' => Model\get_config_value('language'),
        );

        Response\text('?action=unread');
    }
    else {
        Response\text("?action=login");
    }
});


// Link Miniflux to a Mozilla Account (ajax check)
Router\post_action('mozilla-link', function() {

    require 'vendor/PicoTools/AuthProvider.php';

    list($valid, $token) = AuthProvider\mozilla_validate(Request\value('token'));

    if ($valid) {
        Model\save_auth_token('mozilla', $token);
        Session\flash(t('Your Mozilla Persona Account is linked to Miniflux.'));
    }
    else {
        Session\flash_error(t('Unable to link Miniflux to your Mozilla Persona Account.'));
    }

    Response\text("?action=config");
});


// Remove account link
Router\get_action('unlink-account-provider', function() {
    Model\remove_auth_token(Request\param('type'));
    Response\redirect('?action=config');
});

Router\get_action('update-miniflux', function() {
    Model\do_miniflux_update();
});


// Display unread items
Router\notfound(function() {

    Model\autoflush();

    $order = Request\param('order', 'updated');
    $direction = Request\param('direction', Model\get_config_value('items_sorting_direction'));
    $offset = Request\int_param('offset', 0);
    $items = Model\get_items('unread', $offset, Model\get_config_value('items_per_page'), $order, $direction);
    $nb_items = Model\count_items('unread');

    if ($nb_items === 0) Response\redirect('?action=feeds&nothing_to_read=1');

    Response\html(Template\layout('unread_items', array(
        'order' => $order,
        'direction' => $direction,
        'items' => $items,
        'nb_items' => $nb_items,
        'nb_unread_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\get_config_value('items_per_page'),
        'title' => 'Miniflux ('.$nb_items.')',
        'menu' => 'unread'
    )));
});
