#!/bin/sh

git log -n 20 --no-merges --date=short --format="<li id=\"%h\"><time>%ad</time> %s</li>"
