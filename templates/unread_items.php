<?php if (empty($items)): ?>

    <p class="alert alert-info"><?= t('Nothing to read') ?></p>

<?php else: ?>

    <div class="page-header">
        <h2><?= t('<span id="page-counter">%s</span>unread items', isset($nb_items) ? $nb_items.' ' : '') ?></h2>
        <ul>
            <li>
                <a href="?action=unread&amp;order=updated&amp;direction=<?= $direction == 'asc' ? 'desc' : 'asc' ?>"><?= t('sort by date<span class="hide-mobile"> (%s)</span>', $direction == 'desc' ? t('older first') : t('most recent first')) ?></a>
            </li>
            <li>
                <a href="?action=mark-as-read" data-action="mark-all-read"><?= t('mark all as read') ?></a>
            </li>
        </ul>
    </div>

    <section class="items" id="listing">
    <?php foreach ($items as $item): ?>
        <article id="item-<?= $item['id'] ?>" data-item-id="<?= $item['id'] ?>" data-item-page="<?= $menu ?>" data-hide="true">
            <h2>
                <?= $item['bookmark'] ? '★ ' : '' ?>
                <a
                    href="?action=show&amp;menu=unread&amp;id=<?= $item['id'] ?>"
                    data-item-id="<?= $item['id'] ?>"
                    id="open-<?= $item['id'] ?>"
                >
                    <?= Helper\escape($item['title']) ?>
                </a>
            </h2>
            <p class="preview">
                <?= Helper\escape(Helper\summary(strip_tags($item['content']), 50, 300)) ?>
            </p>
            <p>
                <a href="?action=feed-items&amp;feed_id=<?= $item['feed_id'] ?>" title="<?= t('Show only this subscription') ?>"><?= Helper\escape($item['feed_title']) ?></a> |
                <span class="hide-mobile"><?= dt('%e %B %Y %k:%M', $item['updated']) ?> |</span>

                <span class="hide-mobile">
                <?php if ($item['bookmark']): ?>
                    <a id="bookmark-<?= $item['id'] ?>" href="?action=bookmark&amp;value=0&amp;id=<?= $item['id'] ?>&amp;menu=unread&amp;offset=<?= $offset ?>"><?= t('remove bookmark') ?></a> |
                <?php else: ?>
                    <a id="bookmark-<?= $item['id'] ?>" href="?action=bookmark&amp;value=1&amp;id=<?= $item['id'] ?>&amp;menu=unread&amp;offset=<?= $offset ?>"><?= t('bookmark') ?></a> |
                <?php endif ?>
                </span>

                <a
                    href="?action=mark-item-read&amp;id=<?= $item['id'] ?>&amp;offset=<?= $offset ?>"
                    data-action="mark-read"
                    data-item-id="<?= $item['id'] ?>"
                    data-hide="true"
                >
                    <?= t('mark as read') ?>
                </a> |
                <a
                    href="<?= $item['url'] ?>"
                    id="original-<?= $item['id'] ?>"
                    rel="noreferrer"
                    target="_blank"
                    data-item-id="<?= $item['id'] ?>"
                    data-action="original-link"
                    data-hide="true"
                >
                    <?= t('original link') ?>
                </a>
            </p>
        </article>
    <?php endforeach ?>

    <div id="bottom-menu">
        <a href="?action=mark-as-read" data-action="mark-all-read"><?= t('mark all as read') ?></a>
    </div>

    <div id="items-paging">
    <?php if ($offset > 0): ?>
        <a id="previous-page" href="?action=unread&amp;offset=<?= ($offset - $items_per_page) ?>&amp;order=<?= $order ?>&amp;direction=<?= $direction ?>">« <?= t('Previous page') ?></a>
    <?php endif ?>
    &nbsp;
    <?php if (($nb_items - $offset) > $items_per_page): ?>
        <a id="next-page" href="?action=unread&amp;offset=<?= ($offset + $items_per_page) ?>&amp;order=<?= $order ?>&amp;direction=<?= $direction ?>"><?= t('Next page') ?> »</a>
    <?php endif ?>
    </div>

    </section>

<?php endif ?>
