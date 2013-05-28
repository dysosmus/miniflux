<?php if (empty($items)): ?>

    <p class="alert alert-info"><?= t('Nothing to read') ?></p>

<?php else: ?>

    <div class="page-header">
        <h2><?= t('Unread items') ?></h2>
        <ul>
            <li><a href="?action=mark-as-read"><?= t('mark all as read') ?></a></li>
        </ul>
    </div>

    <section class="items" id="listing">

        <?php $i = 0; foreach ($items as $item): ?>

                <?php $has_lazy_loading = $config['lazy_loading'] && $i > PRELOAD_SIZE; ?>

                <article id="item-<?= urlencode($item['id']) ?>" 
                         class="<?= $has_lazy_loading ? 'lazy-load' : '';?>"
                         data-item-id="<?= urlencode($item['id']) ?>"
                         data-content-url="?action=summary&amp;id=<?= urlencode($item['id']) ?>">

                    <?php if(!$has_lazy_loading): ?>

                        <?= PicoTools\Template\load('summary_item', array('item' => $item)); ?>

                    <?php endif; ?>

                </article>
                
        <?php $i++; endforeach ?>

        <?php if($config['lazy_loading']): ?>
            <a data-action="load-async" href="#load-more"><?= t('load more'); ?></a>
        <?php endif; ?>
    </section>

<?php endif ?>