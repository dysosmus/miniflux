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
            <h2><a href="?action=show&amp;id=<?= urlencode($item['id']) ?>"><?= Helper\escape($item['title']) ?></a></h2>
            <p>
                <?= Helper\get_host_from_url($item['url']) ?> |
                <?= date('l, j F Y H:i', $item['updated']) ?> |
                <a href="<?= $item['url'] ?>" rel="noreferrer" target="_blank">direct link</a>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>