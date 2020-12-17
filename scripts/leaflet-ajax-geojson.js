(function () {
  window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
  window.WPLeafletMapPlugin.unshift(initAjaxGeoJSON);

  function initAjaxGeoJSON() {
    L.AjaxGeoJSON = L.GeoJSON.extend({
      options: {
        type: 'json', // 'json|kml|gpx'
      },

      initialize: function (url, options) {
        L.setOptions(this, options);
        this._url = url;
        this.layer = L.geoJson(null, this.options);
      },

      onAdd: function (map) {
        var _this = this;
        var type = this.options.type;
        var xhr;

        this.map = map;

        map.addLayer(this.layer);

        if (!this.request) {
          this.request = xhr = new XMLHttpRequest();

          xhr.onreadystatechange = function () {
            var data;
            if (xhr.readyState === xhr.DONE && xhr.status === 200) {
              if (type === 'json') {
                data = JSON.parse(xhr.responseText);
              } else if (['kml', 'gpx'].indexOf(type) !== -1) {
                data = window.toGeoJSON[type](xhr.responseXML);
              }
              _this.json = data;
              _this.layer.addData(data);
              _this.fire('ready');
            }
          };

          xhr.open('get', this._url, true);

          xhr.send();
        }
        this.fire('add');
      },

      eachLayer: function (fnc) {
        this.layer.eachLayer(fnc);
      },

      setStyle: function (style) {
        this.layer.setStyle(style);
      },

      resetStyle: function (layer) {
        this.layer.resetStyle(layer);
      },

      onRemove: function (map) {
        this.map.removeLayer(this.layer);
      },

      getBounds: function () {
        return this.layer.getBounds();
      },
    });

    L.ajaxGeoJson = function (url, options) {
      return new L.AjaxGeoJSON(url, options);
    };
  }
})();
