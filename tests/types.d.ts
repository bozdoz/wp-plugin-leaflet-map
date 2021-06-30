/**
 * VSCode sometimes recognizes this file 🤷‍♀️
 * maybe the tests should just be written in typescript
 */
interface Options {
  fitBounds: boolean;
  attribution: string;
}

type LeafletMap = {};
type LeafletFeatureGroup = {};

// helps with some intellisense
export declare global {
  interface Window {
    WPLeafletMapPlugin: {
      push(cb: () => void): void;
      unshift(cb: () => void): void;
      init(): void;
      createMap(options: Options): LeafletMap;
      createImageMap(options: Options): LeafletMap;
      getCurrentMap(): LeafletMap;
      getCurrentGroup(): LeafletFeatureGroup;
      getGroup(map: LeafletMap): LeafletFeatureGroup;
      newMarkerGroup(map: LeafletMap): LeafletFeatureGroup;
      propsToTable(props: {}): string;
      template(str: string, data: {}): string;
      waitForSVG(cb: () => void): void;
      waitForAjax(cb: () => void): void;
      createScale(options: {}): void;
      maps: LeafletMap[];
      images: LeafletMap[];
      markergroups: Record<number, LeafletGroup>;
      markers: {}[];
      lines: {}[];
      polygons: {}[];
      circles: {}[];
      geojson: {}[];
    };
  }
}
