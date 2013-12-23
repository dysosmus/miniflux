<?php

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoTools\Template;

// Display unread items
Router\notfound(function() {

    Model\Item\autoflush();

    $order = Request\param('order', 'updated');
    $direction = Request\param('direction', Model\Config\get('items_sorting_direction'));
    $offset = Request\int_param('offset', 0);
    $items = Model\Item\get_all('unread', $offset, Model\Config\get('items_per_page'), $order, $direction);
    $nb_items = Model\Item\count_by_status('unread');

    if ($nb_items === 0) Response\redirect('?action=feeds&nothing_to_read=1');

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
