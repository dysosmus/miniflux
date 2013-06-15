<?php

require 'check_setup.php';
require 'vendor/password.php';
require 'vendor/PicoTools/Dependency_Injection.php';
require 'vendor/PicoTools/Translator.php';
require 'vendor/PicoDb/Database.php';
require 'vendor/PicoDb/Table.php';
require 'schema.php';
require 'model.php';


const DB_VERSION    = 8;
const APP_VERSION   = 'master';
const APP_USERAGENT = 'Miniflux - http://miniflux.net';
const HTTP_TIMEOUT  = 5;
const LIMIT_ALL     = -1;


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
