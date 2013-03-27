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
    </article>

<?php endif ?>