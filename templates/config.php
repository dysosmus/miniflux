<div class="page-header">
    <h2><?= t('Preferences') ?></h2>
</div>
<section>
<form method="post" action="?action=config" autocomplete="off">

    <?= Helper\form_label(t('Username'), 'username') ?>
    <?= Helper\form_text('username', $values, $errors, array('required')) ?><br/>

    <?= Helper\form_label(t('Password'), 'password') ?>
    <?= Helper\form_password('password', $values, $errors) ?><br/>

    <?= Helper\form_label(t('Confirmation'), 'confirmation') ?>
    <?= Helper\form_password('confirmation', $values, $errors) ?><br/>

    <?= Helper\form_label(t('Language'), 'language') ?>
    <?= Helper\form_select('language', $languages, $values, $errors) ?><br/>

    <?= Helper\form_label(t('Remove automatically read items'), 'autoflush') ?>
    <?= Helper\form_select('autoflush', $autoflush_options, $values, $errors) ?><br/>

    <?= Helper\form_label(t('Items per page'), 'items_per_page') ?>
    <?= Helper\form_select('items_per_page', $paging_options, $values, $errors) ?><br/>

    <?= Helper\form_label(t('Theme'), 'theme') ?>
    <?= Helper\form_select('theme', $theme_options, $values, $errors) ?><br/>

    <?= Helper\form_checkbox('nocontent', t('Do not fetch the content of articles'), 1, isset($values['nocontent']) ? $values['nocontent'] : false) ?><br />

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</form>
</section>

<div class="page-section">
    <h2><?= t('More informations') ?></h2>
</div>
<section>
    <div class="alert alert-normal">
        <h3><?= t('Database') ?></h3>
        <ul>
            <li><?= t('Database size:') ?> <?= Helper\format_bytes($db_size) ?></li>
            <li><a href="?action=optimize-db"><?= t('Optimize the database') ?></a> <?= t('(VACUUM command)') ?></li>
            <li><a href="?action=download-db"><?= t('Download the entire database') ?></a> <?= t('(Gzip compressed Sqlite file)') ?></li>
        </ul>
    </div>
    <div class="alert alert-normal" id="shortcuts">
        <h3><?= t('Keyboard shortcuts') ?></h3>
        <ul>
            <li><?= t('Previous item') ?> = <strong>p</strong> <?= t('or') ?> <strong>j</strong></li>
            <li><?= t('Next item') ?> = <strong>n</strong> <?= t('or') ?> <strong>k</strong></li>
            <li><?= t('Mark as read or unread') ?> = <strong>m</strong></li>
            <li><?= t('Open original link') ?> = <strong>v</strong></li>
            <li><?= t('Open item') ?> = <strong>o</strong></li>
            <li><?= t('Bookmark item') ?> = <strong>f</strong></li>
            <li><?= t('Previous page') ?> = <strong>h</strong></li>
            <li><?= t('Next page') ?> = <strong>l</strong></li><br />
            <li><?= t('Show shortcuts') ?> = <strong>?</strong></li>
            <li><?= t('Close shortcut list') ?> = <strong>q</strong></li>
        </ul>
    </div>
    <div class="alert alert-normal">
        <h3><?= t('About') ?></h3>
        <ul>
            <li><?= t('Miniflux version:') ?> <strong><?= APP_VERSION ?></strong></li>
            <li><?= t('Official website:') ?> <a href="http://miniflux.net" target="_blank">http://miniflux.net</a></li>
        </ul>
    </div>
</section>