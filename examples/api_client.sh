#!/bin/sh

curl \
-u "demo:/EobxWLMrb+VO8G" \
-d '{"jsonrpc": "2.0", "method": "feed.create", "params": {"url": "http://images.apple.com/main/rss/hotnews/hotnews.rss"}, "id": 1}' \
https://miniflux.net/demo/jsonrpc.php

