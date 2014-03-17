<?php if (empty($items)): ?>
    <p class="alert">
        <?= tne('This subscription is empty, <a href="?action=unread">go back to unread items</a>') ?>
    </p>
<?php else: ?>

    <div class="page-header">
        <h2><?= Helper\escape($feed['title']) ?> (<?= $nb_items ?>)</h2>
        <ul>
            <li>
                <a href="?action=feed-items&amp;feed_id=<?= $feed['id'] ?>&amp;order=updated&amp;direction=<?= $direction == 'asc' ? 'desc' : 'asc' ?>"><?= tne('sort by date<span class="hide-mobile"> (%s)</span>', $direction == 'desc' ? t('older first') : t('most recent first')) ?></a>
            </li>
            <li>
                <a href="?action=mark-feed-as-read&amp;feed_id=<?= $feed['id'] ?>" data-action="mark-feed-read" data-feed-id="<?= $feed['id'] ?>"><?= t('mark all as read') ?></a>
            </li>
        </ul>
    </div>

    <section class="items" id="listing">
        <?php foreach ($items as $item): ?>
            <?= \PicoFarad\Template\load('item', array('item' => $item, 'menu' => $menu, 'offset' => $offset, 'hide' => false)) ?>
        <?php endforeach ?>

        <div id="bottom-menu">
            <a href="?action=mark-feed-as-read&amp;feed_id=<?= $feed['id'] ?>" data-action="mark-feed-read" data-feed-id="<?= $feed['id'] ?>"><?= t('mark all as read') ?></a>
        </div>

        <?= \PicoFarad\Template\load('paging', array('menu' => $menu, 'nb_items' => $nb_items, 'items_per_page' => $items_per_page, 'offset' => $offset, 'order' => $order, 'direction' => $direction, 'feed_id' => $feed['id'])) ?>
    </section>

<?php endif ?>