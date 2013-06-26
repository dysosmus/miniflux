<?php if (empty($items)): ?>

    <p class="alert alert-info"><?= t('No bookmark') ?></p>

<?php else: ?>

    <div class="page-header">
        <h2><?= t('Bookmarks') ?></h2>
    </div>

    <section class="items">
    <?php foreach ($items as $item): ?>
        <?php $item_id = Model\encode_item_id($item['id']) ?>
        <article id="item-<?= $item_id ?>" data-item-id="<?= $item_id ?>" class="<?= $item['status'] == 'read' ? 'item-status-read' : '' ?>">
            <h2>
                <a
                    href="?action=show&amp;id=<?= $item_id ?>"
                    id="open-<?= $item_id ?>"
                >
                    <?= Helper\escape($item['title']) ?>
                </a>
            </h2>
            <p>
                <?= Helper\get_host_from_url($item['url']) ?> |
                <?= dt('%e %B %Y %k:%M', $item['updated']) ?> |
                <a href="?action=bookmark&amp;value=0&amp;id=<?= $item_id ?>&amp;redirect=bookmarks"><?= t('remove bookmark') ?></a> |
                <a
                    href="<?= $item['url'] ?>"
                    id="original-<?= $item_id ?>"
                    rel="noreferrer"
                    target="_blank"
                    data-item-id="<?= $item_id ?>"
                >
                    <?= t('original link') ?>
                </a>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>