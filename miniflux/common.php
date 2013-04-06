<?php

require 'check_setup.php';
require 'vendor/password.php';
require 'vendor/PicoTools/Dependency_Injection.php';
require 'vendor/PicoDb/Database.php';
require 'vendor/PicoDb/Table.php';
require 'schema.php';
require 'model.php';


const DB_VERSION = 1;
const APP_VERSION = 'master';
const APP_USERAGENT = 'Miniflux - http://miniflux.net';
const HTTP_TIMEOUT = 5;


// For future use...
function get_db_filename()
{
    return 'data/db.sqlite';
}


PicoTools\container('db', function() {

    $db = new PicoDb\Database(array(
        'driver' => 'sqlite',
        'filename' => get_db_filename()
    ));

    if ($db->schema()->check(DB_VERSION)) {

        return $db;
    }
    else {

        die('Unable to migrate database schema.');
    }
});