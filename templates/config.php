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
        <h3 id="api"><?= t('API') ?></h3>
        <ul>
            <li>
                <?= t('Bookmarklet:') ?>
                <a href="javascript:location.href='<?= Helper\get_current_base_url() ?>?action=subscribe&amp;url='+encodeURIComponent(location.href)"><?= t('Subscribe with Miniflux') ?></a> (<?= t('Drag and drop this link to your bookmarks') ?>)
            <li>
                <?= t('Bookmarks RSS Feed:') ?>
                <a href="<?= Helper\get_current_base_url().'feed.php?token='.urlencode($values['feed_token']) ?>" target="_blank"><?= Helper\get_current_base_url().'feed.php?token='.urlencode($values['feed_token']) ?></a>
            </li>
            <li><?= t('API endpoint:') ?> <strong><?= Helper\get_current_base_url().'jsonrpc.php' ?></strong></li>
            <li><?= t('API username:') ?> <strong><?= Helper\escape($values['username']) ?></strong></li>
            <li><?= t('API token:') ?> <strong><?= Helper\escape($values['api_token']) ?></strong></li>
            <li><a href="?action=generate-tokens"><?= t('Generate new tokens') ?></a></li>
        </ul>
    </div>
    <div class="alert alert-normal">
        <h3><?= t('Database') ?></h3>
        <ul>
            <li><?= t('Database size:') ?> <strong><?= Helper\format_bytes($db_size) ?></strong></li>
            <li><a href="?action=optimize-db"><?= t('Optimize the database') ?></a> <?= t('(VACUUM command)') ?></li>
            <li><a href="?action=download-db"><?= t('Download the entire database') ?></a> <?= t('(Gzip compressed Sqlite file)') ?></li>
        </ul>
    </div>
    <?= \PicoTools\Template\load('keyboard_shortcuts') ?>
    <div class="alert alert-normal">
        <h3><?= t('About') ?></h3>
        <ul>
            <li><?= t('Miniflux version:') ?> <strong><?= APP_VERSION ?></strong></li>
            <li><?= t('Official website:') ?> <a href="http://miniflux.net" target="_blank">http://miniflux.net</a></li>
            <li><a href="?action=console"><?= t('Console') ?></a></li>
        </ul>
    </div>
</section>