<?php if (empty($item)): ?>

    <p class="alert alert-info">Article not found.</p>

<?php else: ?>

    <article class="item">
        <h1>
            <a href="<?= $item['url'] ?>" rel="noreferrer" target="_blank"><?= Helper\escape($item['title']) ?></a>
        </h1>

        <p class="infos">
            <?= Helper\get_host_from_url($item['url']) ?> |
            <?= date('l, j F Y H:i', $item['updated']) ?>
        </p>

        <?= $item['content'] ?>

        <?php if (isset($item_nav)): ?>
        <nav>
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=read&amp;id=<?= urlencode($item_nav['previous']['id']) ?>">« Previous</a>
                <?php else: ?>
                    « Previous
                <?php endif ?>
            </span>

            <span class="nav-middle">
                <?php if ($item_nav['previous'] && $item_nav['next']): ?>
                    <a href="?action=default&amp;#item-<?= urlencode($item_nav['next']['id']) ?>">Unread items</a>
                <?php elseif ($item_nav['previous'] && ! $item_nav['next']): ?>
                    <a href="?action=default&amp;#item-<?= urlencode($item_nav['previous']['id']) ?>">Unread items</a>
                <?php elseif (! $item_nav['previous'] && $item_nav['next']): ?>
                    <a href="?action=default&amp;#item-<?= urlencode($item_nav['next']['id']) ?>">Unread items</a>
                <?php elseif (! $item_nav['previous'] && ! $item_nav['next']): ?>
                    <a href="?action=default">Unread items</a>
                <?php endif ?>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=read&amp;id=<?= urlencode($item_nav['next']['id']) ?>">Next »</a>
                <?php else: ?>
                    Next »
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>
    </article>

<?php endif ?>