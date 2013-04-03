<div class="page-header">
    <h2>Username and password</h2>
</div>
<section>
<form method="post" action="?action=config" autocomplete="off">

    <?= Helper\form_label('Username', 'username') ?>
    <?= Helper\form_text('username', $values, $errors, array('required')) ?><br/>

    <?= Helper\form_label('Password', 'password') ?>
    <?= Helper\form_password('password', $values, $errors) ?><br/>

    <?= Helper\form_label('Confirmation', 'confirmation') ?>
    <?= Helper\form_password('confirmation', $values, $errors) ?><br/>
    <span class="form-help">Don't use the same password everywhere!</span><br/>

    <div class="form-actions">
        <input type="submit" value="Update" class="btn btn-blue"/>
    </div>
</form>
</section>

<div class="page-section">
    <h2>My data</h2>
</div>
<section>
<div class="alert alert-normal">
    <h3>Database</h3>
    <ul>
        <li>Database size: <?= Helper\format_bytes($db_size) ?></li>
        <li><a href="?action=optimize-db">Optimize the database</a> (VACUUM command)</li>
        <li><a href="?action=download-db">Download the entire database</a> (Gzip compressed Sqlite file).</li>
    </ul>
</div>
</section>

<div class="page-section">
    <h2>Help</h2>
</div>
<section>
<div class="alert">
    <h3>Keyboard shortcuts</h3>
    <ul>
        <li>Previous item = <strong>p</strong></li>
        <li>Next item = <strong>n</strong></li>
        <li>Mark as read or unread = <strong>m</strong></li>
        <li>Open original link = <strong>v</strong></li>
        <li>Open item = <strong>o</strong></li>
    </ul>
</div>
<div class="alert alert-info">
    <h3>About</h3>
    <ul>
        <li>Miniflux version: <strong><?= APP_VERSION ?></strong></li>
    </ul>
</div>
</section>
