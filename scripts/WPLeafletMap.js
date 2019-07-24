L.WPLeafletMapPlugin = (L.Evented || L.Class).extend({
  includes: !!L.Evented 
    ? {}
    : L.Mixin.Events,

  maps: [],
  images: [],
  groups: [],
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
    fnc.call(this);
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
    var plural = type + "s"
    var arr = this[plural]
    if (arr) {
      arr.push(item)
      // fire event (singular)
      var data = {}
      data[type] = item
      this.fire(type, data)
    }

    return item
  },

  /**
   * maps are created iteratively, so the last map is the current map
   */
  getCurrentMap: function() {
    return this.maps[this.maps.length - 1]
  },

  /**
   * Get/Create L.FeatureGroup for ALL shapes; used for `fitbounds`
   * @since 2.13.0
   */
  getCurrentGroup: function() {
    // groups are mapid -> feature group
    var mapid = this.maps.length
    var group = this.groups[mapid]
    if (!group) {
      group = this.add("group", this.newGroup(this.getCurrentMap()))
    }
    return group
  },

  /**
   * backwards-compatible getCurrentGroup
   * @deprecated 2.13.0
   */
  getCurrentMarkerGroup: function() {
    return this.getCurrentGroup()
  },

  /**
   * get FeatureGroup and add to map
   *
   * This is extracted so that it can be overwritten by plugins
   */
  getGroup: function(map) {
    return new L.FeatureGroup().addTo(map)
  },

  /**
   * group is created and event is added
   */
  newGroup: function(map) {
    var group = this.getGroup(map)

    group.timeout = null

    // custom attribute
    if (map._shouldFitBounds) {
      group.on(
        "layeradd",
        function(event) {
          // needs a timeout so that it doesn't
          // opt out of a bound change
          if (event.layer instanceof L.FeatureGroup) {
            // wait for featuregroup/ajax-geojson to be ready
            event.layer.on("ready", function() {
              map.fitBounds(group.getBounds())
            })
          }

          window.clearTimeout(this.timeout)
          this.timeout = window.setTimeout(function() {
            try {
              map.fitBounds(group.getBounds())
            } catch (_) {
              // ajax-geojson might not have valid bounds yet
            }
          }, 100)
        },
        group
      )
    }

    return group
  },

  unescape: function(str) {
    var div = document.createElement("div")
    div.innerHTML = str
    return div.innerText
  },

  templateRe: /\{ *(.*?) *\}/g,

  /**
   * It interpolates variables in curly brackets (regex above)
   *
   * ex: "Property Value: {property_key}"
   *
   * @param {string} str
   * @param {object} data e.g. feature.properties
   */
  template: function(str, data) {
    var _parseKey = this._parseKey.bind(this);
    return str.replace(this.templateRe, function(match, key) {
      var value = _parseKey(data, key)
      if (value === undefined) {
        return match
      }
      return value
    })
  },

  _strToPathRe: /[.‘’'“”"\[\]]+/g,

  /**
   * Converts nested object keys to array
   *
   * ex: `this.that['and'].theOther[4]` ->
   *     ['this', 'that', 'and', 'theOther', '4']
   * @param {string} key
   */
  _strToPath: function(key) {
    var input = key.split(this._strToPathRe)
    var output = []

    // failsafe for all empty strings;
    // mostly catches brackets at the end of a string
    for (var i = 0, len = input.length; i < len; i++) {
      if (input[i] !== "") {
        output.push(input[i])
      }
    }

    return output
  },

  /**
   * It uses _strToPath to access a possibly nested path value
   *
   * @param {object} obj
   * @param {string} key
   */
  _parseKey: function(obj, key) {
    var arr = this._strToPath(this.unescape(key))
    var value = obj

    for (var i = 0, len = arr.length; i < len; i++) {
      value = value[arr[i]]
      if (!value) {
        return undefined
      }
    }

    return value
  }
})


;(function () {
  function init() {
    /**
     * window.WPLeafletMapPlugin can be used, by saving arguments,
     * before it is officially initialized
     *
     * This is used to deal with the potential for deferred scripts
     */
    var original = window.WPLeafletMapPlugin
    window.WPLeafletMapPlugin = new L.WPLeafletMapPlugin()
    // check for functions to execute
    if (!!original) {
      for (var i = 0, len = original.length; i < len; i++) {
        window.WPLeafletMapPlugin.push(original[i])
      }
  
      // empty the array
      original.splice(0)
  
      // re-add any methods that may have been added to the original
      for (var k in original) {
        if (original.hasOwnProperty(k)) {
          window.WPLeafletMapPlugin[k] = original[k]
        }
      }
    }
  }
  
  // waits any plugins (svg-icon, geojson-ajax) to load
  if (window.addEventListener) {
    window.addEventListener('load', init, false);
  } else if (window.attachEvent) {
    window.attachEvent('onload', init);
  }
})()
