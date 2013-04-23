<?php if (empty($items)): ?>

    <p class="alert alert-info"><?= t('No history') ?></p>

<?php else: ?>

    <div class="page-header">
        <h2><?= t('History') ?></h2>
        <ul>
            <li><a href="?action=confirm-flush-history"><?= t('flush these items') ?></a></li>
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
                <?= dt('%A %e %B %Y %k:%M', $item['updated']) ?> |
                <a href="?action=mark-item-unread&amp;id=<?= urlencode($item['id']) ?>"><?= t('mark as unread') ?></a> |
                <a href="?action=mark-item-removed&amp;id=<?= urlencode($item['id']) ?>"><?= t('remove') ?></a> |
                <a
                    href="<?= $item['url'] ?>"
                    id="original-<?= urlencode($item['id']) ?>"
                    rel="noreferrer"
                    target="_blank"
                    data-item-id="<?= urlencode($item['id']) ?>"
                    data-action="mark-read"
                >
                    <?= t('original link') ?>
                </a>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>