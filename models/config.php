<?php

namespace Model\Config;

require_once __DIR__.'/../vendor/SimpleValidator/Validator.php';
require_once __DIR__.'/../vendor/SimpleValidator/Base.php';
require_once __DIR__.'/../vendor/SimpleValidator/Validators/Required.php';
require_once __DIR__.'/../vendor/SimpleValidator/Validators/Unique.php';
require_once __DIR__.'/../vendor/SimpleValidator/Validators/MaxLength.php';
require_once __DIR__.'/../vendor/SimpleValidator/Validators/MinLength.php';
require_once __DIR__.'/../vendor/SimpleValidator/Validators/Integer.php';
require_once __DIR__.'/../vendor/SimpleValidator/Validators/Equals.php';
require_once __DIR__.'/../vendor/SimpleValidator/Validators/Integer.php';

use SimpleValidator\Validator;
use SimpleValidator\Validators;
use PicoDb\Database;

const DB_VERSION          = 22;
const HTTP_USERAGENT      = 'Miniflux - http://miniflux.net';
const HTTP_FAKE_USERAGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36';

// Write PicoFeed debug output to a file
function write_debug()
{
    if (DEBUG) {

        $data = '';

        foreach (\PicoFeed\Logging::$messages as $line) {
            $data .= $line.PHP_EOL;
        }

        file_put_contents(DEBUG_FILENAME, $data);
    }
}

// Get available timezone
function get_timezones()
{
    $timezones = \timezone_identifiers_list();
    return array_combine(array_values($timezones), $timezones);
}

// Get all supported languages
function get_languages()
{
    $languages = array(
        'cs_CZ' => t('Czech'),
        'de_DE' => t('German'),
        'en_US' => t('English'),
        'es_ES' => t('Spanish'),
        'fr_FR' => t('French'),
        'it_IT' => t('Italian'),
        'pt_BR' => t('Portuguese'),
        'zh_CN' => t('Simplified Chinese'),
    );

    asort($languages);

    return $languages;
}

// Get all skins
function get_themes()
{
    $themes = array(
        'original' => t('Original')
    );

    if (file_exists(THEME_DIRECTORY)) {

        $dir = new \DirectoryIterator(THEME_DIRECTORY);

        foreach ($dir as $fileinfo) {

            if (! $fileinfo->isDot() && $fileinfo->isDir()) {
                $themes[$dir->getFilename()] = ucfirst($dir->getFilename());
            }
        }
    }

    return $themes;
}

// Sorting direction choices for items
function get_sorting_directions()
{
    return array(
        'asc' => t('Older items first'),
        'desc' => t('Most recent first'),
    );
}

// Autoflush choices for items
function get_autoflush_options()
{
    return array(
        '0' => t('Never'),
        '-1' => t('Immediately'),
        '1' => t('After %d day', 1),
        '5' => t('After %d days', 5),
        '15' => t('After %d days', 15),
        '30' => t('After %d days', 30)
    );
}

// Number of items per pages
function get_paging_options()
{
    return array(
        50 => 50,
        100 => 100,
        150 => 150,
        200 => 200,
        250 => 250,
    );
}

// Get redirect options when there is nothing to read
function get_nothing_to_read_redirections()
{
    return array(
        'feeds' => t('Subscription page'),
        'history' => t('History page'),
        'bookmarks' => t('Bookmark page'),
    );
}

// Generate a token from /dev/urandom or with uniqid() if open_basedir is enabled
function generate_token()
{
    if (ini_get('open_basedir') === '') {
        return substr(base64_encode(file_get_contents('/dev/urandom', false, null, 0, 20)), 0, 15);
    }
    else {
        return substr(base64_encode(uniqid(mt_rand(), true)), 0, 20);
    }
}

// Regenerate tokens for the API and bookmark feed
function new_tokens()
{
    $values = array(
        'api_token' => generate_token(),
        'feed_token' => generate_token(),
    );

    return Database::get('db')->table('config')->update($values);
}

// Save tokens for external authentication
function save_auth_token($type, $value)
{
    return Database::get('db')
        ->table('config')
        ->update(array(
            'auth_'.$type.'_token' => $value
        ));
}

// Clear authentication tokens
function remove_auth_token($type)
{
    Database::get('db')
        ->table('config')
        ->update(array(
            'auth_'.$type.'_token' => ''
        ));

    $_SESSION['config'] = get_all();
}

// Get a config value from the DB or from the session
function get($name)
{
    if (! isset($_SESSION)) {
        return Database::get('db')->table('config')->findOneColumn($name);
    }
    else {

        if (! isset($_SESSION['config'][$name])) {
            $_SESSION['config'] = get_all();
        }

        if (isset($_SESSION['config'][$name])) {
            return $_SESSION['config'][$name];
        }
    }

    return null;
}

// Get all config parameters
function get_all()
{
    return Database::get('db')
        ->table('config')
        ->columns(
            'username',
            'language',
            'timezone',
            'autoflush',
            'nocontent',
            'items_per_page',
            'theme',
            'api_token',
            'feed_token',
            'auth_google_token',
            'auth_mozilla_token',
            'items_sorting_direction',
            'redirect_nothing_to_read'
        )
        ->findOne();
}

// Validation for edit action
function validate_modification(array $values)
{
    if (! empty($values['password'])) {

        $v = new Validator($values, array(
            new Validators\Required('username', t('The user name is required')),
            new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
            new Validators\Required('password', t('The password is required')),
            new Validators\MinLength('password', t('The minimum length is 6 characters'), 6),
            new Validators\Required('confirmation', t('The confirmation is required')),
            new Validators\Equals('password', 'confirmation', t('Passwords doesn\'t match')),
            new Validators\Required('autoflush', t('Value required')),
            new Validators\Required('items_per_page', t('Value required')),
            new Validators\Integer('items_per_page', t('Must be an integer')),
            new Validators\Required('theme', t('Value required')),
        ));
    }
    else {

        $v = new Validator($values, array(
            new Validators\Required('username', t('The user name is required')),
            new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
            new Validators\Required('autoflush', t('Value required')),
            new Validators\Required('items_per_page', t('Value required')),
            new Validators\Integer('items_per_page', t('Must be an integer')),
            new Validators\Required('theme', t('Value required')),
        ));
    }

    return array(
        $v->execute(),
        $v->getErrors()
    );
}

// Save config into the database and update the session
function save(array $values)
{
    // Update the password if needed
    if (! empty($values['password'])) {
        $values['password'] = \password_hash($values['password'], PASSWORD_BCRYPT);
    } else {
        unset($values['password']);
    }

    unset($values['confirmation']);

    // Reload configuration in session
    $_SESSION['config'] = $values;

    // Reload translations for flash session message
    \PicoTools\Translator\load($values['language']);

    // If the user does not want content of feeds, remove it in previous ones
    if (isset($values['nocontent']) && (bool) $values['nocontent']) {
        Database::get('db')->table('items')->update(array('content' => ''));
    }

    return Database::get('db')->table('config')->update($values);
}
