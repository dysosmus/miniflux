(function() {

    // List of subscriptions
    var feeds = [];

    // List of feeds currently updating
    var queue = [];

    // Number of concurrent requests when updating all feeds
    var queue_length = 5;

    // Keyboard shortcuts queue
    var keyqueue = [];

    // Download full content from the original website
    function download_item()
    {
        var container = document.getElementById("download-item");
        if (! container) return;

        var item_id = container.getAttribute("data-item-id");
        var message = container.getAttribute("data-before-message");

        var img = document.createElement("img");
        img.src = "assets/img/refresh.gif";

        container.innerHTML = "";
        container.className = "downloading";
        container.appendChild(img);
        container.appendChild(document.createTextNode(" " + message));

        var request = new XMLHttpRequest();

        request.onload = function() {

            var response = JSON.parse(request.responseText);

            if (response.result) {

                var content = document.getElementById("item-content");
                if (content) content.innerHTML = response.content;

                if (container) {

                    var message = container.getAttribute("data-after-message");

                    container.innerHTML = "";
                    container.appendChild(document.createTextNode(" " + message));
                }
            }
            else {

                if (container) {

                    var message = container.getAttribute("data-failure-message");

                    container.innerHTML = "";
                    container.appendChild(document.createTextNode(" " + message));
                }
            }
        };

        request.open("POST", "?action=download-item&id=" + item_id, true);
        request.send();
    }

    // Flip item status between unread and read
    function switch_status(item_id, hide)
    {
        var request = new XMLHttpRequest();

        request.onload = function() {

            if (is_listing()) {

                var response = JSON.parse(request.responseText);

                if (response.status == "read" || response.status == "unread") {

                    find_next_item();

                    if (hide) {
                        remove_item(response.item_id);
                    }
                    else if (response.status == "read") {
                        show_item_as_read(item_id);
                    }
                    else if (response.status == "unread") {
                        show_item_as_unread(item_id);
                    }
                }
            }
        }

        request.open("POST", "?action=change-item-status&id=" + item_id, true);
        request.send();
    }

    // Set all items of the current page to the status read and redirect to the main page
    function mark_items_as_read(redirect)
    {
        var articles = document.getElementsByTagName("article");
        var idlist = [];

        for (var i = 0, ilen = articles.length; i < ilen; i++) {
            idlist.push(articles[i].getAttribute("data-item-id"));
        }

        var request = new XMLHttpRequest();

        request.onload = function() {
            window.location.href = redirect;
        };

        request.open("POST", "?action=mark-items-as-read", true);
        request.send(JSON.stringify(idlist));
    }

    // Mark the current item read and hide this item
    function mark_as_read(item_id)
    {
        var request = new XMLHttpRequest();

        request.onload = function() {
            remove_item(item_id);
        };

        request.open("POST", "?action=mark-item-read&id=" + item_id, true);
        request.send();
    }

    // Set the current item unread and hide this item
    function mark_as_unread(item_id)
    {
        var request = new XMLHttpRequest();

        request.onload = function() {
            remove_item(item_id);
        };

        request.open("POST", "?action=mark-item-unread&id=" + item_id, true);
        request.send();
    }

    // Bookmark the selected item
    function bookmark_item()
    {
        var item = document.getElementById("current-item");

        if (item) {

            var item_id = item.getAttribute("data-item-id");
            var link = document.getElementById("bookmark-" + item_id);

            if (link) link.click();
        }
    }

    // Show an item as read (change title color and add icon)
    function show_item_as_read(item_id)
    {
        var link = document.getElementById("open-" + item_id);

        if (link) {
            link.className = "read";

            var icon = document.createElement("span");
            icon.id = "read-icon-" + item_id;
            icon.appendChild(document.createTextNode("☑ "));
            link.parentNode.insertBefore(icon, link);
        }
    }

    // Show an item as unread (change title color and remove read icon)
    function show_item_as_unread(item_id)
    {
        var link = document.getElementById("open-" + item_id);
        if (link) link.className = "";

        var icon = document.getElementById("read-icon-" + item_id);
        if (icon) icon.parentNode.removeChild(icon);
    }

    // Show the refresh icon when updating a feed
    function show_refresh_icon(feed_id)
    {
        var container = document.getElementById("loading-feed-" + feed_id);

        if (container) {

            var img = document.createElement("img");
            img.src = "assets/img/refresh.gif";

            container.appendChild(img);
        }
    }

    // Hide the refresh icon after update
    function hide_refresh_icon(feed_id)
    {
        var container = document.getElementById("loading-feed-" + feed_id);
        if (container) container.innerHTML = "";

        var container = document.getElementById("last-checked-feed-" + feed_id);
        if (container) container.innerHTML = container.getAttribute("data-after-update");
    }

    // Update one feed in the background and execute a callback after that
    function refresh_feed(feed_id, callback)
    {
        if (! feed_id) return false;

        show_refresh_icon(feed_id);

        var request = new XMLHttpRequest();

        request.onload = function() {

            hide_refresh_icon(feed_id);

            try {
                if (callback) {
                    callback(JSON.parse(this.responseText));
                }
            }
            catch (e) {}
        };

        request.open("POST", "?action=refresh-feed&feed_id=" + feed_id, true);
        request.send();

        return true;
    }

    // Get all subscriptions from the feeds page
    function get_feeds()
    {
        var links = document.getElementsByTagName("a");

        for (var i = 0, ilen = links.length; i < ilen; i++) {
            var feed_id = links[i].getAttribute('data-feed-id');
            if (feed_id) feeds.push(parseInt(feed_id));
        }
    }

    // Refresh all feeds (use a queue to allow 5 concurrent feed updates)
    function refresh_all()
    {
        get_feeds();

        var interval = setInterval(function() {

            while (feeds.length > 0 && queue.length < queue_length) {

                var feed_id = feeds.shift();
                queue.push(feed_id);

                refresh_feed(feed_id, function(response) {

                    var index = queue.indexOf(response.feed_id);
                    if (index >= 0) queue.splice(index, 1);

                    if (feeds.length == 0 && queue.length == 0) {
                        clearInterval(interval);
                        window.location.href = "?action=unread";
                    }
                });
            }

        }, 100);
    }

    // Go the next page
    function open_next_page()
    {
        var link = document.getElementById("next-page");
        if (link) link.click();
    }

    // Go to the previous page
    function open_previous_page()
    {
        var link = document.getElementById("previous-page");
        if (link) link.click();
    }

    // Hide one item and update the item counter on the top
    function remove_item(item_id)
    {
        var item = document.getElementById("item-" + item_id);

        if (! item) {
            item = document.getElementById("current-item");
            if (item.getAttribute("data-item-id") != item_id) item = false;
        }

        if (item && item.getAttribute("data-hide")) {

            item.parentNode.removeChild(item);
            var container = document.getElementById("page-counter");

            if (container) {

                counter = parseInt(container.textContent.trim(), 10) - 1;

                if (counter == 0) {

                    window.location = "?action=feeds&nothing_to_read=1";
                }
                else {

                    container.textContent = counter + " ";
                    document.title = "miniflux (" + counter + ")";
                    document.getElementById("nav-counter").textContent = "(" + counter + ")";
                }
            }
        }
    }

    // Open the original url inside a new tab
    function open_original_item()
    {
        var link = document.getElementById("original-item");

        if (link) {

            if (is_listing() && link.getAttribute("data-hide")) {
                mark_as_read(link.getAttribute("data-item-id"));
            }

            link.removeAttribute("data-action");
            link.click();
        }
    }

    // Show item content
    function open_item()
    {
        var link = document.getElementById("open-item");
        if (link) link.click();
    }

    // Show the next item
    function open_next_item()
    {
        var link = document.getElementById("next-item");

        if (link) {

            link.click();
        }
        else if (is_listing()) {

            find_next_item();
        }
    }

    // Show the previous item
    function open_previous_item()
    {
        var link = document.getElementById("previous-item");

        if (link) {

            link.click();
        }
        else if (is_listing()) {

            find_previous_item();
        }
    }

    // Change item status and select the next item in the list
    function change_item_status()
    {
        if (is_listing() && ! document.getElementById("current-item")) {
            find_next_item();
        }

        var item = document.getElementById("current-item");

        if (item) {
            switch_status(item.getAttribute("data-item-id"), item.getAttribute("data-hide"));
        }
    }

    // Scroll automatically the page when using keyboard shortcuts
    function scroll_page_to(item)
    {
        var clientHeight = pageYOffset + document.documentElement.clientHeight;
        var itemPosition = item.offsetTop + item.offsetHeight;

        if (clientHeight - itemPosition < 0 || clientHeight - item.offsetTop > document.documentElement.clientHeight) {
            window.scrollTo(0, item.offsetTop - 10);
        }
    }

    // Prepare the DOM for the selected item
    function set_links_item(item_id)
    {
        var link = document.getElementById("current-item");
        if (link) scroll_page_to(link);

        var link = document.getElementById("original-item");
        if (link) link.id = "original-" + link.getAttribute("data-item-id");

        var link = document.getElementById("open-item");
        if (link) link.id = "open-" + link.getAttribute("data-item-id");

        var link = document.getElementById("original-" + item_id);
        if (link) link.id = "original-item";

        var link = document.getElementById("open-" + item_id);
        if (link) link.id = "open-item";
    }

    // Find the next item in the listing page
    function find_next_item()
    {
        var items = document.getElementsByTagName("article");

        if (! document.getElementById("current-item")) {

            items[0].id = "current-item";
            set_links_item(items[0].getAttribute("data-item-id"));
        }
        else {

            for (var i = 0, ilen = items.length; i < ilen; i++) {

                if (items[i].id == "current-item") {

                    items[i].id = "item-" + items[i].getAttribute("data-item-id");

                    if (i + 1 < ilen) {

                        items[i + 1].id = "current-item";
                        set_links_item(items[i + 1].getAttribute("data-item-id"));
                    }

                    break;
                }
            }
        }
    }

    // Find the previous item in the listing page
    function find_previous_item()
    {
        var items = document.getElementsByTagName("article");

        if (! document.getElementById("current-item")) {

            items[items.length - 1].id = "current-item";
            set_links_item(items[items.length - 1].getAttribute("data-item-id"));
        }
        else {

            for (var i = items.length - 1; i >= 0; i--) {

                if (items[i].id == "current-item") {

                    items[i].id = "item-" + items[i].getAttribute("data-item-id");

                    if (i - 1 >= 0) {

                        items[i - 1].id = "current-item";
                        set_links_item(items[i - 1].getAttribute("data-item-id"));
                    }

                    break;
                }
            }
        }
    }

    // Check if we are on a listing page
    function is_listing()
    {
        if (document.getElementById("listing")) return true;
        return false;
    }

    // Authentication with Mozilla Persona
    function mozilla_auth(action)
    {
        navigator.id.watch({
            onlogin: function(assertion) {

                var xhr = new XMLHttpRequest();
                xhr.open("POST", "?action=" + action, true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.setRequestHeader("Connection", "close");

                xhr.onload = function () {
                    window.location.href = this.responseText;
                };

                xhr.send("token=" + assertion);
            },
            onlogout: function() {}
        });

        navigator.id.request();
    }

    // Click event handler, if there is a "data-action" attribute execute the corresponding callback
    document.onclick = function(e) {

        var action = e.target.getAttribute("data-action");

        if (action) {

            switch (action) {
                case 'refresh-all':
                    e.preventDefault();
                    refresh_all();
                    break;
                case 'refresh-feed':
                    e.preventDefault();
                    var feed_id = e.target.getAttribute("data-feed-id");
                    refresh_feed(feed_id);
                    break;
                case 'mark-read':
                    e.preventDefault();
                    var item_id = e.target.getAttribute("data-item-id");
                    mark_as_read(item_id);
                    break;
                case 'mark-unread':
                    e.preventDefault();
                    var item_id = e.target.getAttribute("data-item-id");
                    mark_as_unread(item_id);
                    break;
                case 'mark-all-read':
                    e.preventDefault();
                    mark_items_as_read("?action=unread");
                    break;
                case 'mark-feed-read':
                    e.preventDefault();
                    mark_items_as_read("?action=feed-items&feed_id=" + e.target.getAttribute("data-feed-id"));
                    break;
                case 'original-link':
                    var item_id = e.target.getAttribute("data-item-id");
                    mark_as_read(item_id);
                    break;
                case 'download-item':
                    e.preventDefault();
                    download_item();
                    break;
                case 'mozilla-login':
                    e.preventDefault();
                    mozilla_auth("mozilla-auth");
                    break;
                case 'mozilla-link':
                    e.preventDefault();
                    mozilla_auth("mozilla-link");
                    break;
            }
        }
    };

    // Keyboard handler, handle keyboard shortcuts
    document.onkeypress = function(e) {

        keyqueue.push(e.keyCode || e.which);

        if (keyqueue[0] == 103) { // g

            switch (keyqueue[1]) {
                case undefined:
                    break;
                case 117: // u
                    window.location.href = "?action=unread";
                    keyqueue = [];
                    break;
                case 98: // b
                    window.location.href = "?action=bookmarks";
                    keyqueue = [];
                    break;
                case 104: // h
                    window.location.href = "?action=history";
                    keyqueue = [];
                    break;
                case 115: // s
                    window.location.href = "?action=feeds";
                    keyqueue = [];
                    break;
                case 112: // p
                    window.location.href = "?action=config";
                    keyqueue = [];
                    break;
                default:
                    keyqueue = [];
                    break;
            }
        }
        else {

            keyqueue = [];

            switch (e.keyCode || e.which) {
                case 100: // d
                    download_item();
                    break;
                case 112: // p
                case 107: // k
                    open_previous_item();
                    break;
                case 110: // n
                case 106: // j
                    open_next_item();
                    break;
                case 118: // v
                    open_original_item();
                    break;
                case 111: // o
                    open_item();
                    break;
                case 109: // m
                    change_item_status();
                    break;
                case 102: // f
                    bookmark_item();
                    break;
                case 104: // h
                    open_previous_page();
                    break
                case 108: // l
                    open_next_page();
                    break;
                case 63: // ?
                    open("?action=show-help", "Help", "width=320,height=450,location=no,scrollbars=no,status=no,toolbar=no");
                    break;
            }
        }
    };

})();
