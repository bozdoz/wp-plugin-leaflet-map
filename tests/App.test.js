require('../scripts/construct-leaflet-map');

const plugin = window.WPLeafletMapPlugin;

describe('WPLeafletMapPlugin', () => {
  it('is defined', () => {
    expect(plugin).toBeDefined();
  });

  describe('template()', () => {
    it('works if empty', () => {
      expect(plugin.template('')).toBe('');
    });

    it('works on basic string', () => {
      const str = 'asdf';
      expect(plugin.template(str)).toBe(str);
    });

    it('outs garbage when ins garbage', () => {
      expect(plugin.template('{adjective} cat')).toBe('{adjective} cat');
    });

    it('works on dict', () => {
      const adjective = 'nice';
      expect(plugin.template('{adjective} cat', { adjective })).toBe(
        `${adjective} cat`
      );
    });

    it('works on nested dict', () => {
      const dict = {
        title: 'example',
        data: {
          item: {
            name: 'overkill',
          },
        },
      };
      expect(
        plugin.template('this is an {title} of {data.item.name}', dict)
      ).toBe(`this is an example of overkill`);
    });

    it('works on arrays', () => {
      const dict = {
        items: [{ name: 'apple' }, { name: 'banana' }, { name: 'cucumber' }],
      };
      expect(
        plugin.template(
          'Here is a {items[1]["name"]} and a {items.2.name}',
          dict
        )
      ).toBe(`Here is a banana and a cucumber`);
    });

    it('accepts a default template tag', () => {
      expect(
        plugin.template('Email: {attributes.email | default: None found}', {})
      ).toBe('Email: None found');
    });

    it('does not use default template tag if found', () => {
      const email = 'who@isthis.com';
      expect(
        plugin.template('Email: {attributes.email | default: None found}', {
          attributes: { email },
        })
      ).toBe(`Email: ${email}`);
    });
  });

  describe('getIconOptions()', () => {
    window.L = {
      Icon: jest.fn(),
    };
    window.L.Icon.Default = {
      prototype: {
        options: {
          iconSize: [10, 10],
        },
      },
    };
    it('works with no inputs', () => {
      expect(plugin.getIconOptions()).toEqual(
        expect.objectContaining({
          popupAnchor: expect.arrayContaining([0, -13]),
        })
      );
    });

    it('calls L.Icon if iconUrl passed', () => {
      const options = plugin.getIconOptions({
        iconUrl: 'gravy',
      });
      expect(L.Icon).toHaveBeenCalled();
      expect(options).toEqual(
        expect.objectContaining({
          icon: expect.anything(),
        })
      );
    });

    it('transforms csv into arrays', () => {
      const options = plugin.getIconOptions({
        popupAnchor: '11,22',
      });
      expect(options).toEqual(
        expect.objectContaining({
          popupAnchor: [11, 22],
        })
      );
    });
  });
});
