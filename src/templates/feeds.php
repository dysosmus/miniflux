<div class="page-header">
    <h2>Subscriptions</h2>
    <?php include __DIR__.'/feed_menu.php' ?>
</div>

<?php if (empty($feeds)): ?>

    <p class="alert alert-info">No subscriptions.</p>

<?php else: ?>

    <section class="items">
    <?php foreach ($feeds as $feed): ?>
        <article>
            <h2><a href="<?= $feed['site_url'] ?>" traget="_blank"><?= Helper\escape($feed['title']) ?></a></h2>
            <p>
                <?= Helper\escape(parse_url($feed['site_url'], PHP_URL_HOST)) ?> |
                <a href="?action=remove&amp;feed_id=<?= $feed['id'] ?>">remove</a> |
                <a href="?action=refresh&amp;feed_id=<?= $feed['id'] ?>">refresh</a>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>