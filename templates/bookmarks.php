<?php if (empty($items)): ?>

    <p class="alert alert-info"><?= t('No bookmark') ?></p>

<?php else: ?>

    <div class="page-header">
        <h2><?= t('Bookmarks') ?> (<?= $nb_items ?>)</h2>
    </div>

    <section class="items" id="listing">
    <?php foreach ($items as $item): ?>
        <article id="item-<?= $item['id'] ?>" data-item-id="<?= $item['id'] ?>">
            <h2>
                <a
                    href="?action=show&amp;menu=bookmarks&amp;id=<?= $item['id'] ?>"
                    data-item-id="<?= $item['id'] ?>"
                    id="open-<?= $item['id'] ?>"
                >
                    <?= Helper\escape($item['title']) ?>
                </a>
            </h2>
            <p>
                <a href="?action=feed-items&amp;feed_id=<?= $item['feed_id'] ?>"><?= Helper\escape($item['feed_title']) ?></a> |
                <?= dt('%e %B %Y %k:%M', $item['updated']) ?> |

                <span class="hide-mobile">
                    <a href="?action=bookmark&amp;value=0&amp;id=<?= $item['id'] ?>&amp;menu=bookmarks&amp;offset=<?= $offset ?>">
                        <?= t('remove bookmark') ?>
                    </a> |
                </span>

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
        <a id="previous-page" href="?action=bookmarks&amp;offset=<?= ($offset - $items_per_page) ?>">⇽ <?= t('Previous page') ?></a>
    <?php endif ?>
    &nbsp;
    <?php if (($nb_items - $offset) > $items_per_page): ?>
        <a id="next-page" href="?action=bookmarks&amp;offset=<?= ($offset + $items_per_page) ?>"><?= t('Next page') ?> ⇾</a>
    <?php endif ?>
    </nav>

    </section>

<?php endif ?>