<?php

namespace Schema;

function version_24($pdo)
{
    $pdo->exec("ALTER TABLE config ADD COLUMN update_url TEXT DEFAULT 'https://github.com/fguillot/miniflux/archive/master.zip");
}

function version_23($pdo)
{
    $pdo->exec('ALTER TABLE items ADD COLUMN language TEXT');
}


function version_22($pdo)
{
    $pdo->exec("ALTER TABLE config ADD COLUMN timezone TEXT DEFAULT 'UTC'");
}


function version_21($pdo)
{
    $pdo->exec('ALTER TABLE items ADD COLUMN enclosure TEXT');
    $pdo->exec('ALTER TABLE items ADD COLUMN enclosure_type TEXT');
}


function version_20($pdo)
{
    $pdo->exec('ALTER TABLE config ADD COLUMN redirect_nothing_to_read TEXT DEFAULT "feeds"');
}


function version_19($pdo)
{
    $rq = $pdo->prepare('SELECT autoflush FROM config');
    $rq->execute();
    $value = (int) $rq->fetchColumn();

    // Change default value of autoflush to 15 days to avoid very large database
    if ($value <= 0) {
        $rq = $pdo->prepare('UPDATE config SET autoflush=?');
        $rq->execute(array(15));
    }
}


function version_18($pdo)
{
    $pdo->exec('ALTER TABLE feeds ADD COLUMN parsing_error INTEGER DEFAULT 0');
}


function version_17($pdo)
{
    $pdo->exec('ALTER TABLE config ADD COLUMN items_sorting_direction TEXT DEFAULT "desc"');
}


function version_16($pdo)
{
    $pdo->exec('ALTER TABLE config ADD COLUMN auth_google_token TEXT DEFAULT ""');
    $pdo->exec('ALTER TABLE config ADD COLUMN auth_mozilla_token TEXT DEFAULT ""');
}


function version_15($pdo)
{
    $pdo->exec('ALTER TABLE feeds ADD COLUMN download_content INTEGER DEFAULT 0');
}


function version_14($pdo)
{
    $pdo->exec('ALTER TABLE config ADD COLUMN feed_token TEXT DEFAULT "'.\Model\Config\generate_token().'"');
}


function version_13($pdo)
{
    $pdo->exec('ALTER TABLE feeds ADD COLUMN enabled INTEGER DEFAULT 1');
}


function version_12($pdo)
{
    $pdo->exec('ALTER TABLE config ADD COLUMN api_token TEXT DEFAULT "'.\Model\Config\generate_token().'"');
}


function version_11($pdo)
{
    $rq = $pdo->prepare('
        SELECT
        items.id, items.url AS item_url, feeds.site_url
        FROM items
        LEFT JOIN feeds ON feeds.id=items.feed_id
    ');

    $rq->execute();

    $items = $rq->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($items as $item) {

        if ($item['id'] !== $item['item_url']) {

            $id = hash('crc32b', $item['id'].$item['site_url']);
        }
        else {

            $id = hash('crc32b', $item['item_url'].$item['site_url']);
        }

        $rq = $pdo->prepare('UPDATE items SET id=? WHERE id=?');
        $rq->execute(array($id, $item['id']));
    }
}


function version_10($pdo)
{
    $pdo->exec('ALTER TABLE config ADD COLUMN theme TEXT DEFAULT "original"');
}


function version_9($pdo)
{
    $pdo->exec('ALTER TABLE config ADD COLUMN items_per_page INTEGER DEFAULT 100');
}


function version_8($pdo)
{
    $pdo->exec('ALTER TABLE items ADD COLUMN bookmark INTEGER DEFAULT 0');
}


function version_7($pdo)
{
    $pdo->exec('ALTER TABLE config ADD COLUMN nocontent INTEGER DEFAULT 0');
}


function version_6($pdo)
{
    $pdo->exec('ALTER TABLE config ADD COLUMN autoflush INTEGER DEFAULT 0');
}


function version_5($pdo)
{
    $pdo->exec('ALTER TABLE feeds ADD COLUMN last_checked INTEGER');
}


function version_4($pdo)
{
    $pdo->exec('CREATE INDEX idx_status ON items(status)');
}


function version_3($pdo)
{
    $pdo->exec("ALTER TABLE config ADD COLUMN language TEXT DEFAULT 'en_US'");
}


function version_2($pdo)
{
    $pdo->exec('ALTER TABLE feeds ADD COLUMN last_modified TEXT');
    $pdo->exec('ALTER TABLE feeds ADD COLUMN etag TEXT');
}


function version_1($pdo)
{
    $pdo->exec("
        CREATE TABLE config (
            username TEXT DEFAULT 'admin',
            password TEXT
        )
    ");

    $pdo->exec("
        INSERT INTO config
        (password)
        VALUES ('".\password_hash('admin', PASSWORD_BCRYPT)."')
    ");

    $pdo->exec('
        CREATE TABLE feeds (
            id INTEGER PRIMARY KEY,
            site_url TEXT,
            feed_url TEXT UNIQUE,
            title TEXT COLLATE NOCASE
        )
    ');

    $pdo->exec('
        CREATE TABLE items (
            id TEXT PRIMARY KEY,
            url TEXT,
            title TEXT,
            author TEXT,
            content TEXT,
            updated INTEGER,
            status TEXT,
            feed_id INTEGER,
            FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
        )
    ');
}
