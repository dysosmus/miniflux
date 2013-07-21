<?php if (empty($item)): ?>

    <p class="alert alert-info"><?= t('Item not found') ?></p>

<?php else: ?>
	<?php $item_id = Model\encode_item_id($item['id']) ?>
    <article class="item" id="current-item" data-item-id="<?= Model\encode_item_id($item['id']) ?>" data-item-page="<?= $menu ?>">
        <h1>
            <a href="<?= $item['url'] ?>" rel="noreferrer" target="_blank" id="original-item">
                <?= Helper\escape($item['title']) ?>
            </a>
        </h1>

        <p class="infos">
            <?= Helper\escape($feed['title']) ?> |
            <span class="hide-mobile"><?= dt('%A %e %B %Y %k:%M', $item['updated']) ?> |</span>
            <?php if ($item['bookmark']): ?>
                <a href="?action=bookmark&amp;value=0&amp;id=<?= $item_id ?>&amp;redirect=<?= $menu ?>"><?= t('remove bookmark') ?></a>
            <?php else: ?>
                <a href="?action=bookmark&amp;value=1&amp;id=<?= $item_id ?>&amp;redirect=<?= $menu ?>"><?= t('bookmark') ?></a>
            <?php endif ?>
        </p>

        <?= $item['content'] ?>

        <?php if (isset($item_nav)): ?>
        <nav>
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=read&amp;id=<?= Model\encode_item_id($item_nav['previous']['id']) ?>" id="previous-item">« <?= t('Previous') ?></a>
                <?php else: ?>
                    « <?= t('Previous') ?>
                <?php endif ?>
            </span>

            <span class="nav-middle">
                <?php if ($item_nav['previous'] && $item_nav['next']): ?>
                    <a href="?action=default#item-<?= Model\encode_item_id($item_nav['next']['id']) ?>"><?= t('Unread items') ?></a>
                <?php elseif ($item_nav['previous'] && ! $item_nav['next']): ?>
                    <a href="?action=default#item-<?= Model\encode_item_id($item_nav['previous']['id']) ?>"><?= t('Unread items') ?></a>
                <?php elseif (! $item_nav['previous'] && $item_nav['next']): ?>
                    <a href="?action=default#item-<?= Model\encode_item_id($item_nav['next']['id']) ?>"><?= t('Unread items') ?></a>
                <?php elseif (! $item_nav['previous'] && ! $item_nav['next']): ?>
                    <a href="?action=default"><?= t('Unread items') ?></a>
                <?php endif ?>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=read&amp;id=<?= Model\encode_item_id($item_nav['next']['id']) ?>" id="next-item"><?= t('Next') ?> »</a>
                <?php else: ?>
                    <?= t('Next') ?> »
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>
    </article>

<?php endif ?>
