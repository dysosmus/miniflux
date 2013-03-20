<?php

require 'check_setup.php';
require 'vendor/password.php';
require 'vendor/PicoTools/Dependency_Injection.php';
require 'vendor/PicoDb/Database.php';
require 'vendor/PicoDb/Table.php';
require 'schema.php';
require 'model.php';


PicoTools\container('db', function() {

    $db = new PicoDb\Database(array(
        'driver' => 'sqlite',
        'filename' => 'data/db.sqlite'
    ));

    if ($db->schema()->check(1)) {

        return $db;
    }
    else {

        die('Unable to migrate database schema.');
    }
});