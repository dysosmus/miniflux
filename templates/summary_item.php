<h2>
    <a
        href="?action=read&amp;id=<?= urlencode($item['id']) ?>"
        id="open-<?= urlencode($item['id']) ?>"
    >
        <?= Helper\escape($item['title']) ?>
    </a>
</h2>
<p class="preview">
    <?= Helper\escape(Helper\summary(strip_tags($item['content']), 50, 300)) ?>
</p>
<p>
    <?= Helper\get_host_from_url($item['url']) ?> |
    <a href="?action=mark-item-read&amp;id=<?= urlencode($item['id']) ?>"><?= t('mark as read') ?></a> |
    <a
        href="<?= $item['url'] ?>"
        id="original-<?= urlencode($item['id']) ?>"
        rel="noreferrer"
        target="_blank"
        data-item-id="<?= urlencode($item['id']) ?>"
        data-action="mark-read"
    >
        <?= t('original link') ?>
    </a>
</p>