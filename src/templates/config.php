<div class="page-header">
    <h2>Preferences</h2>
</div>

<form method="post" action="?action=config">

    <?= Helper\form_label('Username', 'username', 'control-label') ?>
    <?= Helper\form_text('username', $values, $errors, array('required')) ?><br/>

    <?= Helper\form_label('Password', 'password', 'control-label') ?>
    <?= Helper\form_password('password', $values, $errors) ?><br/>

    <?= Helper\form_label('Confirmation', 'confirmation', 'control-label') ?>
    <?= Helper\form_password('confirmation', $values, $errors) ?><br/>

    <div class="form-actions">
        <input type="submit" value="Update" class="btn btn-blue"/>
    </div>
</form>