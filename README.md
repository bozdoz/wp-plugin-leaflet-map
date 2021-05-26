# Leaflet Map WordPress Plugin

![Leaflet](https://img.shields.io/badge/leaflet-1.7.1-green.svg?style=flat)
![WordPress](https://img.shields.io/badge/wordpress-5.6.1-green.svg?style=flat)

![Header Image](https://ps.w.org/leaflet-map/assets/banner-1544x500.png?rev=1693083)

Add a map generated with [LeafletJS](http://leafletjs.com/): an open-source JavaScript library for mobile-friendly interactive maps. Map tiles are provided by default through [OpenStreetMap](http://www.openstreetmap.org/), or [MapQuest](https://www.mapquest.ca/) (with an app key). Can be set per map with shortcode attributes or through the dashboard settings.

![Admin Screenshot](https://imgur.com/W4BGTif.jpg)

## Table of Contents

- [Leaflet Map WordPress Plugin](#leaflet-map-wordpress-plugin)
  - [Table of Contents](#table-of-contents)
  - [Installation](#installation)
  - [General Usage](#general-usage)
  - [Developing](#developing)
  - [Available Shortcodes](#available-shortcodes)
    - [[leaflet-map]](#leaflet-map)
      - [[leaflet-map] Options:](#leaflet-map-options)
    - [[leaflet-image]](#leaflet-image)
      - [[leaflet-image] Options:](#leaflet-image-options)
    - [[leaflet-marker]](#leaflet-marker)
      - [[leaflet-marker] Options:](#leaflet-marker-options)
    - [[leaflet-line]](#leaflet-line)
      - [[leaflet-line] Options](#leaflet-line-options)
    - [[leaflet-polygon]](#leaflet-polygon)
    - [[leaflet-circle]](#leaflet-circle)
      - [[leaflet-circle] Options](#leaflet-circle-options)
    - [[leaflet-geojson]](#leaflet-geojson)
      - [[leaflet-geojson] Options](#leaflet-geojson-options)
    - [[leaflet-kml]](#leaflet-kml)
    - [[leaflet-gpx]](#leaflet-gpx)
    - [[leaflet-scale]](#leaflet-scale)
  - [Frequently Asked Questions](#frequently-asked-questions)
    - [How Can I Add another Leaflet Plugin?](#how-can-i-add-another-leaflet-plugin)
  - [Contributing](#contributing)
  - [Wish List](#wish-list)

## Installation

- Install via the WordPress plugins page on your WordPress site: `/wp-admin/plugin-install.php` (search Leaflet)

## General Usage

```
[leaflet-map address="Manhattan, New York"]
[leaflet-marker]
```

The above shortcode will produce a map centered at Manhattan, New York (thanks to geocoding), and also drop a marker in the center.

It's also useful to add popups to the markers:

```
[leaflet-map address="Las Vegas"]
[leaflet-marker]Hey! This is where I got married![/leaflet-marker]
```

You can have SVG markers, add shapes, geojson, kml, images, and more! See available shortcodes below.

## Developing

This plugin uses Docker for development. Simply:

1. [install Docker](https://www.docker.com/get-started)
1. fork/clone the repo, and
1. execute this command from the repo's root directory in your terminal:

```bash
docker-compose up
```

You can also use NPM scripts to interact with Docker, if you make changes to Docker-related files:

To start:

```bash
npm start
```

To completely remove:

```bash
npm run destroy
```

That's all for now! Thanks!

## Available Shortcodes

### [leaflet-map]

![Alternate map tiles](https://imgur.com/oURcNiX.jpg)

Height, width, latitude, longitude and zoom are the basic attributes:

```
[leaflet-map height=250 width=250 lat=44.67 lng=-63.61 zoom=5]
```

However, you can also just give it an address, and the chosen geocoder (default: Nominatum) will look it up for you:

```
[leaflet-map address="Oslo, Norway"]
```

#### [leaflet-map] Options:

| Option                       | Default                                              |
| ---------------------------- | ---------------------------------------------------- |
| `lat` and `lng` or `address` | lat: 44.67, lng: -63.61                              |
| `zoom`                       | 12                                                   |
| `height`                     | 250                                                  |
| `width`                      | 100%                                                 |
| `fitbounds`                  | 0 (false)                                            |
| `zoomcontrol`                | 0 (false)                                            |
| `scrollwheel`                | 0 (false)                                            |
| `doubleclickzoom`            | 0 (false)                                            |
| `min_zoom`                   | 0                                                    |
| `max_zoom`                   | 20                                                   |
| `subdomains`                 | abc                                                  |
| `attribution`                | ©Leaflet ©OpenStreetMap                              |
| `closepopuponclick`          | false                                                |
| `trackresize`                | false                                                |
| `boxZoom`                    | true                                                 |
| `dragging`                   | true                                                 |
| `keyboard`                   | true                                                 |
| `maxbounds`                  | null                                                 |
| `detect-retina`              | 0 (false)                                            |
| `tileurl`                    | 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png' |
| `subdomains`                 | 'abc'                                                |
| `tap`                        | true                                                 |
| `tilesize`                   | 256                                                  |
| `mapid`                      | null                                                 |
| `accesstoken`                | null                                                 |
| `zoomoffset`                 | 0                                                    |
| `nowrap`                     | false                                                |

---

### [leaflet-image]

Much the same as leaflet-map above, but uses `src` for the source image.

TBH, it's a huge mess, and probably shouldn't be used. It might make a good image viewer with optional marker highlight points. It requires far too much manual work at the moment. Recommended usage:

```
[leaflet-image src="path/to/img.jpg" zoom=1]
[leaflet-marker draggable]
```

Then in the console, check the coordinates when you move the marker (should only work at that zoom level).

#### [leaflet-image] Options:

| Option            | Default                          |
| ----------------- | -------------------------------- |
| `src`             | https://picsum.photos/1000/1000/ |
| `zoom`            | 12                               |
| `height`          | 250                              |
| `width`           | 100%                             |
| `fitbounds`       | 0 (false)                        |
| `zoomcontrol`     | 0 (false)                        |
| `scrollwheel`     | 0 (false)                        |
| `doubleclickzoom` | 0 (false)                        |
| `min_zoom`        | 0                                |
| `max_zoom`        | 20                               |
| `attribution`     | ©Leaflet ©OpenStreetMap          |

---

### [leaflet-marker]

![Markers with HTML within a popup](https://imgur.com/ap38lwe.jpg)

Add a marker to any map by adding `[leaflet-marker]` after any `[leaflet-map]` shortcode. You can adjust the lat/lng in the same way, as well as some other basic functionality (popup message, draggable, visible on load). Also, if you want to add a link to a marker popup, use `[leaflet-marker]Message here: click here[/leaflet-marker]` and add a link like you normally would with the WordPress editor.

#### [leaflet-marker] Options:

| Option                       | Usage                                                                                      |
| ---------------------------- | ------------------------------------------------------------------------------------------ |
| `lat` and `lng` or `address` | Location on the map; defaults to map center; `lat`/`lng` are floats, `address` is a string |
| `draggable`                  | Make a marker draggable (`boolean`); default `false`                                       |
| `title`                      | Add a hover-over message to your marker (different than popup)                             |
| `alt`                        | Add an alt text to the marker image                                                        |
| `zindexoffset`               | Define the z-index for the marker image                                                    |
| `opacity`                    | Define the css opacity for the marker image                                                |
| `iconurl`                    | Give a url for the marker image file                                                       |
| `iconsize`                   | Set the size of the icon: e.g. "80,50" for 80px width 50px height                          |
| `iconanchor`                 | Set the anchor position of the icon: e.g. "40,60" for 40px left 60px top                   |
| `shadowurl`                  | Give a url for the marker shadow image file                                                |
| `shadowsize`                 | Set the size of the shadow: e.g. "80,50" for 80px width 50px height                        |
| `shadowanchor`               | Set the anchor position of the shadow: e.g. "40,60" for 40px left 60px top                 |
| `popupanchor`                | Set the anchor position of the popup: e.g. "40,60" for 40px left 60px top                  |
| `svg`                        | Boolean for whether the marker should be created as an svg: default `false`                |
| `background`                 | Background color for an SVG marker (above)                                                 |
| `color`                      | color of the SVG marker (above)                                                            |
| `iconclass`                  | className for the marker image                                                             |

---

### [leaflet-line]

![Fitted Colored Line](https://imgur.com/dixNDtF.jpg)

Add a line to the map by adding `[leaflet-line]`. You can specify the postions with a list separated by semi-colon `;` or bar `|` using lat/lng: `[leaflet-line latlngs="41, 29; 44, 18"]` or addresses: `[leaflet-line addresses="Istanbul; Sarajevo"]`, or x/y coordinates for image maps.

Add a popup to the line by adding text to the content of the shortcode:

`[leaflet-line addresses="new york; chicago"]New York to Chicago[/leaflet-line]`

#### [leaflet-line] Options

| Option                                   | Usage                                                                                                                                                                                      |
| ---------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `addresses`, `latlngs`, or `coordinates` | For geocoded addresses, latitude/longitude, or x/y coordinates for Image Maps; ex: `[leaflet-line latlngs="41, 29; 44, 18"]` or addresses: `[leaflet-line addresses="Istanbul; Sarajevo"]` |
| `fitbounds`                              | Fit the map to the bounds of the line (instead of whatever center you gave the map originally)                                                                                             |

And the following Shape Options. See https://leafletjs.com/reference-1.3.4.html#path for details.
'stroke', 'color', 'weight', 'opacity',
'lineCap', 'lineJoin', 'dashArray', 'dashOffset'
'fill', 'fillColor', 'fillOpacity', 'fillRule', 'className'

---

### [leaflet-polygon]

Virtually the same as [leaflet-line](above)

---

### [leaflet-circle]

![Circle](https://i.imgur.com/rVHH6Zm.png?1234)

Add a circle to the map by adding `[leaflet-circle]`. You can specify the position using `lat` and `lng` and the radius in meters using `radius`. You can also customize the style using [Leaflet's Path options](https://leafletjs.com/reference-1.3.4.html#path-option). Example: `[leaflet-circle message="max distance" lng=5.1179 lat=52.0979 radius=17500 color="#0DC143" fillOpacity=0.1]`.

#### [leaflet-circle] Options

| Options                        | Usage                                                                                                                                                                                         |
| ------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `address`, `lat/lng`, or `x/y` | For geocoded addresses, latitude/longitude, or x/y coordinates for Image Maps (see [leaflet-image]); ex: `[leaflet-circle lat=52 lng=5]` or addresses: `[leaflet-circle address="Amsterdam"]` |
| `fitbounds`                    | Fit the map to the bounds of the circle (instead of whatever center you gave the map originally)                                                                                              |
| radius                         | Radius of the circle in meters                                                                                                                                                                |

Includes all style options: See https://leafletjs.com/reference-1.3.4.html#path

---

### [leaflet-geojson]

[![Random GeoJSON created by me](https://imgur.com/fJktgtI.jpg)](https://gist.github.com/bozdoz/064a7101b95a324e8852fe9381ab9a18)

Or you can add a geojson shape via a url:

```
[leaflet-geojson src="https://gist.githubusercontent.com/bozdoz/064a7101b95a324e8852fe9381ab9a18/raw/03f4f54b13a3a7e256732760a8b679818d9d36fc/map.geojson"]
```

#### [leaflet-geojson] Options

| Option           | Usage                                                                                            |
| ---------------- | ------------------------------------------------------------------------------------------------ |
| `src`            | Source of the geojson file                                                                       |
| `popup_text`     | Text for any popups when shapes are clicked                                                      |
| `popup_property` | Name of the geojson property that contains popup content                                         |
| `fitbounds`      | Fit the map to the bounds of all shapes (instead of whatever center you gave the map originally) |
| `circleMarker`   | Display circles instead of markers. Vastly improves performance on maps with a lot of points.    |
| `radius`         | Radius of the circles, when `circleMarkers` is set                                               |
| `table-view`     | Show all properties on each feature when clicked                                                 |
| `iconurl`        | Give a url for the marker image file                                                             |
| `iconsize`       | Set the size of the icon: e.g. "80,50" for 80px width 50px height                                |
| `iconanchor`     | Set the anchor position of the icon: e.g. "40,60" for 40px left 60px top                         |
| `popupanchor`    | Set the anchor position of the popup: e.g. "40,60" for 40px left 60px top                        |

Includes all style options: See https://leafletjs.com/reference-1.3.4.html#path. Also, if you want to add feature
properties to the popups, use the inner content and curly brackets to substitute the values:
`[leaflet-geojson]Field A = {field_a}[/leaflet-geojson]`.

### [leaflet-kml]

Same idea as geojson (above), but takes KML files and loads [Mapbox's togeojson library](https://github.com/mapbox/togeojson)

### [leaflet-gpx]

Same idea as geojson and KML (above), but takes GPX files and also loads [Mapbox's togeojson library](https://github.com/mapbox/togeojson)

### [leaflet-scale]

Can be added after any map, or enabled for all maps in the admin. If you want to extend it, you can extend window.WPLeafletMapPlugin.createScale with custom JavaScript.

| Option           | Default   |
| ---------------- | --------- |
| `maxWidth`       | 100       |
| `metric`         | 1 (true)  |
| `imperial`       | 1 (true)  |
| `updateWhenIdle` | 0 (false) |
| `position`       | topright  |

## Frequently Asked Questions

### How Can I Add another Leaflet Plugin?

There are some steps you can take, currently, to add another Leaflet Plugin to enhance this WordPress plugin. In general, you can add an action to trigger when Leaflet is loaded, and add custom JavaScript and any dependencies your plugin needs:

Here's an example with MapBox Fullscreen plugin:

functions.php

```php
add_action('leaflet_map_loaded', 'fs_leaflet_loaded');
function fs_leaflet_loaded() {
  wp_enqueue_script('full_screen_leaflet', 'https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js', Array('wp_leaflet_map'), '1.0', true);
  wp_enqueue_style('full_screen_leaflet_styles', 'https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css');

  // custom js
  wp_enqueue_script('full_screen_custom', get_theme_file_uri( '/js/full-screen.js' ), Array('full_screen_leaflet'), '1.0', true);
}
```

/js/full-screen.js

```js
(function () {
  function main() {
    if (!window.WPLeafletMapPlugin) {
      console.log('no plugin found!');
      return;
    }

    // iterate any of these arrays: `maps`, `markers`, `lines`, `circles`, `geojsons`
    var maps = window.WPLeafletMapPlugin.maps;
    
    // Note: `markergroups` is an *object*. If you'd like to iterate it, you can do it like this:
    // var markergroups = window.WPLeafletMapPlugin.markergroups;
    // var keys = Object.keys(markergroups);
    // for (var i = 0, len = keys.length; i < len; i++) {
    //   var markergroup = markergroups[keys[i]];
    // }

    for (var i = 0, len = maps.length; i < len; i++) {
      var map = maps[i];
      map.whenReady(function () {
        this.addControl(new L.Control.Fullscreen());
      });
    }
  }

  window.addEventListener('load', main);
})();
```

## Contributing

[View Contribution guidelines](https://github.com/bozdoz/wp-plugin-leaflet-map/blob/master/CONTRIBUTING.md)

## Wish List

- A map editor/shortcode generator (so users can see what they're adding to the page)
