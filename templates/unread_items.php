<?php if (empty($items)): ?>

    <p class="alert alert-info"><?= t('Nothing to read') ?></p>

<?php else: ?>

    <div class="page-header">
        <h2><span id="page-counter"><?= isset($nb_items) ? $nb_items.' ' : '' ?></span><?= t('unread items') ?></h2>
        <ul>
            <li>
            <a href="?action=mark-as-read" data-action="mark-all-read">
                <?= t('mark all as read') ?>
            </a>
            </li>
        </ul>
    </div>

    <section class="items" id="listing">
    <?php foreach ($items as $item): ?>
        <?php $item_id = Model\encode_item_id($item['id']) ?>
        <article id="item-<?= $item_id ?>" data-item-id="<?= $item_id ?>" data-item-page="<?= $menu ?>" data-hide="true">
            <h2>
                <?= $item['bookmark'] ? '★ ' : '' ?>
                <a
                    href="?action=read&amp;id=<?= $item_id ?>"
                    data-item-id="<?= $item_id ?>"
                    id="open-<?= $item_id ?>"
                >
                    <?= Helper\escape($item['title']) ?>
                </a>
            </h2>
            <p class="preview">
                <?= Helper\escape(Helper\summary(strip_tags($item['content']), 50, 300)) ?>
            </p>
            <p>
                <?= Helper\escape($item['feed_title']) ?> |
                <span class="hide-mobile"><?= dt('%e %B %Y %k:%M', $item['updated']) ?> |</span>

                <span class="hide-mobile">
                <?php if ($item['bookmark']): ?>
                    <a id="bookmark-<?= $item_id ?>" href="?action=bookmark&amp;value=0&amp;id=<?= $item_id ?>&amp;redirect=unread&amp;offset=<?= $offset ?>"><?= t('remove bookmark') ?></a> |
                <?php else: ?>
                    <a id="bookmark-<?= $item_id ?>" href="?action=bookmark&amp;value=1&amp;id=<?= $item_id ?>&amp;redirect=unread&amp;offset=<?= $offset ?>"><?= t('bookmark') ?></a> |
                <?php endif ?>
                </span>

                <a
                    href="?action=mark-item-read&amp;id=<?= $item_id ?>&amp;offset=<?= $offset ?>"
                    data-action="mark-read"
                    data-item-id="<?= $item_id ?>"
                >
                    <?= t('mark as read') ?>
                </a> |
                <a
                    href="<?= $item['url'] ?>"
                    id="original-<?= $item_id ?>"
                    rel="noreferrer"
                    target="_blank"
                    data-item-id="<?= $item_id ?>"
                    data-action="original-link"
                    data-hide="true"
                >
                    <?= t('original link') ?>
                </a>
            </p>
        </article>
    <?php endforeach ?>

    <nav id="items-paging">
    <?php if ($offset > 0): ?>
        <a id="previous-page" href="?action=unread&amp;offset=<?= ($offset - $items_per_page) ?>">⇽ <?= t('Previous page') ?></a>
    <?php endif ?>
    &nbsp;
    <?php if (($nb_items - $offset) > $items_per_page): ?>
        <a id="next-page" href="?action=unread&amp;offset=<?= ($offset + $items_per_page) ?>"><?= t('Next page') ?> ⇾</a>
    <?php endif ?>
    </nav>

    </section>

<?php endif ?>
