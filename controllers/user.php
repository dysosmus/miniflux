<?php

require_once __DIR__.'/../lib/AuthProvider.php';

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoFarad\Template;

// Logout and destroy session
Router\get_action('logout', function() {

    Session\close();
    Response\redirect('?action=login');
});

// Display form login
Router\get_action('login', function() {

    if (isset($_SESSION['user'])) Response\redirect('?action=unread');

    Response\html(Template\load('login', array(
        'google_auth_enable' => Model\Config\get('auth_google_token') !== '',
        'mozilla_auth_enable' => Model\Config\get('auth_mozilla_token') !== '',
        'errors' => array(),
        'values' => array()
    )));
});

// Check credentials and redirect to unread items
Router\post_action('login', function() {

    $values = Request\values();
    list($valid, $errors) = Model\User\validate_login($values);

    if ($valid) Response\redirect('?action=unread');

    Response\html(Template\load('login', array(
        'google_auth_enable' => Model\Config\get('auth_google_token') !== '',
        'mozilla_auth_enable' => Model\Config\get('auth_mozilla_token') !== '',
        'errors' => $errors,
        'values' => $values
    )));
});

// Link to a Google Account (redirect)
Router\get_action('google-redirect-link', function() {

    Response\Redirect(AuthProvider\google_get_url(Helper\get_current_base_url(), '?action=google-link'));
});

// Link to a Google Account (association)
Router\get_action('google-link', function() {

    list($valid, $token) = AuthProvider\google_validate();

    if ($valid) {
        Model\Config\save_auth_token('google', $token);
        Session\flash(t('Your Google Account is linked to Miniflux.'));
    }
    else {
        Session\flash_error(t('Unable to link Miniflux to your Google Account.'));
    }

    Response\redirect('?action=config');
});

// Authenticate with a Google Account (redirect)
Router\get_action('google-redirect-auth', function() {

    Response\Redirect(AuthProvider\google_get_url(Helper\get_current_base_url(), '?action=google-auth'));
});

// Authenticate with a Google Account (callback url)
Router\get_action('google-auth', function() {

    list($valid, $token) = AuthProvider\google_validate();

    if ($valid && $token === Model\Config\get('auth_google_token')) {

        $_SESSION['user'] = array(
            'username' => Model\Config\get('username'),
            'language' => Model\Config\get('language'),
        );

        Response\redirect('?action=unread');
    }
    else {

        Response\html(Template\load('login', array(
            'google_auth_enable' => Model\Config\get('auth_google_token') !== '',
            'mozilla_auth_enable' => Model\Config\get('auth_mozilla_token') !== '',
            'errors' => array('login' => t('Unable to authenticate with Google')),
            'values' => array()
        )));
    }
});

// Authenticate with a Mozilla Persona (ajax check)
Router\post_action('mozilla-auth', function() {

    list($valid, $token) = AuthProvider\mozilla_validate(Request\param('token'));

    if ($valid && $token === Model\Config\get('auth_mozilla_token')) {

        $_SESSION['user'] = array(
            'username' => Model\Config\get('username'),
            'language' => Model\Config\get('language'),
        );

        Response\text('?action=unread');
    }
    else {
        Response\text("?action=login");
    }
});

// Link Miniflux to a Mozilla Account (ajax check)
Router\post_action('mozilla-link', function() {

    list($valid, $token) = AuthProvider\mozilla_validate(Request\param('token'));

    if ($valid) {
        Model\Config\save_auth_token('mozilla', $token);
        Session\flash(t('Your Mozilla Persona Account is linked to Miniflux.'));
    }
    else {
        Session\flash_error(t('Unable to link Miniflux to your Mozilla Persona Account.'));
    }

    Response\text("?action=config");
});

// Remove account link
Router\get_action('unlink-account-provider', function() {
    Model\Config\remove_auth_token(Request\param('type'));
    Response\redirect('?action=config');
});
