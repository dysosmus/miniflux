<?php

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoFarad\Template;

// Display history page
Router\get_action('history', function() {

    $offset = Request\int_param('offset', 0);
    $nb_items = Model\Item\count_by_status('read');

    Response\html(Template\layout('history', array(
        'items' => Model\Item\get_all(
            'read',
            $offset,
            Model\Config\get('items_per_page'),
            'updated',
            Model\Config\get('items_sorting_direction')
        ),
        'order' => '',
        'direction' => '',
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => Model\Config\get('items_per_page'),
        'nothing_to_read' => Request\int_param('nothing_to_read'),
        'menu' => 'history',
        'title' => t('History').' ('.$nb_items.')'
    )));
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

    Model\Item\mark_all_as_removed();
    Response\redirect('?action=history');
});
