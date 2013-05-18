<div class="page-header">
    <h2><?= t('Confirmation') ?></h2>
</div>

<p class="alert alert-info"><?= t('Do you really want to remove these items from your history?') ?></p>

<div class="form-actions">
    <a href="?action=flush-history" class="btn btn-red"><?= t('Yes') ?></a>
    <?= t('or') ?> <a href="?action=history"><?= t('cancel') ?></a>
</div>