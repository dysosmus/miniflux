<?php if (empty($item)): ?>

    <p class="alert alert-info"><?= t('Item not found') ?></p>

<?php else: ?>
    <article class="item" id="current-item" data-item-id="<?= Model\encode_item_id($item['id']) ?>">
        <h1>
            <a href="<?= $item['url'] ?>" rel="noreferrer" target="_blank" id="original-item"><?= Helper\escape($item['title']) ?></a>
        </h1>

        <p class="infos">
            <?= Helper\get_host_from_url($item['url']) ?> |
            <?= dt('%A %e %B %Y %k:%M', $item['updated']) ?> |
		    <a href="?action=mark-item-unstarred&amp;id=<?= $item_id ?>"><?= t('mark as unstarred') ?></a>
        </p>

        <?= $item['content'] ?>

        <?php if (isset($item_nav)): ?>
        <nav>
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=read_starred&amp;id=<?= Model\encode_item_id($item_nav['previous']['id']) ?>" id="previous-item">« <?= t('Previous') ?></a>
                <?php else: ?>
                    « <?= t('Previous') ?>
                <?php endif ?>
            </span>

            <span class="nav-middle">
                    <a href="?action=starred"><?= t('Starred') ?></a>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=read_starred&amp;id=<?= Model\encode_item_id($item_nav['next']['id']) ?>" id="next-item"><?= t('Next') ?> »</a>
                <?php else: ?>
                    <?= t('Next') ?> »
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>
    </article>

<?php endif ?>
