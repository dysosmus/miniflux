<?php if (empty($items)): ?>
    <p class="alert alert-info"><?= t('No bookmark') ?></p>
<?php else: ?>

    <div class="page-header">
        <h2><?= t('Bookmarks') ?> (<?= $nb_items ?>)</h2>
    </div>

    <?php if ($nothing_to_read): ?>
        <p class="alert"><?= t('There is nothing new to read, enjoy your favorites articles!') ?></p>
    <?php endif ?>

    <section class="items" id="listing">
        <?php foreach ($items as $item): ?>
            <?= \PicoFarad\Template\load('item', array('item' => $item, 'menu' => $menu, 'offset' => $offset, 'hide' => false)) ?>
        <?php endforeach ?>

        <?= \PicoFarad\Template\load('paging', array('menu' => $menu, 'nb_items' => $nb_items, 'items_per_page' => $items_per_page, 'offset' => $offset, 'order' => $order, 'direction' => $direction)) ?>
    </section>

<?php endif ?>