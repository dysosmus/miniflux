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
        <article id="item-<?= $item['id'] ?>" data-item-id="<?= $item['id'] ?>" data-item-page="<?= $menu ?>" data-hide="true">
            <h2>
                <?= $item['bookmark'] ? '★ ' : '' ?>
                <a
                    href="?action=show&amp;menu=history&amp;id=<?= $item['id'] ?>"
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

                <?php if (! $item['bookmark']): ?>
                    <span class="hide-mobile">
                    <a id="bookmark-<?= $item['id'] ?>" href="?action=bookmark&amp;value=1&amp;id=<?= $item['id'] ?>&amp;menu=history&amp;offset=<?= $offset ?>"><?= t('bookmark') ?></a> |
                    </span>
                <?php endif ?>

                <a
                    href="?action=mark-item-unread&amp;id=<?= $item['id'] ?>&amp;offset=<?= $offset ?>"
                    data-action="mark-unread"
                    data-item-id="<?= $item['id'] ?>"
                >
                    <?= t('mark as unread') ?>
                </a> |

                <span class="hide-mobile">
                <a href="?action=mark-item-removed&amp;id=<?= $item['id'] ?>&amp;offset=<?= $offset ?>"><?= t('remove') ?></a> |
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
        <a id="previous-page" href="?action=history&amp;offset=<?= ($offset - $items_per_page) ?>">⇽ <?= t('Previous page') ?></a>
    <?php endif ?>
    &nbsp;
    <?php if (($nb_items - $offset) > $items_per_page): ?>
        <a id="next-page" href="?action=history&amp;offset=<?= ($offset + $items_per_page) ?>"><?= t('Next page') ?> ⇾</a>
    <?php endif ?>
    </nav>

    </section>

<?php endif ?>
