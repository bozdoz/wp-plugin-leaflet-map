(function() {
    // holds a function queue to call once page is loaded
    function Main() {
        var init_functions = [];

        // this function iterates all map creation functions
        this.init = function() {
            for (var i = 0, len = init_functions.length; i < len; i++) {
                var fnc = init_functions[i];
                fnc();
            }
        };

        // this is the function we use to create all map objects
        this.add = function(fnc) {
            init_functions.push(fnc);
        };

        this.getCurrentMap = function() {
            // maps are created iteratively, so the last map is the current map
            return this.maps[this.maps.length - 1];
        };

        this.getCurrentMarkerGroup = function() {
            // marker groups are mapid -> feature group
            var mapid = this.maps.length;
            if (!this.markergroups[mapid]) {
                this.markergroups[mapid] = this.newMarkerGroup(this.maps[mapid - 1]);
            }
            return this.markergroups[mapid];
        };

        this.newMarkerGroup = function(map) {
            var mg = new L.FeatureGroup().addTo(map);

            mg.timeout = null;

            // custom attribute
            if (map.fit_markers) {
                mg.on('layeradd', function(d) {
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

        // these accessible properties hold map objects
        this.maps = [];
        this.images = [];
        this.markergroups = {};
        this.markers = [];
        this.lines = [];
    }

    Main.prototype.unescape = function(str) {
        var div = document.createElement('div');
        div.innerHTML = str;
        return div.innerText;
    };

    window.WPLeafletMapPlugin = new Main();

    // initialize the function
    if (window.addEventListener) {
        window.addEventListener('load', window.WPLeafletMapPlugin.init, false);
    } else if (window.attachEvent) {
        window.attachEvent('onload', function() {
            // hopefully this helps any references to `this`
            window.WPLeafletMapPlugin.init();
        });
    }
})();
