var Miniflux = {};

Miniflux.App = (function() {

    return {
        Run: function() {
            Miniflux.Event.ListenKeyboardEvents();
            Miniflux.Event.ListenMouseEvents();
        },
        MozillaAuth: function(action) {
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
    }

})();
