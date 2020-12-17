(function () {
  window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
  window.WPLeafletMapPlugin.push(initAdminShortcodes);

  function initAdminShortcodes() {
    var map_input = document.getElementById('map-shortcode');
    var marker_input = document.getElementById('marker-shortcode');
    var map_1 = window.WPLeafletMapPlugin.maps[0];
    var marker_1 = window.WPLeafletMapPlugin.markers[0];

    function update_marker() {
      var latlng = marker_1.getLatLng();
      marker_input.value =
        '[leaflet-marker lat=' + latlng.lat + ' lng=' + latlng.lng + ']';
    }

    function update_map() {
      var bounds = map_1.getBounds();
      var latlng = bounds.getCenter();
      map_input.value =
        '[leaflet-map lat=' +
        latlng.lat +
        ' lng=' +
        latlng.lng +
        ' zoom=' +
        map_1.getZoom() +
        ']';

      // update marker if outside of bounds
      if (!bounds.contains(marker_1.getLatLng())) {
        // move marker to center
        marker_1.setLatLng(latlng);
      }
    }

    marker_1.on('drag', update_marker);
    map_1.on('move', update_map);

    update_map();
    update_marker();

    if (map_input.addEventListener) {
      map_input.addEventListener('click', function () {
        this.select();
      });

      marker_input.addEventListener('click', function () {
        this.select();
      });
    } else {
      // IE 8
      map_input.attachEvent('onclick', function () {
        map_input.select();
      });

      marker_input.attachEvent('onclick', function () {
        marker_input.select();
      });
    }
  }
})();
