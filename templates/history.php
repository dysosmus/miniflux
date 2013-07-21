<?php if (empty($items)): ?>
    <p class="alert alert-info"><?= t('No history') ?></p>
<?php else: ?>

    <div class="page-header">
        <h2><?= t('History') ?> (<?= $nb_items ?>)</h2>
        <ul>
            <li><a href="?action=confirm-flush-history"><?= t('flush all items') ?></a></li>
        </ul>
    </div>

    <section class="items" id="listing">
    <?php foreach ($items as $item): ?>
        <?php $item_id = Model\encode_item_id($item['id']) ?>
        <article id="item-<?= $item_id ?>" data-item-id="<?= $item_id ?>" data-item-page="<?= $menu ?>" data-hide="true">
            <h2>
                <?= $item['bookmark'] ? '★ ' : '' ?>
                <a
                    href="?action=show&amp;id=<?= $item_id ?>"
                    data-item-id="<?= $item_id ?>"
                    id="open-<?= $item_id ?>"
                >
                    <?= Helper\escape($item['title']) ?>
                </a>
            </h2>
            <p>
                <?= Helper\escape($item['feed_title']) ?> |

                <span class="hide-mobile"><?= dt('%e %B %Y %k:%M', $item['updated']) ?> |</span>

                <?php if (! $item['bookmark']): ?>
                    <span class="hide-mobile">
                    <a id="bookmark-<?= $item_id ?>" href="?action=bookmark&amp;value=1&amp;id=<?= $item_id ?>&amp;redirect=history&amp;offset=<?= $offset ?>"><?= t('bookmark') ?></a> |
                    </span>
                <?php endif ?>

                <a
                    href="?action=mark-item-unread&amp;id=<?= $item_id ?>&amp;offset=<?= $offset ?>"
                    data-action="mark-unread"
                    data-item-id="<?= $item_id ?>"
                >
                    <?= t('mark as unread') ?>
                </a> |

                <span class="hide-mobile">
                <a href="?action=mark-item-removed&amp;id=<?= $item_id ?>&amp;offset=<?= $offset ?>"><?= t('remove') ?></a> |
                </span>

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

    <nav id="items-paging">
    <?php if ($offset > 0): ?>
        <a id="previous-page" href="?action=history&amp;offset=<?= ($offset - $items_per_page) ?>">⇽ <?= t('Previous page') ?></a>
    <?php endif ?>
    &nbsp;
    <?php if (($nb_items - $offset) > $items_per_page): ?>
        <a id="next-page" href="?action=history&amp;offset=<?= ($offset + $items_per_page) ?>"><?= t('Next page') ?> ⇾</a>
    <?php endif ?>
    </nav>

    </section>

<?php endif ?>
