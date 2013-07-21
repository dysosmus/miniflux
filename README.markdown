Miniflux - Minimalist News Reader
=================================

Miniflux is a minimalist web-based news reader.

Features
--------

- Host anywhere (shared hosting, vps or localhost)
- Easy setup => copy and paste and you are done!
- CSS optimized for readability
- Keeps history of read items
- Remove Feedburner Ads and analytics trackers
- Import/Export of OPML feeds
- Feed updates via a cronjob or with the user interface with one click
- Protected by a login/password (only one possible user)
- Use secure headers (only external images and Youtube/Vimeo videos are allowed)
- Open external links inside a new tab with a `rel="noreferrer"` attribute
- Mobile CSS (responsive design)
- Keyboard shortcuts (pressing '?' displays a pop-up listing the shortcuts; pressing 'q' closes it)
- Basic bookmarks
- Translated in English, French, German, Italian, Czech and Simplified Chinese
- Themes

Todo and known bugs
-------------------

- See Issues: <https://github.com/fguillot/miniflux/issues>

License
-------

- AGPL: <http://www.gnu.org/licenses/agpl-3.0.txt>

Authors
-------

Original author: [Frédéric Guillot](http://fredericguillot.com/)

### Contributors

People who sent one or many pull-requests:

- André Kelpe: https://github.com/fs111
- Ayodio: https://github.com/ayodio
- Chris Lemonier: https://github.com/chrislemonier
- Derjus: https://github.com/derjus
- Eauland: https://github.com/eauland
- Félix: https://github.com/dysosmus
- Horsely: https://github.com/horsley
- Ing. Jan Kaláb: https://github.com/Pitel
- James Scott-Brown: https://github.com/jamesscottbrown
- Luca Marra: https://github.com/facciocose
- Maxime: https://github.com/EpocDotFr
- MonsieurPaulLeBoulanger: https://github.com/MonsieurPaulLeBoulanger
- Necku: https://github.com/Necku
- Thiriot Christophe: https://github.com/doubleface
- Ygbillet: https://github.com/ygbillet

PS: Many people sent a bug report too (see [issues tracker](https://github.com/fguillot/miniflux/issues))

Roadmap
-------

- http://miniflux.net/roadmap.html

ChangeLog
---------

- http://miniflux.net/changes.html

Requirements
------------

- Recent version of libxml2 >= 2.7.x (version 2.6.32 on Debian Lenny are not supported anymore)
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
2. Download the source code and copy the directory `miniflux` where you want
3. Check if the directory `data` is writeable (Miniflux stores everything inside a Sqlite database)
4. With your browser go to <http://yourpersonalserver/miniflux>
5. The default login and password is **admin/admin**
6. Start to use the software

FAQ
----

### How do I update my feeds with a cronjob?

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

### How does Miniflux update my feeds from the user interface?

Miniflux uses an Ajax request to refresh each subscription.
By default, there is only 5 feeds updated in parallel.

### I have 600 subscriptions, can Miniflux handle that?

Your life is cluttered.

### Why are there no categories? Why is feature X missing?

Miniflux is a minimalist software. Less is more.

### I found a bug, what next?

Report the bug to the [issues tracker](https://github.com/fguillot/miniflux/issues) and I will fix it.

You can report feeds that doesn't works properly too.

### Which browser is compatible with Miniflux?

Miniflux is tested with the latest versions of Mozilla Firefox, Google Chrome and Safari.

I don't use Microsoft products, then I have no idea if Miniflux works correctly with Internet Explorer.

### How to override application variables?

There is few settings that can't be changed by the user interface.
These parameters are defined with PHP constants.

To override them, create a `config.php` file at the root of the project and change yourself the values.

By example, to override the default HTTP timeout value:

    # file config.php

    <?php

    // My specific HTTP timeout (5 seconds)
    define('HTTP_TIMEOUT', 5);

Actually, the following constants can be overrided:

- `HTTP_TIMEOUT` => default value is 10 seconds
- `APP_VERSION` => default value is master
- `DB_FILENAME` => default value is `data/db.sqlite`
- `DEBUG` => default is false (enable logs dump of picoFeed)
- `DEBUG_DIRECTORY` => default is /tmp (place to store log files)
- `THEME_DIRECTORY` => default is themes

### How to create a theme for Miniflux?

It's very easy to write a custom theme for Miniflux.

A theme is just a CSS file, images and fonts.
A theme doesn't change the behaviour of the application but only the page design.

The first step is to create a new directory structure for your theme:

    mkdir -p themes/mysuperskin/{css,img,fonts}

The name of your theme should be only alphanumeric.
There is the following directories inside your theme:

- `css`: Your stylesheet, the file must be named `app.css` (required)
- `img`: Theme images (not required)
- `fonts`: Theme fonts (not required)

For a very basic theme example, have a look to the directory `examples\mytheme`.

Miniflux use responsive design, so it's better if your theme can handle mobile devices.

If you write a very cool theme for Miniflux, **send me your code to be available in the default installation!**
It would be awesome for everybody :)

### List of themes:

- Original theme By Frederic Guillot
- Midnight By Luca Marra
- Green by Maxime (aka EpocDotFr)

### Coding standards for contributors

- Line indentation: 4 spaces
- Line endings: Unix
- File encoding: UTF-8
