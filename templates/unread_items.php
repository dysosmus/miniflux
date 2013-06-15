<?php if (empty($items)): ?>

    <p class="alert alert-info"><?= t('Nothing to read') ?></p>

<?php else: ?>

    <div class="page-header">
        <h2> <?= t('<span id="unread-count">%d</span> unread items', $nb_items) ?></h2>
        <ul>
            <li><a href="?action=mark-as-read"><?= t('mark all as read') ?></a></li>
        </ul>
    </div>

    <section class="items" id="listing">
    <?php foreach ($items as $item): ?>
        <?php $item_id = Model\encode_item_id($item['id']) ?>
        <article id="item-<?= $item_id ?>" data-item-id="<?= $item_id ?>">
            <h2>
                <a
                    href="?action=read&amp;id=<?= $item_id ?>"
                    id="open-<?= $item_id ?>"
                >
                    <?= Helper\escape($item['title']) ?>
                </a>
            </h2>
            <p class="preview">
                <?= Helper\escape(Helper\summary(strip_tags($item['content']), 50, 300)) ?>
            </p>
            <p>
                <?= Helper\get_host_from_url($item['url']) ?> |
                <?= dt('%A %e %B %Y %k:%M', $item['updated']) ?> |
                <a href="?action=mark-item-read&amp;id=<?= $item_id ?>"><?= t('mark as read') ?></a> |
                <a
                    href="<?= $item['url'] ?>"
                    id="original-<?= $item_id ?>"
                    rel="noreferrer"
                    target="_blank"
                    data-item-id="<?= $item_id ?>"
                    data-action="mark-read"
                >
                    <?= t('original link') ?>
                </a>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>