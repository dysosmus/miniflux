<?php

require __DIR__.'/check_setup.php';
require __DIR__.'/lib/Translator.php';
require __DIR__.'/vendor/PicoDb/Database.php';
require __DIR__.'/vendor/PicoFeed/Client.php';
require __DIR__.'/vendor/PicoFeed/Parser.php';
require __DIR__.'/models/config.php';
require __DIR__.'/models/user.php';
require __DIR__.'/models/feed.php';
require __DIR__.'/models/item.php';
require __DIR__.'/models/schema.php';

if (file_exists('config.php')) require 'config.php';

defined('APP_VERSION') or define('APP_VERSION', 'master');
defined('HTTP_TIMEOUT') or define('HTTP_TIMEOUT', 20);
defined('DB_FILENAME') or define('DB_FILENAME', 'data/db.sqlite');
defined('DEBUG') or define('DEBUG', true);
defined('DEBUG_FILENAME') or define('DEBUG_FILENAME', 'data/debug.log');
defined('THEME_DIRECTORY') or define('THEME_DIRECTORY', 'themes');
defined('SESSION_SAVE_PATH') or define('SESSION_SAVE_PATH', '');
defined('PROXY_HOSTNAME') or define('PROXY_HOSTNAME', '');
defined('PROXY_PORT') or define('PROXY_PORT', 3128);
defined('PROXY_USERNAME') or define('PROXY_USERNAME', '');
defined('PROXY_PASSWORD') or define('PROXY_PASSWORD', '');
defined('ROOT_DIRECTORY') or define('ROOT_DIRECTORY', __DIR__);
defined('UPDATE_DOWNLOAD_DIRECTORY') or define('UPDATE_DOWNLOAD_DIRECTORY', 'data/update/download');
defined('UPDATE_ROLLBACK_DIRECTORY') or define('UPDATE_ROLLBACK_DIRECTORY', 'data/update/rollback');

defined('ROOT_DIRECTORY') or define('ROOT_DIRECTORY', __DIR__);
PicoFeed\Client::proxy(PROXY_HOSTNAME, PROXY_PORT, PROXY_USERNAME, PROXY_PASSWORD);

PicoDb\Database::bootstrap('db', function() {

    $db = new PicoDb\Database(array(
        'driver' => 'sqlite',
        'filename' => DB_FILENAME
    ));

    if ($db->schema()->check(Model\Config\DB_VERSION)) {
        return $db;
    }
    else {
        die('Unable to migrate database schema.');
    }
});
