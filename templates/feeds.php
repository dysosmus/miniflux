<div class="page-header">
    <h2><?= t('Subscriptions') ?></h2>
    <ul>
        <li><a href="?action=add"><?= t('add') ?></a></li>
        <li><a href="?action=import"><?= t('import') ?></a></li>
        <li><a href="?action=export"><?= t('export') ?></a></li>
        <li><a href="?action=refresh-all" data-action="refresh-all"><?= t('refresh all') ?></a></li>
    </ul>
</div>

<?php if (empty($feeds)): ?>

    <p class="alert alert-info"><?= t('No subscription') ?></p>

<?php else: ?>

    <?php if ($nothing_to_read): ?>
        <p class="alert"><?= t('Nothing to read, do you want to <a href="?action=refresh-all" data-action="refresh-all">update your subscriptions?</a>') ?></p>
    <?php endif ?>

    <section class="items">
    <?php foreach ($feeds as $feed): ?>
        <article>
            <h2>
                <span id="loading-feed-<?= $feed['id'] ?>"></span>
                <a href="<?= $feed['site_url'] ?>" rel="noreferrer" target="_blank"><?= Helper\escape($feed['title']) ?></a>
            </h2>
            <p>
                <a href="<?= $feed['site_url'] ?>" rel="noreferrer" target="_blank"><?= Helper\get_host_from_url($feed['site_url']) ?></a> |
                <a href="<?= Helper\escape($feed['feed_url']) ?>" rel="noreferrer" target="_blank"><?= t('feed link') ?></a> |
                <a href="?action=confirm-remove&amp;feed_id=<?= $feed['id'] ?>"><?= t('remove') ?></a> |
                <a href="?action=refresh-feed&amp;feed_id=<?= $feed['id'] ?>" data-feed-id="<?= $feed['id'] ?>" data-action="refresh-feed"><?= t('refresh') ?></a>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>