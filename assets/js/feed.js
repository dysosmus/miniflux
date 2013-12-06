Miniflux.Feed = (function() {

    // List of subscriptions
    var feeds = [];

    // List of feeds currently updating
    var queue = [];

    // Number of concurrent requests when updating all feeds
    var queue_length = 5;

    // Show the refresh icon when updating a feed
    function showRefreshIcon(feed_id)
    {
        var container = document.getElementById("loading-feed-" + feed_id);

        if (container) {
            var img = document.createElement("img");
            img.src = "assets/img/refresh.gif";
            container.appendChild(img);
        }
    }

    // Hide the refresh icon after update
    function hideRefreshIcon(feed_id)
    {
        var container = document.getElementById("loading-feed-" + feed_id);
        if (container) container.innerHTML = "";

        var container = document.getElementById("last-checked-feed-" + feed_id);
        if (container) container.innerHTML = container.getAttribute("data-after-update");
    }

    // Get all subscriptions from the feeds page
    function loadFeeds()
    {
        var links = document.getElementsByTagName("a");

        for (var i = 0, ilen = links.length; i < ilen; i++) {
            var feed_id = links[i].getAttribute('data-feed-id');
            if (feed_id) feeds.push(parseInt(feed_id));
        }
    }

    return {
        Update: function(feed_id, callback) {

            showRefreshIcon(feed_id);

            var request = new XMLHttpRequest();

            request.onload = function() {

                hideRefreshIcon(feed_id);

                try {
                    if (callback) {
                        callback(JSON.parse(this.responseText));
                    }
                }
                catch (e) {}
            };

            request.open("POST", "?action=refresh-feed&feed_id=" + feed_id, true);
            request.send();
        },
        UpdateAll: function() {

            loadFeeds();

            var interval = setInterval(function() {

                while (feeds.length > 0 && queue.length < queue_length) {

                    var feed_id = feeds.shift();
                    queue.push(feed_id);

                    Miniflux.Feed.Update(feed_id, function(response) {

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
    };
})();