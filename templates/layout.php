<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <meta name="mobile-web-app-capable" content="yes">
        <link rel="icon" type="image/png" href="assets/img/favicon.png">
        <link rel="shortcut icon" href="favicon.ico">
        <link rel="apple-touch-icon" href="assets/img/touch-icon-iphone.png">
        <link rel="apple-touch-icon" sizes="72x72" href="assets/img/touch-icon-ipad.png">
        <link rel="apple-touch-icon" sizes="114x114" href="assets/img/touch-icon-iphone-retina.png">
        <link rel="apple-touch-icon" sizes="144x144" href="assets/img/touch-icon-ipad-retina.png">
        <title><?= isset($title) ? Helper\escape($title) : 'Miniflux' ?></title>
        <link href="<?= Helper\css() ?>" rel="stylesheet" media="screen">
        <script type="text/javascript" src="assets/js/all.min.js?<?= filemtime('assets/js/all.min.js') ?>" defer></script>
    </head>
    <body>
        <header>
            <nav>
                <a class="logo" href="?">mini<span>flux</span></a>
                <ul>
                    <li <?= isset($menu) && $menu === 'unread' ? 'class="active"' : '' ?>>
                        <a href="?action=unread"><?= t('unread') ?> <span id="nav-counter"><?= isset($nb_unread_items) ? '('.$nb_unread_items.')' : '' ?></span></a>
                    </li>
                    <li class="<?= isset($menu) && $menu === 'bookmarks' ? 'active hide-mobile' : 'hide-mobile' ?>">
                        <a href="?action=bookmarks"><?= t('bookmarks') ?></a>
                    </li>
                    <li class="<?= isset($menu) && $menu === 'history' ? 'active hide-mobile' : 'hide-mobile' ?>">
                        <a href="?action=history"><?= t('history') ?></a>
                    </li>
                    <li class="<?= isset($menu) && $menu === 'feeds' ? 'active hide-mobile' : 'hide-mobile' ?>">
                        <a href="?action=feeds"><?= t('subscriptions') ?></a>
                    </li>
                    <li class="<?= isset($menu) && $menu === 'config' ? 'active hide-mobile' : 'hide-mobile' ?>">
                        <a href="?action=config"><?= t('preferences') ?></a>
                    </li>
                    <li class="hide-mobile">
                        <a href="?action=logout"><?= t('logout') ?></a>
                    </li>
                    <li class="<?= isset($menu) && $menu === 'more' ? 'active hide-desktop' : 'hide-desktop' ?>">
                        <a href="?action=more"><?= t('∨ menu') ?></a>
                    </li>
                </ul>
            </nav>
        </header>
        <section class="page">
            <?= Helper\flash('<div class="alert alert-success">%s</div>') ?>
            <?= Helper\flash_error('<div class="alert alert-error">%s</div>') ?>
            <?= $content_for_layout ?>
         </section>
    </body>
</html>
