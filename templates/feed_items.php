<?php if (empty($items)): ?>

    <p class="alert alert-info"><?= t('No item') ?></p>

<?php else: ?>

    <div class="page-header">
        <h2><?= Helper\escape($feed['title']) ?> (<?= $nb_items ?>)</h2>
    </div>

    <section class="items" id="listing">
    <?php foreach ($items as $item): ?>
        <article id="item-<?= $item['id'] ?>" data-item-id="<?= $item['id'] ?>">
            <h2>
                <?= $item['bookmark'] ? '★ ' : '' ?>
                <a
                    href="?action=show&amp;menu=feed-items&amp;id=<?= $item['id'] ?>"
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
                <?= Helper\get_host_from_url($item['url']) ?> |
                <?= dt('%e %B %Y %k:%M', $item['updated']) ?> |

                <a
                    href="<?= $item['url'] ?>"
                    id="original-<?= $item['id'] ?>"
                    rel="noreferrer"
                    target="_blank"
                    data-item-id="<?= $item['id'] ?>"
                >
                    <?= t('original link') ?>
                </a>
            </p>
        </article>
    <?php endforeach ?>

    <nav id="items-paging">
    <?php if ($offset > 0): ?>
        <a id="previous-page" href="?action=feed-items&amp;feed_id=<?= $feed['id'] ?>&amp;offset=<?= ($offset - $items_per_page) ?>">⇽ <?= t('Previous page') ?></a>
    <?php endif ?>
    &nbsp;
    <?php if (($nb_items - $offset) > $items_per_page): ?>
        <a id="next-page" href="?action=feed-items&amp;feed_id=<?= $feed['id'] ?>&amp;offset=<?= ($offset + $items_per_page) ?>"><?= t('Next page') ?> ⇾</a>
    <?php endif ?>
    </nav>

    </section>

<?php endif ?>