#!/bin/sh

curl \
-u "admin:UkL1sN2xACNsySQ" \
-d '{"jsonrpc": "2.0", "method": "feed.create", "params": {"url": "http://images.apple.com/main/rss/hotnews/hotnews.rss"}, "id": 1}' \
http://127.0.0.1:8000/jsonrpc.php

