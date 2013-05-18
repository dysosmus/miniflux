<div class="page-header">
    <h2><?= t('OPML Import') ?></h2>
    <ul>
        <li><a href="?action=feeds"><?= t('feeds') ?></a></li>
        <li><a href="?action=add"><?= t('add') ?></a></li>
        <li><a href="?action=export"><?= t('export') ?></a></li>
    </ul>
</div>

<form method="post" action="?action=import" enctype="multipart/form-data">
    <label for="file"><?= t('OPML file') ?></label>
    <input type="file" name="file" required/>
    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= t('Import') ?></button>
    </div>
</form>