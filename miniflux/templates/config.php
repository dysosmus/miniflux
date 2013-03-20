<div class="page-header">
    <h2>Preferences</h2>
</div>

<form method="post" action="?action=config">

    <?= Helper\form_label('Username', 'username') ?>
    <?= Helper\form_text('username', $values, $errors, array('required')) ?><br/>

    <?= Helper\form_label('Password', 'password') ?>
    <?= Helper\form_password('password', $values, $errors) ?><br/>

    <?= Helper\form_label('Confirmation', 'confirmation') ?>
    <?= Helper\form_password('confirmation', $values, $errors) ?><br/>

    <div class="form-actions">
        <input type="submit" value="Update" class="btn btn-blue"/>
    </div>
</form>