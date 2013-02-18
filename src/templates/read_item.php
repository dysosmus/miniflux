<?php if (empty($item)): ?>

    <p class="alert alert-info">Article not found.</p>

<?php else: ?>

    <article class="item">
        <h1>
            <a href="<?= $item['url'] ?>" target="_blank"><?= Helper\escape($item['title']) ?></a>
        </h1>

        <p class="infos">
            <?= Helper\escape(parse_url($item['url'], PHP_URL_HOST)) ?> |
            <?= date('l, j F Y H:i T', $item['updated']) ?>
        </p>

        <?= $item['content'] ?>
    </article>

<?php endif ?>