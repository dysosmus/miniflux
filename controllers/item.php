<?php

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoFarad\Template;

// Display unread items
Router\get_action('unread', function() {

    Model\Item\autoflush();

    $order = Request\param('order', 'updated');
    $direction = Request\param('direction', Model\Config\get('items_sorting_direction'));
    $offset = Request\int_param('offset', 0);
    $items = Model\Item\get_all('unread', $offset, Model\Config\get('items_per_page'), $order, $direction);
    $nb_items = Model\Item\count_by_status('unread');

    if ($nb_items === 0) {

        $action = Model\Config\get('redirect_nothing_to_read');
        Response\redirect('?action='.$action.'&nothing_to_read=1');
    }

    Response\html(Template\layout('unread_items', array(
        'order' => $order,
        'direction' => $direction,
        'items' => $items,
        'nb_items' => $nb_items,
        'nb_unread_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\Config\get('items_per_page'),
        'title' => 'Miniflux ('.$nb_items.')',
        'menu' => 'unread'
    )));
});

// Show item
Router\get_action('show', function() {

    $id = Request\param('id');
    $menu = Request\param('menu');
    $item = Model\Item\get($id);
    $feed = Model\Feed\get($item['feed_id']);

    Model\Item\set_read($id);

    switch ($menu) {
        case 'unread':
            $nav = Model\Item\get_nav($item);
            $nb_unread_items = Model\Item\count_by_status('unread');
            break;
        case 'history':
            $nav = Model\Item\get_nav($item, array('read'));
            break;
        case 'feed-items':
            $nav = Model\Item\get_nav($item, array('unread', 'read'), array(1, 0), $item['feed_id']);
            break;
        case 'bookmarks':
            $nav = Model\Item\get_nav($item, array('unread', 'read'), array(1));
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

// Display feed items page
Router\get_action('feed-items', function() {

    $feed_id = Request\int_param('feed_id', 0);
    $offset = Request\int_param('offset', 0);
    $nb_items = Model\Item\count_by_feed($feed_id);
    $feed = Model\Feed\get($feed_id);
    $order = Request\param('order', 'updated');
    $direction = Request\param('direction', Model\Config\get('items_sorting_direction'));
    $items = Model\Item\get_all_by_feed($feed_id, $offset, Model\Config\get('items_per_page'), $order, $direction);

    Response\html(Template\layout('feed_items', array(
        'order' => $order,
        'direction' => $direction,
        'feed' => $feed,
        'items' => $items,
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\Config\get('items_per_page'),
        'menu' => 'feed-items',
        'title' => '('.$nb_items.') '.$feed['title']
    )));
});

// Ajax call to download an item (fetch the full content from the original website)
Router\post_action('download-item', function() {

    Response\json(Model\Item\download_content_id(Request\param('id')));
});

// Ajax call change item status
Router\post_action('change-item-status', function() {

    $id = Request\param('id');

    Response\json(array(
        'item_id' => $id,
        'status' => Model\Item\switch_status($id)
    ));
});

// Ajax call to mark item read
Router\post_action('mark-item-read', function() {

    Model\Item\set_read(Request\param('id'));
    Response\json(array('Ok'));
});

// Ajax call to mark item unread
Router\post_action('mark-item-unread', function() {

    Model\Item\set_unread(Request\param('id'));
    Response\json(array('Ok'));
});

// Mark all unread items as read
Router\get_action('mark-as-read', function() {

    Model\Item\mark_all_as_read();
    Response\redirect('?action=unread');
});

// Mark all unread items as read for a specific feed
Router\get_action('mark-feed-as-read', function() {

    Model\Item\mark_feed_as_read(Request\int_param('feed_id'));
    Response\redirect('?action=unread');
});

// Mark sent items id as read (Ajax request)
Router\post_action('mark-items-as-read', function(){

    Model\Item\mark_items_as_read(Request\values());
    Response\json(array('OK'));
});

// Mark item as read and redirect to the listing page
Router\get_action('mark-item-read', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'unread');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\Item\set_read($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'&feed_id='.$feed_id.'#item-'.$id);
});

// Mark item as unread and redirect to the listing page
Router\get_action('mark-item-unread', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'history');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\Item\set_unread($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'&feed_id='.$feed_id.'#item-'.$id);
});

// Mark item as removed and redirect to the listing page
Router\get_action('mark-item-removed', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'history');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\Item\set_removed($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'&feed_id='.$feed_id);
});
