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
        <?php $item_id = Model\encode_item_id($item['id']) ?>
        <article id="item-<?= $item_id ?>" data-item-id="<?= $item_id ?>">
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
                <?= dt('%A %e %B %Y %k:%M', $item['updated']) ?> |


                <?php if (isset($item['starred']) && $item['starred']=='starred'): ?>
                    <a href="?action=mark-item-unstarred&amp;id=<?= $item_id ?>"><?= t('mark as unstarred') ?></a> |
                <?php else: ?>
                    <a href="?action=mark-item-starred&amp;id=<?= $item_id ?>"><?= t('mark as starred') ?></a> |
                <?php endif ?>


                <a href="?action=mark-item-unread&amp;id=<?= $item_id ?>"><?= t('mark as unread') ?></a> |
                <a href="?action=mark-item-removed&amp;id=<?= $item_id ?>"><?= t('remove') ?></a> |
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
