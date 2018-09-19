(function() {
    // holds a function queue to call once page is loaded
    function Main() {
        /**
         * this function mirrors the array appending function
         * so that we can at least append functions to a global array
         * before the maps are ready to be rendered
         * 
         * All shortcodes should execute the following:
         *      window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
         *      window.WPLeafletMapPlugin.push(function () {
         * 
         * Think of it as a whenReady callback
         */
        this.push = function(fnc) {
            fnc();
        };

        /**
         * maps are created iteratively, so the last map is the current map
         */
        this.getCurrentMap = function () {
            return this.maps[this.maps.length - 1];
        };

        this.getCurrentMarkerGroup = function () {
            // marker groups are mapid -> feature group
            var mapid = this.maps.length;
            if (!this.markergroups[mapid]) {
                this.markergroups[mapid] = this.newMarkerGroup(this.maps[mapid - 1]);
            }
            return this.markergroups[mapid];
        };

        this.getGroup = function (map) {
            return new L.FeatureGroup().addTo(map);
        };

        this.newMarkerGroup = function (map) {
            var mg = this.getGroup(map);

            mg.timeout = null;

            // custom attribute
            if (map.fit_markers) {
                mg.on('layeradd', function (d) {
                    // needs a timeout so that it doesn't 
                    // opt out of a bound change
                    window.clearTimeout(this.timeout);
                    this.timeout = window.setTimeout(function() {
                        map.fitBounds(mg.getBounds());
                    }, 100);
                }, mg);
            }

            return mg;
        };

        this.unescape = function (str) {
            var div = document.createElement('div');
            div.innerHTML = str;
            return div.innerText;
        };

        // these accessible properties hold map objects
        this.maps = [];
        this.images = [];
        this.markergroups = {};
        this.markers = [];
        this.lines = [];
    }

    function init () {
        /**
         * window.WPLeafletMapPlugin can be used, by saving arguments, 
         * before it is officially initialized
         */
        var original = window.WPLeafletMapPlugin;
        window.WPLeafletMapPlugin = new Main();

        if (!!original) {
            for (var i = 0, len = original.length; i < len; i++) {
                window.WPLeafletMapPlugin.push(original[i]);
            }
        }
    }

    // initialize the function
    if (window.addEventListener) {
        window.addEventListener('load', init, false);
    } else if (window.attachEvent) {
        window.attachEvent('onload', init);
    }
})();
