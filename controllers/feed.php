<?php

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoFarad\Template;

// Refresh all feeds, used when Javascript is disabled
Router\get_action('refresh-all', function() {

    Model\Feed\refresh_all();
    Session\flash(t('Your subscriptions are updated'));
    Response\redirect('?action=unread');
});

// Edit feed form
Router\get_action('edit-feed', function() {

    $id = Request\int_param('feed_id');

    Response\html(Template\layout('edit_feed', array(
        'values' => Model\Feed\get($id),
        'errors' => array(),
        'menu' => 'feeds',
        'title' => t('Edit subscription')
    )));
});

// Submit edit feed form
Router\post_action('edit-feed', function() {

    $values = Request\values();
    list($valid, $errors) = Model\Feed\validate_modification($values);

    if ($valid) {

        if (Model\Feed\update($values)) {
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

    if ($id && Model\Feed\disable_grabber($id)) {
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

    if ($id && Model\Feed\enable_grabber($id)) {
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
        'feed' => Model\Feed\get($id),
        'menu' => 'feeds',
        'title' => t('Confirmation')
    )));
});

// Disable a feed
Router\get_action('disable-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id && Model\Feed\disable($id)) {
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

    if ($id && Model\Feed\enable($id)) {
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
        'feed' => Model\Feed\get($id),
        'menu' => 'feeds',
        'title' => t('Confirmation')
    )));
});

// Remove a feed
Router\get_action('remove-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id && Model\Feed\remove($id)) {
        Session\flash(t('This subscription has been removed successfully.'));
    }
    else {
        Session\flash_error(t('Unable to remove this subscription.'));
    }

    Response\redirect('?action=feeds');
});

// Refresh one feed and redirect to unread items
Router\get_action('refresh-feed', function() {

    Model\Feed\refresh(Request\int_param('feed_id'));
    Response\redirect('?action=unread');
});

// Ajax call to refresh one feed
Router\post_action('refresh-feed', function() {

    $feed_id = Request\int_param('feed_id', 0);

    Response\json(array(
        'feed_id' => $feed_id,
        'result' => Model\Feed\refresh($feed_id),
        'items_count' => Model\Feed\count_items($feed_id),
    ));
});

// Display all feeds
Router\get_action('feeds', function() {

    if (! Request\int_param('disable_empty_feeds_check')) {

        $empty_feeds = Model\Feed\get_all_empty();

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
        'feeds' => Model\Feed\get_all_item_counts(),
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
    $result = Model\Feed\create($url, $values['download_content']);

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
    Response\xml(Model\Feed\export_opml());
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

    if (Model\Feed\import_opml(Request\file_content('file'))) {

        Session\flash(t('Your feeds have been imported.'));
        Response\redirect('?action=feeds&disable_empty_feeds_check=1');
    }
    else {

        Session\flash_error(t('Unable to import your OPML file.'));
        Response\redirect('?action=import');
    }
});
