L.WPLeafletMapPlugin = L.Class({
    includes: L.Evented || L.Mixin.Events,

    maps: [],
    images: [],
    markergroups: [],
    markers: [],
    lines: [],
    circles: [],
    geojsons: [],

    // initialize: function() {},

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
     *
     * @param {function} fnc
     */
    push: function(fnc) {
        fnc();
    },

    /**
     * It adds items to specific lists of accessible items;
     *
     * used to fire events for plugins
     *
     * e.g.:
     * - window.WPLeafletMapPlugin.add('map', map);
     * and a hook:
     * - window.WPLeafletMapPlugin.on('map', function (map) {
     *   ...use map here
     * })
     * @param {string} type
     * @param {any} item
     */
    add: function(type, item) {
        // type is saved as plural
        var plural = type + "s";
        var arr = this[plural];
        if (arr) {
            arr.push(item);
            // fire event (singular)
            var data = {};
            data[type] = item;
            this.fire(type, data);
        }
    },

    /**
     * maps are created iteratively, so the last map is the current map
     */
    getCurrentMap: function() {
        return this.maps[this.maps.length - 1];
    },

    /**
     * Get/Create L.FeatureGroup for ALL shapes; used for `fitbounds`
     * @since 2.13.0
     */
    getCurrentGroup: function() {
        // marker groups are mapid -> feature group
        var mapid = this.maps.length;
        if (!this.markergroups[mapid]) {
            this.markergroups[mapid] = this.newMarkerGroup(
                this.maps[mapid - 1]
            );
        }
        return this.markergroups[mapid];
    },

    /**
     * backwards-compatible getCurrentGroup
     * @deprecated 2.13.0
     */
    getCurrentMarkerGroup: function() {
        return this.getCurrentGroup();
    },

    /**
     * get FeatureGroup and add to map
     *
     * ! This is extracted so that it can be overwritten by plugins
     */
    getGroup: function(map) {
        return new L.FeatureGroup().addTo(map);
    },

    /**
     * group is created and event is added
     */
    newMarkerGroup: function(map) {
        var mg = this.getGroup(map);

        mg.timeout = null;

        // custom attribute
        if (map._shouldFitBounds) {
            mg.on(
                "layeradd",
                function(event) {
                    // needs a timeout so that it doesn't
                    // opt out of a bound change
                    if (event.layer instanceof L.FeatureGroup) {
                        // wait for featuregroup/ajax-geojson to be ready
                        event.layer.on("ready", function() {
                            map.fitBounds(mg.getBounds());
                        });
                    }

                    window.clearTimeout(this.timeout);
                    this.timeout = window.setTimeout(function() {
                        try {
                            map.fitBounds(mg.getBounds());
                        } catch (e) {
                            // ajax-geojson might not have valid bounds yet
                        }
                    }, 100);
                },
                mg
            );
        }

        return mg;
    },

    unescape: function(str) {
        var div = document.createElement("div");
        div.innerHTML = str;
        return div.innerText;
    }
});

/**
 * Init function
 */
(function() {
    /**
     * window.WPLeafletMapPlugin can be used, by saving arguments,
     * before it is officially initialized
     *
     * This is used to deal with the potential for deferred scripts
     */
    var original = window.WPLeafletMapPlugin;
    window.WPLeafletMapPlugin = new L.WPLeafletMapPlugin();
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
})();
