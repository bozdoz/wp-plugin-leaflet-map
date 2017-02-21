// holds a function queue to call once page is loaded
window.WPLeafletMapPlugin = new function() {
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

    // these accessible properties hold map objects
    this.maps = [];
    this.images = [];
    this.markers = [];
    this.lines = [];
};

// initialize the function
if (window.addEventListener) {
    window.addEventListener('load', WPLeafletMapPlugin.init, false);
} else if (window.attachEvent) {
    window.attachEvent('onload', function() {
        // hopefully this helps any references to `this`
        WPLeafletMapPlugin.init();
    });
}
