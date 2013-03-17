<?php

namespace Schema;

function version_1($pdo)
{
    $pdo->exec("
        CREATE TABLE config (
            username TEXT DEFAULT 'admin',
            password TEXT,
            history INTEGER DEFAULT '15'
        )
    ");

    $pdo->exec("
        INSERT INTO config
        (password)
        VALUES ('".\PicoTools\Crypto\password_hash('admin')."')
    ");

    $pdo->exec('
        CREATE TABLE feeds (
            id INTEGER PRIMARY KEY,
            site_url TEXT,
            feed_url TEXT UNIQUE,
            title TEXT
        )
    ');

    $pdo->exec('
        CREATE TABLE items (
            id TEXT PRIMARY KEY,
            url TEXT,
            title TEXT,
            author TEXT,
            content TEXT,
            updated TEXT,
            status TEXT,
            feed_id INTEGER,
            FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
        )
    ');
}