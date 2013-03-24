<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>miniflux</title>
        <link href="./assets/css/app.css?v1" rel="stylesheet" media="screen">
    </head>
    <body>

        <div class="page-header">
            <h1>Login</h1>
        </div>

        <?php if (isset($errors['login'])): ?>

            <p class="alert alert-error"><?= Helper\escape($errors['login']) ?></p>

        <?php endif ?>

        <form method="post" action="?action=login">

            <?= Helper\form_label('Username', 'username') ?>
            <?= Helper\form_text('username', $values, $errors, array('autofocus', 'required')) ?><br/>

            <?= Helper\form_label('Password', 'password') ?>
            <?= Helper\form_password('password', $values, $errors, array('required')) ?>

            <div class="form-actions">
                <input type="submit" value="Login" class="btn btn-blue"/>
            </div>
        </form>
    </body>
</html>