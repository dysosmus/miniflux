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
            <?= \PicoTools\Template\load('item', array('item' => $item, 'menu' => $menu, 'offset' => $offset, 'hide' => true)) ?>
        <?php endforeach ?>

        <div id="bottom-menu">
            <a href="?action=mark-as-read" data-action="mark-all-read"><?= t('mark all as read') ?></a>
        </div>

        <?= \PicoTools\Template\load('paging', array('menu' => $menu, 'nb_items' => $nb_items, 'items_per_page' => $items_per_page, 'offset' => $offset, 'order' => $order, 'direction' => $direction)) ?>
    </section>

<?php endif ?>
