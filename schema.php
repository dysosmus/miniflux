<?php

namespace Schema;


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
