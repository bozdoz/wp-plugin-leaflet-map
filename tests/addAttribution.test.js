require('../scripts/construct-leaflet-map');

const plugin = window.WPLeafletMapPlugin;

// mock leaflet functions (kind of long-winded)
const addAttribution = jest.fn();
window.L = {
  control: {
    attribution: () => ({
      addTo: () => ({
        addAttribution,
      }),
    }),
  },
  map: jest.fn(),
};

const originalAttribution =
  '<a href="http://leafletjs.com" title="A JS library for interactive maps">Leaflet</a>; © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

describe('addAttribution', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('does not call addAttribution if not present in options', () => {
    plugin.createMap({});

    expect(L.map).toHaveBeenCalledWith(undefined, expect.any(Object));
    expect(addAttribution).not.toHaveBeenCalled();
  });

  it('calls addAttribution with original (backwards-compatible) HTML string', () => {
    plugin.createMap({
      attribution: originalAttribution,
    });

    const attributions = originalAttribution.split('; ');

    expect(addAttribution).toHaveBeenCalledWith(attributions[0]);
    expect(addAttribution).toHaveBeenCalledWith(attributions[1]);
  });

  it('calls original attribution with markdown string', () => {
    const attribution =
      '[Leaflet](http://leafletjs.com "A JS library for interactive maps"); © [OpenStreetMap](http://www.openstreetmap.org/copyright) contributors';

    plugin.createMap({
      attribution,
    });

    const attributions = originalAttribution.split('; ');

    expect(addAttribution).toHaveBeenCalledWith(attributions[0]);
    expect(addAttribution).toHaveBeenCalledWith(attributions[1]);
  });

  it('removes HTML, otherwise', () => {
    const attribution = '<script>alert("XSS")</script>';

    plugin.createMap({
      attribution,
    });

    expect(addAttribution).toHaveBeenCalledWith(
      '&lt;script&gt;alert("XSS")&lt;/script&gt;'
    );
  });
});
