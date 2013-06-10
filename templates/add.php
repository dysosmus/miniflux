<div class="page-header">
    <h2><?= t('New subscription') ?></h2>
    <ul>
        <li><a href="?action=feeds"><?= t('feeds') ?></a></li>
        <li><a href="?action=import"><?= t('import') ?></a></li>
        <li><a href="?action=export"><?= t('export') ?></a></li>
    </ul>
</div>

<form method="post" action="?action=add" autocomplete="off">
    <label for="url"><?= t('Website or Feed URL') ?></label>
    <input type="text" name="url" id="url" placeholder="<?= t('http://website/') ?>" autofocus required/>
    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= t('Add') ?></button>
    </div>
</form>
