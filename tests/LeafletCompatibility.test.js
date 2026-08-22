function loadExtension(path, leaflet) {
  window.L = leaflet;
  window.WPLeafletMapPlugin = [];
  jest.resetModules();
  require(path);
  window.WPLeafletMapPlugin[0]();
}

function createLeafletV1Class() {
  class LeafletClass {
    constructor() {
      if (this.initialize) {
        this.initialize.apply(this, arguments);
      }
    }

    static extend(definition) {
      class ExtendedClass extends this {}
      Object.assign(ExtendedClass.prototype, definition);
      return ExtendedClass;
    }
  }

  return LeafletClass;
}

describe('Leaflet compatibility extensions', () => {
  afterEach(() => {
    delete window.L;
    delete window.WPLeafletMapPlugin;
  });

  it('keeps SVG icons on the Leaflet v1 extension path', () => {
    const Icon = createLeafletV1Class();
    const Marker = createLeafletV1Class();
    Icon.prototype.options = {};
    Marker.prototype.initialize = function (latlng, options) {
      this.latlng = latlng;
      this.options = options;
    };

    const leaflet = { Icon, Marker, extend: Object.assign };
    loadExtension('../scripts/leaflet-svg-icon.js', leaflet);

    const marker = new leaflet.SVGMarker([1, 2], {});
    expect(marker.options.icon).toBeInstanceOf(leaflet.SVGIcon);
  });

  it('uses native subclasses for SVG icons on the Leaflet v2 path', () => {
    class Icon {
      constructor(options) {
        this.options = Object.assign(
          {},
          this.constructor.prototype.options,
          options
        );
      }
    }
    Icon.prototype.options = {};

    class Marker {
      constructor(latlng, options) {
        this.latlng = latlng;
        this.options = options;
      }
    }

    const leaflet = { Icon, Marker };
    loadExtension('../scripts/leaflet-svg-icon.js', leaflet);

    const marker = new leaflet.SVGMarker([1, 2], {});
    expect(marker.options.icon).toBeInstanceOf(leaflet.SVGIcon);
  });

  it('keeps Ajax GeoJSON on the Leaflet v1 extension path', () => {
    const GeoJSON = createLeafletV1Class();
    GeoJSON.prototype.options = {};

    const leaflet = {
      GeoJSON,
      setOptions(object, options) {
        object.options = Object.assign({}, object.options, options);
      },
      geoJson(data, options) {
        return new GeoJSON(data, options);
      },
    };
    loadExtension('../scripts/leaflet-ajax-geojson.js', leaflet);

    const layer = new leaflet.AjaxGeoJSON('/data.json', { type: 'json' });
    expect(layer.layer).toBeInstanceOf(GeoJSON);
    expect(layer._url).toBe('/data.json');
  });

  it('uses a native GeoJSON subclass on the Leaflet v2 path', () => {
    class GeoJSON {
      constructor(data, options) {
        this.data = data;
        this.options = options;
      }
    }

    const leaflet = { GeoJSON };
    loadExtension('../scripts/leaflet-ajax-geojson.js', leaflet);

    const layer = new leaflet.AjaxGeoJSON('/data.json', { type: 'json' });
    expect(layer.layer).toBeInstanceOf(GeoJSON);
    expect(layer._url).toBe('/data.json');
  });
});
