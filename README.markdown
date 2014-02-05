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
- Use secure headers (only external images and Youtube/Vimeo/Dailymotion videos are allowed)
- Open external links inside a new tab with a `rel="noreferrer"` attribute
- Mobile CSS (responsive design)
- Keyboard shortcuts (pressing '?' displays a pop-up listing the shortcuts; pressing 'q' closes it)
- Basic bookmarks
- Translated in English, French, German, Italian, Czech, Spanish, Portuguese and Simplified Chinese
- Themes support
- Alternative login with a Google Account or Mozilla Persona
- **Full article download for feeds that display only a summary** (website scraper based on Xpath rules)

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

People who sent a pull-request, report a bug, make a new theme or share a super cool idea:

- André Kelpe: https://github.com/fs111
- Ayodio: https://github.com/ayodio
- Bjauy: https://github.com/bjauy
- Bohwaz: https://github.com/bohwaz
- Chase Arnold: https://github.com/chase4926
- Chris Lemonier: https://github.com/chrislemonier
- Delehef: https://github.com/delehef
- Derjus: https://github.com/derjus
- Eauland: https://github.com/eauland
- Félix: https://github.com/dysosmus
- Geriel Castro: https://github.com/GerielCastro
- Horsely: https://github.com/horsley
- Ing. Jan Kaláb: https://github.com/Pitel
- Itoine: https://github.com/itoine
- James Scott-Brown: https://github.com/jamesscottbrown
- Luca Marra: https://github.com/facciocose
- Maxime: https://github.com/EpocDotFr
- MonsieurPaulLeBoulanger: https://github.com/MonsieurPaulLeBoulanger
- Necku: https://github.com/Necku
- Nicolas Dewaele: http://adminrezo.fr/
- Silvus: https://github.com/Silvus
- Skasi7: https://github.com/skasi7
- Thiriot Christophe: https://github.com/doubleface
- Vincent Ozanam
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

- Recent version of libxml2 >= 2.7.x (version 2.6.32 on Debian Lenny is not supported anymore)
- PHP >= 5.3.7
- PHP XML extensions (SimpleXML, DOM...)
- PHP Sqlite extension
- cURL extension for PHP or stream context with (`allow_url_fopen=On`)
- Short tags enabled for PHP < 5.4

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

From the archive:

1. You must have a web server with PHP installed (version 5.3.7 minimum) with the Sqlite and XML extensions
2. Download the source code and copy the directory `miniflux` where you want
3. Check if the directory `data` is writeable (Miniflux stores everything inside a Sqlite database)
4. With your browser go to <http://yourpersonalserver/miniflux>
5. The default login and password is **admin/admin**
6. Start to use the software
7. Don't forget to change your password!

From the repository:

1. `git clone https://github.com/fguillot/miniflux.git`
2. Go to the third step just above

Update
------

From the archive:

1. Close your session (logout)
2. Rename your actual miniflux directory (to keep a backup)
3. Uncompress the new archive and copy your database file `db.sqlite` in the directory `data`
4. Make the directory `data` writeable by the web server user
5. Login and check if everything is ok
6. Remove the old miniflux directory

From the repository:

1. Close your session (logout)
2. `git pull`
3. Login and check if everything is ok

Security
--------

- Don't forget to change the default user/password
- Don't allow everybody to access to the directory `data` from the URL. There is already a `.htaccess` for Apache but nothing for Nginx.

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

I don't use Microsoft products, and as such I have no idea if Miniflux works correctly with Internet Explorer.

### How do I override application variables?

There are few settings that can't be changed by the user interface.
These parameters are defined with PHP constants.

To override them, create a `config.php` file at the root of the project and change the values.

By example, to override the default HTTP timeout value:

    <?php

    // My specific HTTP timeout (5 seconds)
    define('HTTP_TIMEOUT', 5);

PS: This file must be a PHP file (nothing before the open tag `<?php`).

Actually, the following constants can be overrided:

- `HTTP_TIMEOUT` => default value is 10 seconds
- `APP_VERSION` => default value is master
- `DB_FILENAME` => default value is `data/db.sqlite`
- `DEBUG` => default is true (enable logging of PicoFeed)
- `DEBUG_FILENAME` => default is `data/debug.log`
- `THEME_DIRECTORY` => default is themes
- `SESSION_SAVE_PATH` => default is empty (used to store session files in a custom directory)
- `PROXY_HOSTNAME` => default is empty (make HTTP requests through a HTTP proxy if set)
- `PROXY_PORT` => default is 3128 (default port of Squid)
- `PROXY_USERNAME` => default is empty (set the proxy username is needed)
- `PROXY_PASSWORD` => default is empty

### How to change the session save path?

With several shared hosting providers, sessions are cleaned frequently, to avoid to login too often,
you can save sessions in a custom directory.

- Create a directory, by example `sessions`
- This directory must be writeable by the web server user
- This directory must NOT be accessible from the outside world (add a `.htaccess` if necessary)
- Override the application variable like described above: `define('SESSION_SAVE_PATH', 'sessions');`
- Now, your sessions are saved in the directory `sessions`

### How to override/extends the content filtering blacklist/whitelist?

Miniflux use [PicoFeed](https://github.com/fguillot/picoFeed) to parse the content of each item.
These variables are public static arrays, extends the actual array or replace it.

**Be careful, you can break everything by doing that!!!**

Put your modifications in your custom `config.php` like described above.

By example to add a new iframe whitelist:

    \PicoFeed\Filter::$iframe_whitelist[] = 'http://www.kickstarter.com';

Or to replace the entire whitelist:

    \PicoFeed\Filter::$iframe_whitelist = array('http://www.kickstarter.com');

Available variables:

    // Allow only specified tags and attributes
    \PicoFeed\Filter::$whitelist_tags

    // Strip content of these tags
    \PicoFeed\Filter::$blacklist_tags

    // Allow only specified URI scheme
    \PicoFeed\Filter::$whitelist_scheme

    // List of attributes used for external resources: src and href
    \PicoFeed\Filter::$media_attributes

    // Blacklist of external resources
    \PicoFeed\Filter::$media_blacklist

    // Required attributes for tags, if the attribute is missing the tag is dropped
    \PicoFeed\Filter::$required_attributes

    // Add attribute to specified tags
    \PicoFeed\Filter::$add_attributes

    // Attributes that must be integer
    \PicoFeed\Filter::$integer_attributes

    // Iframe allowed source
    \PicoFeed\Filter::$iframe_whitelist

For more details, have a look to the class `vendor/PicoFeed/Filter.php`.

### Where is the API documentation?

<http://miniflux.net/api.html>

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
- Bootstrap 3 (Light) By Silvus
- Bootswatch Cyborg By Silvus

### How to create or update a translation?

- Translations are stored inside the directory `locales`
- There is sub-directory for each language, by example for french we have `fr_FR`, for italian `it_IT` etc...
- A translation is a PHP file that return an Array with a key-value pairs
- The key is the original text in english and the value is the translation for the corresponding language

French translations are always the most recent (because I am french).

Create a new translation:

1. Make a new directory: `locales/xx_XX` by example `locales/fr_CA` for French Canadian
2. Create a new file for the translation: `locales/xx_XX/translations.php`
3. Use the content of the french locales to have the most recent keys and replace the values
4. Inside the file `model.php`, add a new entry for your translation in the function `get_languages()`
5. Check with your local installation of Miniflux if everything is ok
6. Send a pull-request with Github

### Coding standards for contributors

- Line indentation: 4 spaces
- Line endings: Unix
- File encoding: UTF-8

### How the content grabber works?

1. Try with rules first (xpath patterns) for the domain name (see `PicoFeed\Rules\`)
2. Try to find the text content by using common attributes for class and id
3. Fallback to Readability if no content is found
4. Finally, if nothing is found, the feed content is displayed

The content downloader use a fake user agent, actually Google Chrome under Mac Os X.

However the content grabber doesn't work very well with all websites.
**The best results are obtained with Xpath rules file.**

There is a PHP script inside PicoFeed to import Fivefilters rules, but I dont' use it because almost of these patterns are not up to date.

### How to write a grabber rules file?

Add a PHP file to the directory `PicoFeed\Rules`, the filename must be the domain name:

Example with the BBC website, `www.bbc.co.uk.php`:

    <?php
    return array(
        'test_url' => 'http://www.bbc.co.uk/news/world-middle-east-23911833',
        'body' => array(
            '//div[@class="story-body"]',
        ),
        'strip' => array(
            '//script',
            '//form',
            '//style',
            '//*[@class="story-date"]',
            '//*[@class="story-header"]',
            '//*[@class="story-related"]',
            '//*[contains(@class, "byline")]',
            '//*[contains(@class, "story-feature")]',
            '//*[@id="video-carousel-container"]',
            '//*[@id="also-related-links"]',
            '//*[contains(@class, "share") or contains(@class, "hidden") or contains(@class, "hyper")]',
        )
    );

Actually, only `body`, `strip` and `test_url` are supported.

Don't forget to send a pull request or a ticket to share your contribution with everybody,

### List of content grabber rules

**If you want to add new rules, just open a ticket and I will do it.**

- *.blog.lemonde.fr
- *.blog.nytimes.com
- *.nytimes.com
- *.phoronix.com
- *.slate.com
- *.theguardian.com
- *.wikipedia.org
- *.wired.com
- *.wsj.com
- github.com
- golem.de
- ing.dk
- karriere.jobfinder.dk
- lesjoiesducode.fr
- lifehacker.com
- lists.*
- medium.com
- pastebin.com
- plus.google.com
- rue89.com
- smallhousebliss.com
- spiegel.de
- techcrunch.com
- version2.dk
- www.bbc.co.uk
- www.businessweek.com
- www.cnn.com
- www.egscomics.com
- www.forbes.com
- www.lemonde.fr
- www.lepoint.fr
- www.npr.org
- www.numerama.com
- www.slate.fr
