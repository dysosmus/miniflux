<?php

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoFarad\Template;

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
