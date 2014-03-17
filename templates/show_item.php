<?php if (empty($item)): ?>
    <p class="alert alert-info"><?= t('Item not found') ?></p>
<?php else: ?>
    <article
        class="item"
        id="current-item"
        data-item-id="<?= $item['id'] ?>"
        data-item-status="<?= $item['status'] ?>"
        data-item-bookmark="<?= $item['bookmark'] ?>"
        data-item-page="<?= $menu ?>"
    >

        <?php if (isset($item_nav)): ?>
        <nav class="top hide-desktop">
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?>&amp;id=<?= $item_nav['previous']['id'] ?>" id="previous-item" title="<?= Helper\escape($item_nav['previous']['title']) ?>">« <?= t('Previous') ?></a>
                <?php else: ?>
                    « <?= t('Previous') ?>
                <?php endif ?>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?>&amp;id=<?= $item_nav['next']['id'] ?>" id="next-item" title="<?= Helper\escape($item_nav['next']['title']) ?>"><?= t('Next') ?> »</a>
                <?php else: ?>
                    <?= t('Next') ?> »
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>

        <h1 <?= Helper\isRTL($item['language']) ? 'dir="rtl"' : '' ?>>
            <a href="<?= $item['url'] ?>" rel="noreferrer" target="_blank" id="original-<?= $item['id'] ?>">
                <?= Helper\escape($item['title']) ?>
            </a>
        </h1>

        <ul class="item-infos">
            <li>
            <?php if ($item['bookmark']): ?>
                <a
                    id="bookmark-<?=$item['id'] ?>"
                    href="?action=bookmark&amp;value=0&amp;id=<?= $item['id'] ?>&amp;source=show&amp;menu=<?= $menu ?>"
                    title="<?= t('remove bookmark') ?>"
                    class="bookmark-icon"
                    data-action="bookmark"
                    data-item-id="<?= $item['id'] ?>"
                >★</a>
            <?php else: ?>
                <a
                    id="bookmark-<?=$item['id'] ?>"
                    href="?action=bookmark&amp;value=1&amp;id=<?= $item['id'] ?>&amp;source=show&amp;menu=<?= $menu ?>"
                    title="<?= t('bookmark') ?>"
                    class="bookmark-icon"
                    data-action="bookmark"
                    data-item-id="<?= $item['id'] ?>"
                >☆</a>
            <?php endif ?>
            </li>
            <li>
                <a href="?action=feed-items&amp;feed_id=<?= $feed['id'] ?>"><?= Helper\escape($feed['title']) ?></a>
            </li>
            <li class="hide-mobile">
                <span title="<?= dt('%e %B %Y %k:%M', $item['updated']) ?>"><?= Helper\relative_time($item['updated']) ?></span>
            </li>
            <?php if ($item['enclosure']): ?>
            <li>
                <a href="<?= $item['enclosure'] ?>" rel="noreferrer" target="_blank"><?= t('attachment') ?></a>
            </li>
            <?php endif ?>
            <li class="hide-mobile">
                <span id="download-item"
                      data-item-id="<?= $item['id'] ?>"
                      data-failure-message="<?= t('unable to fetch content') ?>"
                      data-before-message="<?= t('in progress...') ?>"
                      data-after-message="<?= t('content downloaded') ?>">
                    <a href="#" data-action="download-item">
                        <?= t('download content') ?>
                    </a>
                </span>
            </li>
        </ul>

        <div id="item-content" <?= Helper\isRTL($item['language']) ? 'dir="rtl"' : '' ?>>
            <?= $item['content'] ?>

            <?php if ($item['enclosure']): ?>
                <?php if (strpos($item['enclosure_type'], 'audio') !== false): ?>
                    <audio controls>
                        <source src="<?= $item['enclosure'] ?>" type="<?= $item['enclosure_type'] ?>">
                    </audio>
                <?php elseif (strpos($item['enclosure_type'], 'video') !== false): ?>
                    <video controls>
                        <source src="<?= $item['enclosure'] ?>" type="<?= $item['enclosure_type'] ?>">
                    </video>
                <?php endif ?>
            <?php endif ?>
        </div>

        <?php if (isset($item_nav)): ?>
        <nav class="bottom">
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?>&amp;id=<?= $item_nav['previous']['id'] ?>" id="previous-item" title="<?= Helper\escape($item_nav['previous']['title']) ?>">« <?= t('Previous') ?></a>
                <?php else: ?>
                    « <?= t('Previous') ?>
                <?php endif ?>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?>&amp;id=<?= $item_nav['next']['id'] ?>" id="next-item" title="<?= Helper\escape($item_nav['next']['title']) ?>"><?= t('Next') ?> »</a>
                <?php else: ?>
                    <?= t('Next') ?> »
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>
    </article>

<?php endif ?>
