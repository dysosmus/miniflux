var Miniflux = {};

Miniflux.App = (function() {

    return {
        // Blink the refresh icon to avoid to load an image and just for fun
        BlinkIcon: function() {
            var icons = document.querySelectorAll(".loading-icon");

            [].forEach.call(icons, function(icon) {
                icon.classList.toggle("loading-icon-blink");
            });
        },
        Run: function() {
            Miniflux.Event.ListenKeyboardEvents();
            Miniflux.Event.ListenMouseEvents();
        },
        MozillaAuth: function(action) {
            navigator.id.watch({
                onlogin: function(assertion) {

                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "?action=" + action + "&token=" + assertion, true);
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
