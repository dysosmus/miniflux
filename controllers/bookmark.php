<?php

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoTools\Template;

// Ajax call to add or remove a bookmark
Router\post_action('bookmark', function() {

    $id = Request\param('id');
    $value = Request\int_param('value');

    Model\Item\set_bookmark_value($id, $value);

    Response\json(array('id' => $id, 'value' => $value));
});

// Add new bookmark
Router\get_action('bookmark', function() {

    $id = Request\param('id');
    $menu = Request\param('menu', 'unread');
    $source = Request\param('source', 'unread');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\Item\set_bookmark_value($id, Request\int_param('value'));

    if ($source === 'show') {
        Response\Redirect('?action=show&menu='.$menu.'&id='.$id);
    }

    Response\Redirect('?action='.$menu.'&offset='.$offset.'&feed_id='.$feed_id.'#item-'.$id);
});

// Display bookmarks page
Router\get_action('bookmarks', function() {

    $offset = Request\int_param('offset', 0);
    $nb_items = Model\Item\count_bookmarks();

    Response\html(Template\layout('bookmarks', array(
        'order' => '',
        'direction' => '',
        'items' => Model\Item\get_bookmarks($offset, Model\Config\get('items_per_page')),
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\Config\get('items_per_page'),
        'nothing_to_read' => Request\int_param('nothing_to_read'),
        'menu' => 'bookmarks',
        'title' => t('Bookmarks').' ('.$nb_items.')'
    )));
});
