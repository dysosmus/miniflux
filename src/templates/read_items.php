<?php if (empty($items)): ?>

    <p class="alert alert-info">No history.</p>

<?php else: ?>

    <div class="page-header">
        <h2>History</h2>
        <ul>
            <li><a href="?action=flush-history">flush</a></li>
        </ul>
    </div>

    <section class="items">
    <?php foreach ($items as $item): ?>
        <article>
            <h2><a href="?action=read&amp;id=<?= urlencode($item['id']) ?>"><?= Helper\escape($item['title']) ?></a></h2>
            <p>
                <?= Helper\escape(parse_url($item['site_url'], PHP_URL_HOST)) ?> |
                <?= date('l, j F Y H:i T', $item['updated']) ?>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>