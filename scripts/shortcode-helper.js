(function () {
  window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
  window.WPLeafletMapPlugin.push(initAdminShortcodes);

  function initAdminShortcodes() {
    var map_input = document.getElementById('map-shortcode');
    var marker_input = document.getElementById('marker-shortcode');
    var reset_shortcodes = document.getElementById(
      'leaflet-map-reset-shortcodes'
    );
    var map_1 = window.WPLeafletMapPlugin.maps[0];
    var marker_1 = window.WPLeafletMapPlugin.markers[0];
    var initialZoom = 2;
    var initialCenter = { lat: 44.6701, lng: -63.61 };
    /** ignore map/marker changing when inputs change */
    var ignore = false;

    function update_marker() {
      if (ignore) {
        return;
      }

      var latlng = marker_1.getLatLng();
      marker_input.value =
        '[leaflet-marker lat=' + latlng.lat + ' lng=' + latlng.lng + ']';
    }

    function update_map() {
      if (ignore) {
        return;
      }

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

    function resetShortCodes() {
      marker_1.setLatLng(initialCenter);
      map_1.setView(initialCenter, initialZoom, { animate: false });
      map_1.whenReady(function () {
        update_marker();
        update_map();
      });
    }

    marker_1.on('drag', update_marker);
    map_1.on('move', update_map);

    update_map();
    update_marker();

    var latRegex = /lat=([-\d.]+)/;
    var lngRegex = /lng=([-\d.]+)/;
    var zoomRegex = /zoom=([\d.]+)/;

    function updateMap(e) {
      var value = e.target.value;

      try {
        var lat = value.match(latRegex)[1];
        var lng = value.match(lngRegex)[1];
        var zoom = value.match(zoomRegex)[1];

        ignore = true;

        map_1.setView({ lat: lat, lng: lng }, zoom, { animate: true });
      } catch (e) {
        // ignore
      }
    }

    function updateMarker(e) {
      var value = e.target.value;

      try {
        var lat = value.match(latRegex)[1];
        var lng = value.match(lngRegex)[1];

        ignore = true;

        marker_1.setLatLng({ lat: lat, lng: lng });
      } catch (e) {
        // ignore
      }
    }

    map_input.addEventListener('input', updateMap);
    map_input.addEventListener('blur', function () {
      ignore = false;
    });

    marker_input.addEventListener('input', updateMarker);
    marker_input.addEventListener('blur', function () {
      ignore = false;
    });

    reset_shortcodes.addEventListener('click', resetShortCodes);
  }
})();
