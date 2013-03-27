<?php if (empty($items)): ?>

    <p class="alert alert-info">Nothing to read.</p>

<?php else: ?>

    <div class="page-header">
        <h2>Unread items</h2>
        <ul>
            <li><a href="?action=flush-unread">flush</a></li>
        </ul>
    </div>

    <section class="items">
    <?php foreach ($items as $item): ?>
        <article id="item-<?= urlencode($item['id']) ?>">
            <h2><a href="?action=read&amp;id=<?= urlencode($item['id']) ?>"><?= Helper\escape($item['title']) ?></a></h2>
            <a href="?action" class="mark-read">mark read</a>
            <p class="preview">
                <?= Helper\escape(Helper\summary(strip_tags($item['content']), 50, 300)) ?>
            </p>
            <p>
                <?= Helper\get_host_from_url($item['url']) ?> |
                <a href="<?= $item['url'] ?>" rel="noreferrer" target="_blank" data-item-id="<?= urlencode($item['id']) ?>" data-action="mark-read">direct link</a> |
                <a href="#read" rel="noreferrer" data-item-id="<?= urlencode($item['id']) ?>" data-action="mark-read">mark read</a>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>