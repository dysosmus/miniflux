<div class="page-header">
    <h2>Subscriptions</h2>
    <ul>
        <li><a href="?action=add">add</a></li>
        <li><a href="?action=import">import</a></li>
        <li><a href="?action=export">export</a></li>
        <li><a href="?action=refresh-all" data-action="refresh-all">refresh all</a></li>
    </ul>
</div>

<?php if (empty($feeds)): ?>

    <p class="alert alert-info">No subscription.</p>

<?php else: ?>

    <?php if ($nothing_to_read): ?>
        <p class="alert">Nothing to read, do you want to <a href="?action=refresh-all" data-action="refresh-all">update your subscriptions?</a></p>
    <?php endif ?>

    <section class="items">
    <?php foreach ($feeds as $feed): ?>
        <article>
            <h2>
                <span id="loading-feed-<?= $feed['id'] ?>"></span>
                <a href="<?= $feed['site_url'] ?>" rel="noreferrer" target="_blank"><?= Helper\escape($feed['title']) ?></a>
            </h2>
            <p>
                <?= Helper\get_host_from_url($feed['site_url']) ?> |
                <a href="<?= Helper\escape($feed['feed_url']) ?>">feed link</a> |
                <a href="?action=confirm-remove&amp;feed_id=<?= $feed['id'] ?>">remove</a> |
                <a href="?action=refresh-feed&amp;feed_id=<?= $feed['id'] ?>" data-feed-id="<?= $feed['id'] ?>" data-action="refresh-feed">refresh</a>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>