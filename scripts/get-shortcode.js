jQuery(function () {
	var map_input = document.getElementById('map-shortcode'),
		marker_input = document.getElementById('marker-shortcode');
	
	function update_marker () {
		var latlng = marker_1.getLatLng();
		marker_input.value = '[leaflet-marker lat=' +
			latlng.lng +
			' lng=' + 
			latlng.lng + 
			']';
	}

	function update_map () {
		var latlng = map_1.getCenter();
		map_input.value = '[leaflet-map lat=' +
			latlng.lng +
			' lng=' + 
			latlng.lng + 
			' zoom=' +
			map_1.getZoom() +
			']';
	}

	marker_1.on('drag', update_marker);
	map_1.on('move', update_map);

	update_map();
	update_marker();

	jQuery(map_input).add(marker_input).on('click', function () {
		jQuery(this).select();
	});
});