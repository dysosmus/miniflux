<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <link rel="icon" type="image/png" href="./assets/img/favicon.png">
        <link rel="shortcut icon" href="favicon.ico">
        <link rel="apple-touch-icon" href="./assets/img/touch-icon-iphone.png">
        <link rel="apple-touch-icon" sizes="72x72" href="./assets/img/touch-icon-ipad.png">
        <link rel="apple-touch-icon" sizes="114x114" href="./assets/img/touch-icon-iphone-retina.png">
        <link rel="apple-touch-icon" sizes="144x144" href="./assets/img/touch-icon-ipad-retina.png">
        <title>miniflux</title>
        <link href="./assets/css/app.css?v<?= filemtime('assets/css/app.css') ?>" rel="stylesheet" media="screen">
        <script type="text/javascript" src="./assets/js/app.js?v<?= filemtime('assets/js/app.js') ?>" defer></script>
    </head>
    <body>
        <header>
            <nav>
                <a class="logo" href="?">mini<span>flux</span></a>
                <ul>
                    <li <?= isset($menu) && $menu === 'unread' ? 'class="active"' : '' ?>><a href="?action=default"><?= t('unread') ?></a></li>
                    <li <?= isset($menu) && $menu === 'starred' ? 'class="active"' : '' ?>><a href="?action=starred"><?= t('starred') ?></a></li>
                    <li <?= isset($menu) && $menu === 'history' ? 'class="active"' : '' ?>><a href="?action=history"><?= t('history') ?></a></li>
                    <li <?= isset($menu) && $menu === 'feeds' ? 'class="active"' : '' ?>><a href="?action=feeds"><?= t('subscriptions') ?></a></li>
                    <li <?= isset($menu) && $menu === 'config' ? 'class="active"' : '' ?>><a href="?action=config"><?= t('preferences') ?></a></li>
                    <li><a href="?action=logout"><?= t('logout') ?></a></li>
                </ul>
            </nav>
        </header>
        <section class="page">
            <?= Helper\flash('<div class="alert alert-success">%s</div>') ?>
            <?= Helper\flash_error('<div class="alert alert-error">%s</div>') ?>
