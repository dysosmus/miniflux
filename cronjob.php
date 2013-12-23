<?php

require 'common.php';

if (php_sapi_name() === 'cli') {

    $options = getopt('', array(
        'limit::',
        'call-interval::',
        'update-interval::'
    ));
}
else {

    $options = $_GET;
}

$limit = ! empty($options['limit']) && ctype_digit($options['limit']) ? (int) $options['limit'] : Model\Feed\LIMIT_ALL;
$update_interval = ! empty($options['update-interval']) && ctype_digit($options['update-interval']) ? (int) $options['update-interval'] : null;
$call_interval = ! empty($options['call-interval']) && ctype_digit($options['call-interval']) ? (int) $options['call-interval'] : null;

if ($update_interval !== null && $call_interval !== null && $limit === Model\Feed\LIMIT_ALL && $update_interval >= $call_interval) {

    $feeds_count = \PicoTools\singleton('db')->table('feeds')->count();
    $limit = ceil($feeds_count / ($update_interval / $call_interval));
}

Model\Feed\refresh_all($limit);
Model\Config\write_debug();
