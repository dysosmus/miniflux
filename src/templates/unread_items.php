<?php if (empty($items)): ?>

    <p class="alert alert-info">No unread items.</p>

<?php else: ?>

    <div class="page-header">
        <h2>Unread items</h2>
        <ul>
            <li><a href="?action=flush-unread">flush</a></li>
        </ul>
    </div>

    <section class="items">
    <?php foreach ($items as $item): ?>
        <article>
            <h2><a href="?action=read&amp;id=<?= urlencode($item['id']) ?>"><?= Helper\escape($item['title']) ?></a></h2>
            <p>
                <?= Helper\escape(parse_url($item['site_url'], PHP_URL_HOST)) ?>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>