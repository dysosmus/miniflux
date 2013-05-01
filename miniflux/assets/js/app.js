(function() {

    var feeds = [];
    var queue = [];
    var queue_length = 5;


    function switch_status(item_id)
    {
        var request = new XMLHttpRequest();

        request.onreadystatechange = function() {

            if (request.readyState === 4 && is_listing()) {

                var response = JSON.parse(request.responseText);

                if (response.status == "read" || response.status == "unread") {

                    find_next_item();
                    remove_item(response.item_id);
                }
            }
        }

        request.open("POST", "?action=change-item-status&id=" + item_id, true);
        request.send();
    }


    function mark_as_read(item_id)
    {
        var request = new XMLHttpRequest();

        request.onload = function() {

            remove_item(item_id);
        };

        request.open("POST", "?action=mark-item-read&id=" + item_id, true);
        request.send();
    }


    function mark_as_unread(item_id)
    {
        var request = new XMLHttpRequest();

        request.open("POST", "?action=mark-item-unread&id=" + item_id, true);
        request.send();
    }


    function show_refresh_icon(feed_id)
    {
        var container = document.getElementById("loading-feed-" + feed_id);

        if (container) {

            var img = document.createElement("img");
            img.src = "./assets/img/refresh.gif";

            container.appendChild(img);
        }
    }


    function hide_refresh_icon(feed_id)
    {
        var container = document.getElementById("loading-feed-" + feed_id);

        if (container) {

            container.innerHTML = "";
        }
    }


    function refresh_feed(feed_id, callback)
    {
        if (! feed_id) {

            return false;
        }

        show_refresh_icon(feed_id);

        var request = new XMLHttpRequest();

        request.onreadystatechange = function() {

            if (request.readyState === 4) {

                hide_refresh_icon(feed_id);

                try {

                    var response = JSON.parse(this.responseText);

                    if (callback) {

                        callback(response);
                    }

                    if (! response.result) {

                        //window.alert('Unable to refresh this feed: ' + feed_id);
                    }
                }
                catch (e) {}
            }
        };

        request.open("POST", "?action=refresh-feed&feed_id=" + feed_id, true);
        request.send();

        return true;
    }


    function get_feeds()
    {
        var links = document.getElementsByTagName("a");

        for (var i = 0, ilen = links.length; i < ilen; i++) {

            var feed_id = links[i].getAttribute('data-feed-id');

            if (feed_id) {

                feeds.push(parseInt(feed_id));
            }
        }
    }


    function refresh_all()
    {
        get_feeds();

        var interval = setInterval(function() {

            while (feeds.length > 0 && queue.length < queue_length) {

                var feed_id = feeds.shift();

                queue.push(feed_id);

                refresh_feed(feed_id, function(response) {

                    var index = queue.indexOf(response.feed_id);

                    if (index >= 0) {

                        queue.splice(index, 1);
                    }

                    if (feeds.length == 0 && queue.length == 0) {

                        clearInterval(interval);
                        window.location.href = "?action=unread";
                    }
                });
            }

        }, 100);
    }


    function remove_item(item_id)
    {
        var item = document.getElementById("item-" + item_id);
        if (item) item.parentNode.removeChild(item);
    }


    function open_original_item()
    {
        var link = document.getElementById("original-item");

        if (link) {

            if (link.getAttribute("data-action") == "mark-read") {

                mark_as_read(link.getAttribute("data-item-id"));
                find_next_item();
            }

            link.click();
        }
    }


    function open_item()
    {
        var link = document.getElementById("open-item");
        if (link) link.click();
    }


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


    function change_item_status()
    {
        var item = document.getElementById("current-item");
        if (item) switch_status(item.getAttribute("data-item-id"));
    }


    function set_links_item(item_id)
    {
        var link = document.getElementById("original-item");
        if (link) link.id = "original-" + link.getAttribute("data-item-id");

        var link = document.getElementById("open-item");
        if (link) link.id = "open-" + link.getAttribute("data-item-id");

        var link = document.getElementById("original-" + item_id);
        if (link) link.id = "original-item";

        var link = document.getElementById("open-" + item_id);
        if (link) link.id = "open-item";
    }


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


    function is_listing()
    {
        if (document.getElementById("listing")) {

            return true;
        }

        return false;
    }


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
                    var item_id = e.target.getAttribute("data-item-id");
                    mark_as_read(item_id);
                    break;
            }
        }
    };

    document.onkeypress = function(e) {

        switch (e.keyCode || e.which) {
            case 112:
                open_previous_item();
                break;
            case 110:
                open_next_item();
                break;
            case 118:
                open_original_item();
                break;
            case 111:
                open_item();
                break;
            case 109:
                change_item_status();
                break;
        }
    };

})();