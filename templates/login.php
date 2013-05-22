<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" href="./assets/img/favicon.png">
        <link rel="shortcut icon" href="favicon.ico">
        <link rel="apple-touch-icon" href="./assets/img/touch-icon-iphone.png">
        <link rel="apple-touch-icon" sizes="72x72" href="./assets/img/touch-icon-ipad.png">
        <link rel="apple-touch-icon" sizes="114x114" href="./assets/img/touch-icon-iphone-retina.png">
        <link rel="apple-touch-icon" sizes="144x144" href="./assets/img/touch-icon-ipad-retina.png">
        <title>miniflux</title>
        <link href="./assets/css/app.css?v<?= filemtime('assets/css/app.css') ?>" rel="stylesheet" media="screen">
    </head>
    <body>

        <div class="page-header">
            <h1><?= t('Sign in') ?></h1>
        </div>

        <?php if (isset($errors['login'])): ?>

            <p class="alert alert-error"><?= Helper\escape($errors['login']) ?></p>

        <?php endif ?>

        <form method="post" action="?action=login">

            <?= Helper\form_label(t('Username'), 'username') ?>
            <?= Helper\form_text('username', $values, $errors, array('autofocus', 'required')) ?><br/>

            <?= Helper\form_label(t('Password'), 'password') ?>
            <?= Helper\form_password('password', $values, $errors, array('required')) ?>

            <div class="form-actions">
                <input type="submit" value="<?= t('Sign in') ?>" class="btn btn-blue"/>
            </div>
        </form>
    </body>
</html>