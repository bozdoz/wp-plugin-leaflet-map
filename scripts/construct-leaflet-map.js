// holds a function queue to call once leaflet.js is loaded
// called in init-leaflet-map.js
var WPLeafletMapPlugin = {
	maps : [],
	images : [],
	markers : [],
	init : function () {
		// shortcodes incrementally add to this function
	},
	add : function (fnc) {
		// add to init
		var prev_init = this.init;

		this.init = function () {
			prev_init();
			fnc();
		};
	}
};