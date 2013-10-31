Miniflux.Event = (function() {

    var queue = [];

    return {
        ListenMouseEvents: function() {

            document.onclick = function(e) {

                var action = e.target.getAttribute("data-action");

                if (action) {

                    switch (action) {
                        case 'refresh-all':
                            e.preventDefault();
                            Miniflux.Feed.UpdateAll();
                            break;
                        case 'refresh-feed':
                            e.preventDefault();
                            Miniflux.Feed.Update(e.target.getAttribute("data-feed-id"));
                            break;
                        case 'mark-read':
                            e.preventDefault();
                            Miniflux.Item.MarkAsRead(e.target.getAttribute("data-item-id"));
                            break;
                        case 'mark-unread':
                            e.preventDefault();
                            Miniflux.Item.MarkAsUnread(e.target.getAttribute("data-item-id"));
                            break;
                        case 'bookmark':
                            e.preventDefault();
                            Miniflux.Item.SwitchBookmark(Miniflux.Item.Get(e.target.getAttribute("data-item-id")));
                            break;
                        case 'download-item':
                            e.preventDefault();
                            Miniflux.Item.DownloadContent();
                            break;
                        case 'original-link':
                            Miniflux.Item.OpenOriginal(e.target.getAttribute("data-item-id"));
                            break;
                        case 'mark-all-read':
                            e.preventDefault();
                            Miniflux.Item.MarkListingAsRead("?action=unread");
                            break;
                        case 'mark-feed-read':
                            e.preventDefault();
                            Miniflux.Item.MarkListingAsRead("?action=feed-items&feed_id=" + e.target.getAttribute("data-feed-id"));
                            break;
                        case 'mozilla-login':
                            e.preventDefault();
                            Miniflux.App.MozillaAuth("mozilla-auth");
                            break;
                        case 'mozilla-link':
                            e.preventDefault();
                            Miniflux.App.MozillaAuth("mozilla-link");
                            break;
                    }
                }
            };
        },
        ListenKeyboardEvents: function() {

            document.onkeypress = function(e) {

                queue.push(e.keyCode || e.which);

                if (queue[0] == 103) { // g

                    switch (queue[1]) {
                        case undefined:
                            break;
                        case 117: // u
                            window.location.href = "?action=unread";
                            queue = [];
                            break;
                        case 98: // b
                            window.location.href = "?action=bookmarks";
                            queue = [];
                            break;
                        case 104: // h
                            window.location.href = "?action=history";
                            queue = [];
                            break;
                        case 115: // s
                            window.location.href = "?action=feeds";
                            queue = [];
                            break;
                        case 112: // p
                            window.location.href = "?action=config";
                            queue = [];
                            break;
                        default:
                            queue = [];
                            break;
                    }
                }
                else {

                    queue = [];

                    switch (e.keyCode || e.which) {
                        case 100: // d
                            Miniflux.Item.DownloadContent(Miniflux.Nav.GetCurrentItemId());
                            break;
                        case 112: // p
                        case 107: // k
                            Miniflux.Nav.SelectPreviousItem();
                            break;
                        case 110: // n
                        case 106: // j
                            Miniflux.Nav.SelectNextItem();
                            break;
                        case 118: // v
                            Miniflux.Item.OpenOriginal(Miniflux.Nav.GetCurrentItemId());
                            break;
                        case 111: // o
                            Miniflux.Item.Show(Miniflux.Nav.GetCurrentItemId());
                            break;
                        case 109: // m
                            Miniflux.Item.SwitchStatus(Miniflux.Nav.GetCurrentItem());
                            break;
                        case 102: // f
                            Miniflux.Item.SwitchBookmark(Miniflux.Nav.GetCurrentItem());
                            break;
                        case 104: // h
                            Miniflux.Nav.OpenPreviousPage();
                            break
                        case 108: // l
                            Miniflux.Nav.OpenNextPage();
                            break;
                        case 114: // r
                        	Miniflux.Feed.UpdateAll();
                        	break;
                        case 63: // ?
                            Miniflux.Nav.ShowHelp();
                            break;
                    }
                }
            }
        }
    };

})();
