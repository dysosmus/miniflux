Miniflux.Item = (function() {

    function getItem(item_id)
    {
        var item = document.getElementById("item-" + item_id);

        if (! item) {
            item = document.getElementById("current-item");
            if (item.getAttribute("data-item-id") != item_id) item = false;
        }

        return item;
    }

    function changeBookmarkLabel(item_id)
    {
        var link = document.getElementById("bookmark-" + item_id);

        if (link && link.getAttribute("data-reverse-label")) {
            var content = link.innerHTML;
            link.innerHTML = link.getAttribute("data-reverse-label");
            link.setAttribute("data-reverse-label", content);
        }
    }

    function showItemBookmarked(item_id, item)
    {
        if (! Miniflux.Nav.IsListing()) {

            var link = document.getElementById("bookmark-" + item_id);
            if (link) link.innerHTML = "★";
        }
        else {

            var link = document.getElementById("show-" + item_id);

            if (link) {
                var icon = document.createElement("span");
                icon.id = "bookmark-icon-" + item_id;
                icon.appendChild(document.createTextNode("★ "));
                link.parentNode.insertBefore(icon, link);
            }

            changeBookmarkLabel(item_id);
        }
    }

    function hideItemBookmarked(item_id, item)
    {
        if (! Miniflux.Nav.IsListing()) {

            var link = document.getElementById("bookmark-" + item_id);
            if (link) link.innerHTML = "☆";
        }
        else {

            var icon = document.getElementById("bookmark-icon-" + item_id);
            if (icon) icon.parentNode.removeChild(icon);

            changeBookmarkLabel(item_id);
        }
    }

    function changeStatusLabel(item_id)
    {
        var link = document.getElementById("status-" + item_id);

        if (link) {
            var content = link.innerHTML;
            link.innerHTML = link.getAttribute("data-reverse-label");
            link.setAttribute("data-reverse-label", content);
        }
    }

    function showItemAsRead(item_id)
    {
        var item = getItem(item_id);

        if (item) {
            if (item.getAttribute("data-hide")) {
                hideItem(item);
            }
            else {

                item.setAttribute("data-item-status", "read");
                changeStatusLabel(item_id);

                // Show icon
                var link = document.getElementById("show-" + item_id);

                if (link) {
                    link.className = "read";

                    var icon = document.createElement("span");
                    icon.id = "read-icon-" + item_id;
                    icon.appendChild(document.createTextNode("☑ "));
                    link.parentNode.insertBefore(icon, link);
                }

                // Change action
                link = document.getElementById("status-" + item_id);
                if (link) link.setAttribute("data-action", "mark-unread");
            }
        }
    }

    function showItemAsUnread(item_id)
    {
        var item = getItem(item_id);

        if (item) {
            if (item.getAttribute("data-hide")) {
                hideItem(item);
            }
            else {

                item.setAttribute("data-item-status", "unread");
                changeStatusLabel(item_id);

                // Remove icon
                var link = document.getElementById("show-" + item_id);
                if (link) link.className = "";

                var icon = document.getElementById("read-icon-" + item_id);
                if (icon) icon.parentNode.removeChild(icon);

                // Change action
                link = document.getElementById("status-" + item_id);
                if (link) link.setAttribute("data-action", "mark-read");
            }
        }
    }

    function hideItem(item)
    {
        Miniflux.Nav.SelectNextItem();

        item.parentNode.removeChild(item);
        var container = document.getElementById("page-counter");

        if (container) {

            counter = parseInt(container.textContent.trim(), 10) - 1;

            if (counter == 0) {
                window.location = "?action=unread";
            }
            else {
                container.textContent = counter + " ";
                document.title = "miniflux (" + counter + ")";
                document.getElementById("nav-counter").textContent = "(" + counter + ")";
            }
        }
    }

    function markAsRead(item_id)
    {
        var request = new XMLHttpRequest();
        request.onload = function() {
            if (Miniflux.Nav.IsListing()) showItemAsRead(item_id);
        };
        request.open("POST", "?action=mark-item-read&id=" + item_id, true);
        request.send();
    }

    function markAsUnread(item_id)
    {
        var request = new XMLHttpRequest();
        request.onload = function() {
            if (Miniflux.Nav.IsListing()) showItemAsUnread(item_id);
        };
        request.open("POST", "?action=mark-item-unread&id=" + item_id, true);
        request.send();
    }

    function bookmark(item, value)
    {
        var item_id = item.getAttribute("data-item-id");
        var request = new XMLHttpRequest();

        request.onload = function() {

            item.setAttribute("data-item-bookmark", value);

            if (value) {
                showItemBookmarked(item_id, item);
            }
            else {
                hideItemBookmarked(item_id, item);
            }
        };

        request.open("POST", "?action=bookmark&id=" + item_id + "&value=" + value, true);
        request.send();
    }

    return {
        Get: getItem,
        MarkAsRead: markAsRead,
        MarkAsUnread: markAsUnread,
        SwitchBookmark: function(item) {

            var bookmarked = item.getAttribute("data-item-bookmark");

            if (bookmarked == "1") {
                bookmark(item, 0);
            }
            else {
                bookmark(item, 1);
            }
        },
        SwitchStatus: function(item) {

            var item_id = item.getAttribute("data-item-id");
            var status = item.getAttribute("data-item-status");

            if (status == "read") {
                markAsUnread(item_id);
            }
            else if (status == "unread") {
                markAsRead(item_id);
            }
        },
        ChangeStatus: function(item_id, status) {

            switch (status) {
                case "read":
                    markAsRead(item_id);
                    break;
                case "unread":
                    markAsUnread(item_id);
                    break;
            }
        },
        Show: function(item_id) {
            var link = document.getElementById("show-" + item_id);
            if (link) link.click();
        },
        OpenOriginal: function(item_id) {

            var link = document.getElementById("original-" + item_id);

            if (link) {
                if (getItem(item_id).getAttribute("data-item-status") == "unread") markAsRead(item_id);
                link.removeAttribute("data-action");
                link.click();
            }
        },
        DownloadContent: function() {

            var container = document.getElementById("download-item");
            if (! container) return;

            var item_id = container.getAttribute("data-item-id");
            var message = container.getAttribute("data-before-message");

            var img = document.createElement("img");
            img.src = "./assets/img/refresh.gif";

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
        },
        MarkListingAsRead: function(redirect) {
            var articles = document.getElementsByTagName("article");
            var listing = [];

            for (var i = 0, ilen = articles.length; i < ilen; i++) {
                listing.push(articles[i].getAttribute("data-item-id"));
            }

            var request = new XMLHttpRequest();

            request.onload = function() {
                window.location.href = redirect;
            };

            request.open("POST", "?action=mark-items-as-read", true);
            request.send(JSON.stringify(listing));
        }
    };

})();