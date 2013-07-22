#!/bin/bash

VERSION=$1

cd /tmp
rm -rf /tmp/miniflux /tmp/miniflux-*.zip 2>/dev/null
git clone git@github.com:fguillot/miniflux.git
rm -rf miniflux/data/*.sqlite miniflux/.git miniflux/.gitignore miniflux/*.sh miniflux/examples
sed -i.bak s/master/$VERSION/g miniflux/common.php && rm -f miniflux/*.bak
zip -r miniflux-$VERSION.zip miniflux
mv miniflux-*.zip ~/Devel/websites/miniflux
rm -rf /tmp/miniflux 2>/dev/null

