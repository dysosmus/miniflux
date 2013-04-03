<?php if (empty($items)): ?>

    <p class="alert alert-info">Nothing to read.</p>

<?php else: ?>

    <div class="page-header">
        <h2>Unread items</h2>
        <ul>
            <li><a href="?action=mark-as-read">mark all as read</a></li>
        </ul>
    </div>

    <section class="items" id="listing">
    <?php foreach ($items as $item): ?>
        <article id="item-<?= urlencode($item['id']) ?>" data-item-id="<?= urlencode($item['id']) ?>">
            <h2>
                <a
                    href="?action=read&amp;id=<?= urlencode($item['id']) ?>"
                    id="open-<?= urlencode($item['id']) ?>"
                >
                    <?= Helper\escape($item['title']) ?>
                </a>
            </h2>
            <p class="preview">
                <?= Helper\escape(Helper\summary(strip_tags($item['content']), 50, 300)) ?>
            </p>
            <p>
                <?= Helper\get_host_from_url($item['url']) ?> |
                <a
                    href="<?= $item['url'] ?>"
                    id="original-<?= urlencode($item['id']) ?>"
                    rel="noreferrer"
                    target="_blank"
                    data-item-id="<?= urlencode($item['id']) ?>"
                    data-action="mark-read"
                >
                    direct link
                </a>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>