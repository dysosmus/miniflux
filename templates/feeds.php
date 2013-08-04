<div class="page-header">
    <h2><?= t('Subscriptions') ?></h2>
    <ul>
        <li><a href="?action=add"><?= t('add') ?></a></li>
        <li><a href="?action=import"><?= t('import') ?></a></li>
        <li><a href="?action=export"><?= t('export') ?></a></li>
        <li><a href="?action=refresh-all" data-action="refresh-all"><?= t('refresh all') ?></a></li>
    </ul>
</div>

<?php if (empty($feeds)): ?>

    <p class="alert alert-info"><?= t('No subscription') ?></p>

<?php else: ?>

    <?php if ($nothing_to_read): ?>
        <p class="alert"><?= t('Nothing to read, do you want to <a href="?action=refresh-all" data-action="refresh-all">update your subscriptions?</a>') ?></p>
    <?php endif ?>

    <section class="items">
    <?php foreach ($feeds as $feed): ?>
        <article>
            <h2 class="<?= (! $feed['enabled']) ? 'feed-disabled' : '' ?>">
                <?php if (! $feed['enabled']): ?>
                    <span title="<?= t('Subscription disabled') ?>">∅</a>
                <?php else: ?>
                    <span id="loading-feed-<?= $feed['id'] ?>"></span>
                <?php endif ?>

                <a href="<?= $feed['site_url'] ?>" rel="noreferrer" target="_blank"><?= Helper\escape($feed['title']) ?></a>

            </h2>
            <p>
                <a href="<?= $feed['site_url'] ?>" rel="noreferrer" target="_blank"><?= Helper\get_host_from_url($feed['site_url']) ?></a> |

                <span class="hide-mobile"><a href="?action=confirm-remove-feed&amp;feed_id=<?= $feed['id'] ?>"><?= t('remove') ?></a> |</span>

                <?php if ($feed['enabled']): ?>
                    <span class="hide-mobile"><a href="?action=confirm-disable-feed&amp;feed_id=<?= $feed['id'] ?>"><?= t('disable') ?></a> |</span>
                    <a href="?action=refresh-feed&amp;feed_id=<?= $feed['id'] ?>" data-feed-id="<?= $feed['id'] ?>" data-action="refresh-feed"><?= t('refresh') ?></a> |
                <?php else: ?>
                    <span class="hide-mobile"><a href="?action=enable-feed&amp;feed_id=<?= $feed['id'] ?>"><?= t('enable') ?></a> |</span>
                <?php endif ?>

                <span class="hide-mobile"><a href="?action=feed-items&amp;feed_id=<?= $feed['id'] ?>"><?= t('items') ?></a></span>
            </p>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>