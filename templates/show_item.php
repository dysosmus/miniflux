<?php if (empty($item)): ?>

    <p class="alert alert-info"><?= t('Item not found') ?></p>

<?php else: ?>
    <article class="item" id="current-item" data-item-id="<?= $item['id'] ?>" data-item-page="<?= $menu ?>">
        <h1>
            <a href="<?= $item['url'] ?>" rel="noreferrer" target="_blank" id="original-item">
                <?= Helper\escape($item['title']) ?>
            </a>
        </h1>

        <p class="infos">
            <a href="?action=feed-items&amp;feed_id=<?= $feed['id'] ?>"><?= Helper\escape($feed['title']) ?></a> |
            <span class="hide-mobile"><?= dt('%e %B %Y - %k:%M', $item['updated']) ?> |</span>

            <?php if ($item['bookmark']): ?>
                <a href="?action=bookmark&amp;value=0&amp;id=<?= $item['id'] ?>&amp;source=show&amp;menu=<?= $menu ?>"><?= t('remove bookmark') ?></a>
            <?php else: ?>
                <a href="?action=bookmark&amp;value=1&amp;id=<?= $item['id'] ?>&amp;source=show&amp;menu=<?= $menu ?>"><?= t('bookmark') ?></a>
            <?php endif ?> |

            <span id="download-item"
                  data-item-id="<?= $item['id'] ?>"
                  data-failure-message="<?= t('unable to fetch content') ?>"
                  data-before-message="<?= t('in progress...') ?>"
                  data-after-message="<?= t('content downloaded') ?>">
                <a href="#" data-action="download-item">
                    <?= t('download content') ?>
                </a>
            </span>
        </p>

        <div id="item-content">
            <?= $item['content'] ?>
        </div>

        <?php if (isset($item_nav)): ?>
        <nav>
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?>&amp;id=<?= $item_nav['previous']['id'] ?>" id="previous-item" title="<?= t($item_nav['previous']['title']) ?>">« <?= t('Previous') ?></a>
                <?php else: ?>
                    « <?= t('Previous') ?>
                <?php endif ?>
            </span>

            <span class="nav-middle">
                <?php if ($item_nav['previous'] && $item_nav['next']): ?>
                    <a href="?action=<?= $menu ?>&amp;feed_id=<?= $feed['id'] ?>#item-<?= $item_nav['next']['id'] ?>"><?= t('Listing') ?></a>
                <?php elseif ($item_nav['previous'] && ! $item_nav['next']): ?>
                    <a href="?action=<?= $menu ?>&amp;feed_id=<?= $feed['id'] ?>#item-<?= $item_nav['previous']['id'] ?>"><?= t('Listing') ?></a>
                <?php elseif (! $item_nav['previous'] && $item_nav['next']): ?>
                    <a href="?action=<?= $menu ?>&amp;feed_id=<?= $feed['id'] ?>#item-<?= $item_nav['next']['id'] ?>"><?= t('Listing') ?></a>
                <?php elseif (! $item_nav['previous'] && ! $item_nav['next']): ?>
                    <a href="?action=<?= $menu ?>&amp;feed_id=<?= $feed['id'] ?>"><?= t('Unread items') ?></a>
                <?php endif ?>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?>&amp;id=<?= $item_nav['next']['id'] ?>" id="next-item" title="<?= t($item_nav['next']['title']) ?>"><?= t('Next') ?> »</a>
                <?php else: ?>
                    <?= t('Next') ?> »
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>
    </article>

<?php endif ?>
