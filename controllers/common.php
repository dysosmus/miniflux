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
    $language = Model\Config\get('language') ?: 'en_US';
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

// Show help
Router\get_action('show-help', function() {

    Response\html(Template\load('show_help'));
});

// Show menu "more" with the mobile view
Router\get_action('more', function() {

    Response\html(Template\layout('show_more', array('menu' => 'more')));
});
