<?php

require 'check_setup.php';
require 'vendor/password.php';
require 'vendor/PicoTools/Dependency_Injection.php';
require 'vendor/PicoDb/Database.php';
require 'vendor/PicoDb/Table.php';
require 'schema.php';
require 'model.php';


function get_db_filename()
{
    return 'data/db.sqlite';
}


PicoTools\container('db', function() {

    $db = new PicoDb\Database(array(
        'driver' => 'sqlite',
        'filename' => get_db_filename()
    ));

    if ($db->schema()->check(1)) {

        return $db;
    }
    else {

        die('Unable to migrate database schema.');
    }
});