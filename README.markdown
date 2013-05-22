Miniflux - Minimalist News Reader
=================================

Miniflux is a minimalist web-based news reader.

Features
--------

- Host anywhere (shared hosting, vps or localhost)
- Easy setup => copy and paste and you are done!
- CSS optimized for readability
- Keep an history of read items
- Remove Feedburner Ads and analytics trackers
- Import/Export OPML feeds
- Feeds update by a cronjob or with the user interface in one click
- Protected by a login/password (only one possible user)
- Use secure headers (only external images and Youtube/Vimeo videos are allowed)
- Open external links inside a new tab with a `rel="noreferrer"` attribute
- Mobile CSS (responsive design)
- Keyboard shortcuts
- Lazy loading (optional)

Todo and known bugs
-------------------

- See Issues: <https://github.com/fguillot/miniflux/issues>

License
-------

- AGPL: <http://www.gnu.org/licenses/agpl-3.0.txt>

Authors
-------

- Original author: [Frédéric Guillot](http://fredericguillot.com/)
- Contributors: [Pull requesters](https://github.com/fguillot/miniflux/pulls?direction=desc&page=1&sort=created&state=closed) and [Bug reporters](https://github.com/fguillot/miniflux/issues?page=1&state=closed)

Requirements
------------

- PHP >= 5.3.7
- PHP XML extensions (SimpleXML, DOM...)
- PHP Sqlite extension

Libraries used
--------------

- [PicoFeed](https://github.com/fguillot/picoFeed)
- [PicoFarad](https://github.com/fguillot/picoFarad)
- [PicoTools](https://github.com/fguillot/picoTools)
- [PicoDb](https://github.com/fguillot/picoDb)
- [SimpleValidator](https://github.com/fguillot/simpleValidator)
- [PHP 5.5 password backport](https://github.com/ircmaxell/password_compat)

Installation
------------

1. You must have a web server with PHP installed (version 5.3.7 minimum) with the Sqlite and XML extensions
2. Download the source code and copy the directory miniflux where you want
3. Check if the directory data is writeable (Miniflux store everything inside a Sqlite database)
4. With your browser go to <http://yourpersonalserver/miniflux>
5. The default login and password is **admin/admin**
6. Start to use the software

FAQ
----

### How to update your feeds with a cronjob?

You just need to be inside the directory `miniflux` and run the script `cronjob.php`.

Parameters          | Type         		             | Value
--------------------|--------------------------------|-----------------------------
--limit             | optional                       | number of feeds
--call-interval     | optional, excluded by --limit, require --update-interval | time in minutes < update interval time
--update-interval   | optional, excluded by --limit, require --call-interval   | time in minutes >= call interval time


Examples:

    crontab -e

    # Update all feeds
    0 */4 * * *  cd /path/to/miniflux && php cronjob.php >/dev/null 2>&1

	# Update the 10 oldest feeds each time
    0 */4 * * *  cd /path/to/miniflux && php cronjob.php --limit=10 >/dev/null 2>&1

	# Update all feeds in 60 minutes (updates the 8 oldest feeds each time with a total of 120 feeds).
    * */4 * * *  cd /path/to/miniflux && php cronjob.php --call-interval=4 --update-interval=60 >/dev/null 2>&1

Note: cronjob.php can also be called from the web; in this case specify the options as GET variables.
Example: <http://yourpersonalserver/miniflux/cronjob.php?call-interval=4&update-interval=60>

### How Miniflux update my feeds from the user interface?

Miniflux use an Ajax request to refresh each subscription.
By default, there is only 5 feeds updated in parallel.

### I have 600 subscriptions, how Miniflux handle that?

Your life is cluttered.

### Why there is no categories? Why the feature X is missing?

Miniflux is a minimalist software. Less is more.

### Why there is no favourites?

Use the right tool for the right job.
Your browser already have bookmarks, if you don't like it there is many online tools for that.

### I found a bug, what next?

Report the bug to the [issues tracker](https://github.com/fguillot/miniflux/issues) and I will fix it.

You can report feeds that doesn't works properly too.

### Which browser is compatible with Miniflux?

Miniflux is tested with the last version of Mozilla Firefox, Google Chrome and Safari.

I don't use Microsoft products, then I have no idea if Miniflux works correctly with Internet Explorer.
