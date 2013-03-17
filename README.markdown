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
- Use secure headers (only external images are allowed)
- Open external links inside a new tab with a `rel="noreferrer"` attribute
- Mobile CSS (responsive design)

Todo
----

- Remove older items from the database

Requirements
------------

- PHP >= 5.3
- PHP XML extensions (SimpleXML, DOM...)
- PHP Sqlite extensions

Libraries used
--------------

- [PicoFeed](https://github.com/fguillot/picoFeed)
- [PicoFarad](https://github.com/fguillot/picoFarad)
- [PicoTools](https://github.com/fguillot/picoTools)
- [PicoDb](https://github.com/fguillot/picoDb)
- [SimpleValidator](https://github.com/fguillot/simpleValidator)

Installation
------------

1. You must have a web server with PHP installed (version 5.3 minimum) with the Sqlite and XML extensions
2. Download the source code and copy the directory miniflux where you want
3. Check if the directory data is writeable (Miniflux store everything inside a Sqlite database)
4. With your browser go to <http://yourpersonalserver/miniflux>
5. The default login and password is admin/admin
6. Start to use the software

FAQ
----

### How to update your feeds with a cronjob?

You just need to be inside the directory `miniflux` and run the script `cronjob.php`.

By example:

    crontab -e

    0 */4 * * *  cd /path/to/miniflux && php cronjob.php >/dev/null 2>&1


### How Miniflux update my feeds from the user interface?

Miniflux use an Ajax request to refresh each subscription.
By default, there is only 5 feeds updated in parallel.


### I have 600 subscriptions, how Miniflux handle that?

Your life is cluttered.
