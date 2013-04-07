<?php if (empty($items)): ?>

    <p class="alert alert-info">No history.</p>

<?php else: ?>

    <div class="page-header">
        <h2>History</h2>
        <ul>
            <li><a href="?action=confirm-flush-history">flush</a></li>
        </ul>
    </div>

    <section class="items" id="listing">
    <?php foreach ($items as $item): ?>
        <article id="item-<?= urlencode($item['id']) ?>" data-item-id="<?= urlencode($item['id']) ?>">
            <h2>
                <a
                    href="?action=show&amp;id=<?= urlencode($item['id']) ?>"
                    id="open-<?= urlencode($item['id']) ?>"
                >
                    <?= Helper\escape($item['title']) ?>
                </a>
            </h2>
            <p>
                <?= Helper\get_host_from_url($item['url']) ?> |
                <?= date('l, j F Y H:i', $item['updated']) ?> |
                <a href="?action=mark-item-unread&amp;id=<?= urlencode($item['id']) ?>">mark as unread</a> |
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