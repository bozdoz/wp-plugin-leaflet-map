(function () {
  // holds a function queue to call once page is loaded
  function Main() {
    // TODO: figure out how to derive this from php or package.json instead
    var VERSION = 'v3.0.4';
    this.VERSION = VERSION;

    /**
     * Call a render function, wrapped in a try/catch
     * @param {() => void} fnc
     */
    function callRenderFunction(fnc) {
      try {
        fnc();
      } catch (e) {
        console.log('-- version --', VERSION);
        console.error(e);
      }
    }

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
    this.push = function (fnc) {
      if (ready) {
        callRenderFunction(fnc);
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
        callRenderFunction(fnc);
      } else {
        callbacks.unshift(fnc);
      }
    };

    /**
     * execute all callbacks once page/Leaflet is loaded
     */
    this.init = function () {
      ready = true;
      for (var i = 0, len = callbacks.length; i < len; i++) {
        callRenderFunction(callbacks[i]);
      }
    };

    /**
     * Create map from a div element
     * @since 2.18.0
     */
    this.createMap = function (options) {
      // gets maps by className in order
      var elems = document.getElementsByClassName('WPLeafletMap');
      var i = this.maps.length;
      var container = elems[i];

      var map = L.map(container, options);

      // removed from PHP in 2.18.0
      if (options.fitBounds) {
        map._shouldFitBounds = true;
      }

      // removed from PHP in 2.18.0
      if (options.attribution) {
        addAttributionToMap(options.attribution, map);
      }

      this.maps.push(map);

      return map;
    };

    /**
     * Create image map from a div element
     * @since 2.18.0
     */
    this.createImageMap = function (options) {
      var map = this.createMap(options);

      // moved from PHP in 2.18.0
      map.is_image_map = true;

      this.images.push(map);

      return map;
    };

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
        mg.on(
          'layeradd',
          function (event) {
            if (event.layer instanceof L.FeatureGroup) {
              // wait for featuregroup/ajax-geojson to be ready
              event.layer.on('ready', function () {
                map.fitBounds(mg.getBounds());
              });
            }

            // needs a timeout so that it doesn't
            // opt out of a bound change
            window.clearTimeout(this.timeout);
            this.timeout = window.setTimeout(function () {
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
    };

    /** Adds all properties as a table-view for GeoJSON popups */
    this.propsToTable = function (props) {
      var prop;
      var keys = [];
      for (prop in props) {
        if (Object.prototype.hasOwnProperty.call(props, prop)) {
          keys.push(prop);
        }
      }
      keys = keys.sort();

      var output = '<table>';

      for (var i = 0, len = keys.length; i < len; i++) {
        var key = keys[i];
        output += '<tr><td>' + key + '</td>';
        output += '<td>' + props[key] + '</td></tr>';
      }

      output += '</table>';

      return output;
    };

    function trim(a) {
      return a.trim ? a.trim() : a.replace(/^\s+|\s+$/gm, '');
    }

    function addAttributionToMap(attribution, map) {
      if (!attribution) {
        return;
      }

      var attributions = attribution.split(';');
      var attControl = L.control
        .attribution({
          prefix: false,
        })
        .addTo(map);

      for (var i = 0, len = attributions.length; i < len; i++) {
        var att = trim(attributions[i]);
        attControl.addAttribution(att);
      }
    }

    var unescape = (this.unescape = function (str) {
      var div = document.createElement('div');
      div.innerHTML = str;
      return div.innerText || str;
    });

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
      if (data == null) {
        return str;
      }

      return str.replace(templateRe, function (match, key) {
        var obj = liquid(key);
        var value = parseKey(data, obj.key);
        if (value == null) {
          return obj.default || match;
        }
        return value;
      });
    };

    /** used in strToPath */
    var strToPathRe = /[.‘’'“”"\[\]]+/g;

    /**
     * Converts nested object keys to array
     *
     * ex: `this.that['and'].theOther[4]` ->
     *     ['this', 'that', 'and', 'theOther', '4']
     * @param {string} key
     */
    function strToPath(key) {
      if (key == null) {
        return [];
      }
      var input = key.split(strToPathRe);
      var output = [];

      // failsafe for all empty strings;
      // mostly catches brackets at the end of a string
      for (var i = 0, len = input.length; i < len; i++) {
        if (input[i] !== '') {
          output.push(input[i]);
        }
      }

      return output;
    }

    /**
     * It uses strToPath to access a possibly nested path value
     *
     * @param {object} obj
     * @param {string} key
     */
    function parseKey(obj, key) {
      var arr = strToPath(unescape(key));
      var value = obj;

      for (var i = 0, len = arr.length; i < len; i++) {
        value = value[arr[i]];
        if (!value) {
          return undefined;
        }
      }

      return value;
    }

    /**
     * parses liquid tags from a string
     *
     * @param {string} str
     */
    function liquid(str) {
      var tags = str.split(' | ');
      var obj = {};

      // removes initial variable from array
      var key = tags.shift();

      for (var i = 0, len = tags.length; i < len; i++) {
        var tag = tags[i].split(': ');
        var tagName = tag.shift();
        var tagValue = tag.join(': ') || true;

        obj[tagName] = tagValue;
      }

      // always preserve the original string
      obj.key = key;

      return obj;
    }

    function waitFor(prop, cb) {
      if (typeof L !== 'undefined' && typeof L[prop] !== 'undefined') {
        cb();
      } else {
        setTimeout(function () {
          waitFor(prop, cb);
        }, 100);
      }
    }

    /** wait for leaflet-svg-icon (if deferred) */
    this.waitForSVG = function (cb) {
      waitFor('SVGIcon', cb);
    };

    /** wait for leaflet-ajax-geojson (if deferred) */
    this.waitForAjax = function (cb) {
      waitFor('AjaxGeoJSON', cb);
    };

    this.createScale = function (options) {
      L.control.scale(options).addTo(this.getCurrentMap());
    };

    this.getIconOptions = function (options) {
      var _options = options || {};
      var iconArrays = [
        'iconSize',
        'iconAnchor',
        'shadowSize',
        'shadowAnchor',
        'popupAnchor',
      ];
      var default_icon = L.Icon.Default.prototype.options;
      // arrays are strings, unfortunately...
      for (var i = 0, len = iconArrays.length; i < len; i++) {
        var option_name = iconArrays[i];
        var option = _options[option_name];
        // convert "1,2" to [1, 2];
        if (option) {
          var arr = option.split(',');
          // array.map for ie<9
          for (var j = 0, lenJ = arr.length; j < lenJ; j++) {
            arr[j] = Number(arr[j]);
          }
          _options[option_name] = arr;
        }
      }
      // default popupAnchor
      if (!_options.popupAnchor) {
        // set (roughly) to size of icon
        _options.popupAnchor = (function (i_size) {
          // copy array
          i_size = i_size.slice();

          // inverse coordinates
          i_size[0] = 0;
          i_size[1] *= -1;
          // bottom position on popup is 7px
          i_size[1] -= 3;
          return i_size;
        })(_options.iconSize || default_icon.iconSize);
      }
      if (_options.iconUrl) {
        _options.icon = new L.Icon(_options);
      }
      return _options;
    };

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
