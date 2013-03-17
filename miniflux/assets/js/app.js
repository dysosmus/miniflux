(function() {

    var feeds = [];
    var queue = [];
    var queue_length = 5;


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

        request.onload = function() {

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
        };

        request.open("GET", "?action=ajax-refresh-feed&feed_id=" + feed_id, true);
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


    document.onclick = function(e) {

        var action = e.target.getAttribute("data-action");

        if (action) {

            e.preventDefault();

            switch (action) {
                case 'refresh-all':
                    refresh_all();
                    break;
                case 'refresh-feed':
                    var feed_id = e.target.getAttribute("data-feed-id");
                    refresh_feed(feed_id);
                    break;
            }
        }
    };

})();