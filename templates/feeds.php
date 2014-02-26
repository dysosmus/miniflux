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
        <p class="alert"><?= tne('Nothing to read, do you want to <a href="?action=refresh-all" data-action="refresh-all">update your subscriptions?</a>') ?></p>
    <?php endif ?>

    <section class="items">
    <?php foreach ($feeds as $feed): ?>
        <article>
            <h2 class="<?= (! $feed['enabled']) ? 'feed-disabled' : '' ?>">
                <?php if (! $feed['enabled']): ?>
                    <span title="<?= t('Subscription disabled') ?>">✖</a>
                <?php else: ?>
                    <span id="loading-feed-<?= $feed['id'] ?>" class="loading-icon"></span>
                <?php endif ?>

                <span id="items-count-<?= $feed['id'] ?>">(<?= $feed['items_unread'] .'/' . $feed['items_total'] ?>)</span>

                <a href="?action=feed-items&amp;feed_id=<?= $feed['id'] ?>" title="<?= t('Show only this subscription') ?>"><?= Helper\escape($feed['title']) ?></a>

                <?php if ($feed['enabled']): ?>

                    <br/>

                    <?php if ($feed['last_checked']): ?>
                        <time class="feed-last-checked" id="last-checked-feed-<?= $feed['id'] ?>" data-after-update="<?= t('updated just now') ?>">
                            <?= t('checked at').' '.dt('%e %B %Y %k:%M', $feed['last_checked']) ?>
                        </time>
                    <?php else: ?>
                        <span class="feed-last-checked" id="last-checked-feed-<?= $feed['id'] ?>" data-after-update="<?= t('now') ?>">
                            <?= t('never updated after creation') ?>
                        </span>
                    <?php endif ?>

                    <?php if ($feed['parsing_error']): ?>
                        <span class="feed-parsing-error"><?= t('(error occurred during the last check)') ?></span>
                    <?php endif ?>

                <?php endif ?>
            </h2>
            <ul class="item-menu">
                <li>
                    <a href="<?= $feed['site_url'] ?>" rel="noreferrer" target="_blank"><?= Helper\get_host_from_url($feed['site_url']) ?></a>
                </li>
                <li class="hide-mobile">
                    <a href="?action=confirm-remove-feed&amp;feed_id=<?= $feed['id'] ?>"><?= t('remove') ?></a>
                </li>
                <li class="hide-mobile">
                    <?php if ($feed['download_content']): ?>
                        <a href="?action=disable-grabber-feed&amp;feed_id=<?= $feed['id'] ?>"><strong><?= t('disable full content') ?></strong></a>
                    <?php else: ?>
                        <a href="?action=enable-grabber-feed&amp;feed_id=<?= $feed['id'] ?>"><?= t('enable full content') ?></a>
                    <?php endif ?>
                </li>

                <?php if ($feed['enabled']): ?>
                <li class="hide-mobile">
                    <a href="?action=confirm-disable-feed&amp;feed_id=<?= $feed['id'] ?>"><?= t('disable') ?></a>
                </li>
                <li class="hide-mobile">
                    <a href="?action=refresh-feed&amp;feed_id=<?= $feed['id'] ?>" data-feed-id="<?= $feed['id'] ?>" data-action="refresh-feed"><?= t('refresh') ?></a>
                </li>
                <?php else: ?>
                <li>
                    <a href="?action=enable-feed&amp;feed_id=<?= $feed['id'] ?>"><?= t('enable') ?></a>
                </li>
                <?php endif ?>

                <li class="hide-mobile">
                    <a href="?action=edit-feed&amp;feed_id=<?= $feed['id'] ?>"><?= t('edit') ?></a>
                </li>
            </ul>
        </article>
    <?php endforeach ?>
    </section>

<?php endif ?>
