(function() {
    // holds a function queue to call once page is loaded
    function Main() {
        
        var ready = false;
        var callbacks = [];

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
            if (ready) {
                fnc();
            } else {
                callbacks.push(fnc);
            }
        };

        /**
         * Same as above, but what if someone wants to execute a function
         * before other functions?
         */
        this.unshift = function (fnc) {
            if (ready) {
                fnc();
            } else {
                callbacks.unshift(fnc);
            }
        }

        /**
         * execute all callbacks once page/Leaflet is loaded
         */
        this.init = function() {
            ready = true;
            for (var i = 0, len = callbacks.length; i < len; i++) {
                callbacks[i]();
            }
        }

        /**
         * maps are created iteratively, so the last map is the current map
         */
        this.getCurrentMap = function () {
            return this.maps[this.maps.length - 1];
        };

        /**
         * Get/Create L.FeatureGroup for ALL shapes; used for `fitbounds`
         * @since 2.13.0
         */
        this.getCurrentGroup = function () {
            // marker groups are mapid -> feature group
            var mapid = this.maps.length;
            if (!this.markergroups[mapid]) {
                this.markergroups[mapid] = this.newMarkerGroup(this.maps[mapid - 1]);
            }
            return this.markergroups[mapid];
        };

        /** 
         * backwards-compatible getCurrentGroup 
         * @deprecated 2.13.0
         */
        this.getCurrentMarkerGroup = this.getCurrentGroup;

        /**
         * get FeatureGroup and add to map
         * 
         * ! This is extracted so that it can be overwritten by plugins
         */
        this.getGroup = function (map) {
            return new L.FeatureGroup().addTo(map);
        };

        /**
         * group is created and event is added
         */
        this.newMarkerGroup = function (map) {
            var mg = this.getGroup(map);

            mg.timeout = null;

            // custom attribute
            if (map._shouldFitBounds) {
                mg.on('layeradd', function (event) {
                    // needs a timeout so that it doesn't 
                    // opt out of a bound change
                    if (event.layer instanceof L.FeatureGroup) {
                        // wait for featuregroup/ajax-geojson to be ready
                        event.layer.on('ready', function () {
                            map.fitBounds(mg.getBounds());
                        })
                    }
                    
                    window.clearTimeout(this.timeout);
                    this.timeout = window.setTimeout(function() {
                        try {
                            map.fitBounds(mg.getBounds());
                        } catch (e) {
                            // ajax-geojson might not have valid bounds yet
                        }
                    }, 100);
                }, mg);
            }

            return mg;
        };

        var unescape = this.unescape = function (str) {
            var div = document.createElement('div');
            div.innerHTML = str;
            return div.innerText;
        };

        var templateRe = /\{ *(.*?) *\}/g;

        /**
         * It interpolates variables in curly brackets (regex above)
         * 
         * ex: "Property Value: {property_key}"
         * 
         * @param {string} str
         * @param {object} data e.g. feature.properties
         */
        this.template = function (str, data) {
            return str.replace(templateRe, function (match, key) {
                var value = parseKey(data, key);
                if (value === undefined) {
                    return match;
                }
                return value;
            });
        }

        var strToPathRe = /[.‘’'“”"\[\]]+/g

        /**
         * Converts nested object keys to array
         * 
         * ex: `this.that['and'].theOther[4]` -> 
         *     ['this', 'that', 'and', 'theOther', '4']
         * @param {string} key 
         */
        function strToPath (key) {
            var input = key.split(strToPathRe)
            var output = []
            
            // failsafe for all empty strings; 
            // mostly catches brackets at the end of a string
            for (var i = 0, len = input.length; i < len; i++) {
                if (input[i] !== '') {
                    output.push(input[i])
                }
            }
            
            return output
        }

        /**
         * It uses strToPath to access a possibly nested path value
         * 
         * @param {object} obj 
         * @param {string} key 
         */
        function parseKey (obj, key) {
            var arr = strToPath(unescape(key))
            var value = obj
            
            for (var i = 0, len = arr.length; i < len; i++) {
                value = value[arr[i]]
                if (!value) {
                    return undefined
                }
            }
            
            return value
        }

        // these accessible properties hold map objects
        this.maps = [];
        this.images = [];
        this.markergroups = {};
        this.markers = [];
        this.lines = [];
        this.polygons = [];
        this.circles = [];
        this.geojsons = [];
    }

    /**
     * window.WPLeafletMapPlugin can be used, by saving arguments, 
     * before it is officially initialized
     * 
     * This is used to deal with the potential for deferred scripts
     */
    var original = window.WPLeafletMapPlugin;
    window.WPLeafletMapPlugin = new Main();

    // check for functions to execute
    if (!!original) {
        for (var i = 0, len = original.length; i < len; i++) {
            window.WPLeafletMapPlugin.push(original[i]);
        }

        // empty the array
        original.splice(0);

        // re-add any methods that may have been added to the original
        for (var k in original) {
            if (original.hasOwnProperty(k)) {
                window.WPLeafletMapPlugin[k] = original[k];
            }
        }
    }

    // onload waits for Leaflet to load
    if (window.addEventListener) {
        window.addEventListener('load', window.WPLeafletMapPlugin.init, false);
    } else if (window.attachEvent) {
        window.attachEvent('onload', window.WPLeafletMapPlugin.init);
    }
})();
