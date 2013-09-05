<div class="page-header">
    <h2><?= t('New subscription') ?></h2>
    <ul>
        <li><a href="?action=feeds"><?= t('feeds') ?></a></li>
        <li><a href="?action=import"><?= t('import') ?></a></li>
        <li><a href="?action=export"><?= t('export') ?></a></li>
    </ul>
</div>

<form method="post" action="?action=subscribe" autocomplete="off">
    <?= Helper\form_label(t('Website or Feed URL'), 'url') ?>
    <?= Helper\form_text('url', $values, array(), array('required', 'autofocus', 'placeholder="'.t('http://website/').'"')) ?>
    <?= Helper\form_checkbox('download_content', t('Download full content'), 1, isset($values['download_content']) ? $values['download_content'] : false) ?><br/>
    <p class="form-help"><?= t('Downloading full content is slower because Miniflux grab the content from the original website. You should use that for subscriptions that display only a summary. This feature doesn\'t work with all websites.') ?></p>
    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= t('Add') ?></button>
    </div>
</form>
