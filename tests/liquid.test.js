require('../scripts/construct-leaflet-map');

const plugin = window.WPLeafletMapPlugin;

describe('liquid', () => {
  const observer = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('parses a boolean filter', () => {
    const str = '{ test | isBoolean }';

    plugin.liquid(str, observer);

    expect(observer).toHaveBeenCalledWith(
      str,
      expect.objectContaining({
        key: 'test',
        isBoolean: true,
      })
    );
  });

  it('also parses when liquid tag is doubled', () => {
    const str = '{{ test }}';

    plugin.liquid(str, observer);

    expect(observer).toHaveBeenCalledWith(
      str,
      expect.objectContaining({
        key: 'test',
      })
    );
  });

  it('does not parse when tag is malformed', () => {
    const str = '{ test ';

    const output = plugin.liquid(str, observer);

    expect(observer).not.toHaveBeenCalled();
    expect(output).toEqual(str);
  });

  it('does not parse when bar is missing whitespace', () => {
    const str = '{ test|isBoolean }';

    const output = plugin.liquid(str, observer);

    expect(observer).toHaveBeenCalledWith(
      expect.any(String),
      expect.not.objectContaining({
        isBoolean: true,
      })
    );
  });

  it('accepts multiple filters', () => {
    const str = '{ test | default: yolo | substr: 0,4 | lowercase }';

    plugin.liquid(str, observer);

    expect(observer).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        key: 'test',
        default: 'yolo',
        substr: '0,4',
        lowercase: true,
      })
    );
  });

  it('does not have key as a filter', () => {
    const str = '{ key | key: not key }';

    plugin.liquid(str, observer);

    expect(observer).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        key: 'key',
      })
    );
  });
});
