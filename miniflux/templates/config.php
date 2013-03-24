<div class="page-header">
    <h2>Preferences</h2>
</div>

<form method="post" action="?action=config" autocomplete="off">

    <?= Helper\form_label('Username', 'username') ?>
    <?= Helper\form_text('username', $values, $errors, array('required')) ?><br/>

    <?= Helper\form_label('Password', 'password') ?>
    <?= Helper\form_password('password', $values, $errors) ?>
    <span class="form-help">Don't use the same password everywhere!</span><br/>

    <?= Helper\form_label('Confirmation', 'confirmation') ?>
    <?= Helper\form_password('confirmation', $values, $errors) ?><br/>

    <div class="form-actions">
        <input type="submit" value="Update" class="btn btn-blue"/>
    </div>
</form>

<div class="page-section">
    <h2>My data</h2>
</div>

<p><a href="?action=download-db">Download the entire database</a> (Gzip compressed Sqlite file).</p>