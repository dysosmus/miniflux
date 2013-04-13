<?php if (empty($item)): ?>

    <p class="alert alert-info"><?= t('Item not found') ?></p>

<?php else: ?>

    <article class="item" id="current-item" data-item-id="<?= urlencode($item['id']) ?>">
        <h1>
            <a href="<?= $item['url'] ?>" rel="noreferrer" target="_blank" id="original-item"><?= Helper\escape($item['title']) ?></a>
        </h1>

        <p class="infos">
            <?= Helper\get_host_from_url($item['url']) ?> |
            <?= dt('%A %e %B %Y %k:%M', $item['updated']) ?>
        </p>

        <?= $item['content'] ?>

        <?php if (isset($item_nav)): ?>
        <nav>
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=read&amp;id=<?= urlencode($item_nav['previous']['id']) ?>" id="previous-item">« <?= t('Previous') ?></a>
                <?php else: ?>
                    « <?= t('Previous') ?>
                <?php endif ?>
            </span>

            <span class="nav-middle">
                <?php if ($item_nav['previous'] && $item_nav['next']): ?>
                    <a href="?action=default#item-<?= urlencode($item_nav['next']['id']) ?>"><?= t('Unread items') ?></a>
                <?php elseif ($item_nav['previous'] && ! $item_nav['next']): ?>
                    <a href="?action=default#item-<?= urlencode($item_nav['previous']['id']) ?>"><?= t('Unread items') ?></a>
                <?php elseif (! $item_nav['previous'] && $item_nav['next']): ?>
                    <a href="?action=default#item-<?= urlencode($item_nav['next']['id']) ?>"><?= t('Unread items') ?></a>
                <?php elseif (! $item_nav['previous'] && ! $item_nav['next']): ?>
                    <a href="?action=default"><?= t('Unread items') ?></a>
                <?php endif ?>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=read&amp;id=<?= urlencode($item_nav['next']['id']) ?>" id="next-item"><?= t('Next') ?> »</a>
                <?php else: ?>
                    <?= t('Next') ?> »
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>
    </article>

<?php endif ?>