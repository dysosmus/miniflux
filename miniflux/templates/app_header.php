<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>miniflux</title>
        <link href="./assets/css/app.css?v2" rel="stylesheet" media="screen">
        <script type="text/javascript" src="./assets/js/app.js?v1" defer></script>
    </head>
    <body>
        <header>
            <nav>
                <a class="logo" href="?">mini<span>flux</span></a>
                <ul>
                    <li <?= isset($menu) && $menu === 'unread' ? 'class="active"' : '' ?>><a href="?action=default">unread</a></li>
                    <li <?= isset($menu) && $menu === 'history' ? 'class="active"' : '' ?>><a href="?action=history">history</a></li>
                    <li <?= isset($menu) && $menu === 'feeds' ? 'class="active"' : '' ?>><a href="?action=feeds">subscriptions</a></li>
                    <li <?= isset($menu) && $menu === 'config' ? 'class="active"' : '' ?>><a href="?action=config">preferences</a></li>
                    <li><a href="?action=logout">logout</a></li>
                </ul>
            </nav>
        </header>
        <section class="page">
            <?= Helper\flash('<div class="alert alert-success">%s</div>') ?>
            <?= Helper\flash_error('<div class="alert alert-error">%s</div>') ?>
