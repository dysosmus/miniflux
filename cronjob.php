<?php

require 'common.php';

if (is_console()) {

    $options = getopt('', array(
        'limit::',
        'call-interval::',
        'update-interval::'
    ));

    $limit           = empty($options['limit'])           ? LIMIT_ALL : (int)$options['limit'];
    $update_interval = empty($options['update-interval']) ? null      : (int)$options['update-interval'];
    $call_interval   = empty($options['call-interval'])   ? null      : (int)$options['call-interval'];
} else {

    $limit           = empty($_GET['limit'])           ? LIMIT_ALL : (int)$_GET['limit'];
    $update_interval = empty($_GET['update-interval']) ? null      : (int)$_GET['update-interval'];
    $call_interval   = empty($_GET['call-interval'])   ? null      : (int)$_GET['call-interval'];
}

if ($update_interval !== null && $call_interval !== null && $limit === LIMIT_ALL && $update_interval >= $call_interval) {

    $feeds_count = \PicoTools\singleton('db')->table('feeds')->count();
    $limit       = ceil($feeds_count / ($update_interval / $call_interval)); // compute new limit
}

Model\update_feeds($limit);
