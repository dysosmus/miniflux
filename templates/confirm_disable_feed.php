<div class="page-header">
    <h2><?= t('Confirmation') ?></h2>
</div>

<p class="alert alert-info"><?= t('Do you really want to disable this subscription: "%s"?', Helper\escape($feed['title'])) ?></p>

<div class="form-actions">
    <a href="?action=disable-feed&amp;feed_id=<?= $feed['id'] ?>" class="btn btn-red"><?= t('Yes') ?></a>
    <?= t('or') ?> <a href="?action=feeds"><?= t('cancel') ?></a>
</div>