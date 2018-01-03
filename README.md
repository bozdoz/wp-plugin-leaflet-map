Leaflet Map WordPress Plugin
========

![Leaflet](https://img.shields.io/badge/leaflet-1.2.0-green.svg?style=flat)
![WordPress](https://img.shields.io/badge/wordpress-4.8.1-green.svg?style=flat)

![Header Image](https://ps.w.org/leaflet-map/assets/banner-1544x500.png?rev=1693083)

Add a map generated with [LeafletJS](http://leafletjs.com/): an open-source JavaScript library for mobile-friendly interactive maps. Map tiles are provided by default through [OpenStreetMap](http://www.openstreetmap.org/), or [MapQuest](https://www.mapquest.ca/) (with an app key).  Can be set per map with shortcode attributes or through the dashboard settings.

![Admin Screenshot](https://imgur.com/W4BGTif.jpg)

Installation
------------

* (simple) Install via the WordPress plugins page on your WordPress site: `/wp-admin/plugin-install.php` (search Leaflet)

* (needlessly complicated) Copy this repo (or download a release of it) into your WordPress plugins directory: `/wp-content/plugins/`. You might also need to name the directory 'leaflet-map', like so: `git clone https://github.com/bozdoz/wp-plugin-leaflet-map.git leaflet-map`


Available Shortcodes
--------------------

### [leaflet-map]

![Alternate map tiles](https://imgur.com/oURcNiX.jpg)

Height, width, latitude, longitude and zoom are the basic attributes: 

```
[leaflet-map height=250 width=250 lat=44.67 lng=-63.61 zoom=5]
```

However, you can also just give it an address, and Google will look it up for you:

```
[leaflet-map address="Oslo, Norway"]
```

The default URL requires attribution by its terms of use.  If you want to change the URL, you may define a new attribution, or remove the attribution.  You can define this site-wide in the admin, or you can set this per map in the shortcode (0 for disabled):

```
[leaflet-map attribution=0]
```

OR 

```
[leaflet-map attribution="Copyright @bozdoz"]
```

The zoom buttons can be large and annoying.  Enable or disable per map in shortcode: 

```
[leaflet-map zoomcontrol="0"]
```

Alternatively, you could use a plain image for visitors to zoom and pan around with `[leaflet-image source="path/to/image/file.jpg"]`.

### [leaflet-marker]

![Markers with HTML within a popup](https://imgur.com/ap38lwe.jpg)

Add a marker to any map by adding `[leaflet-marker]` after any `[leaflet-map]` shortcode.  You can adjust the lat/lng in the same way, as well as some other basic functionality (popup message, draggable, visible on load).  Also, if you want to add a link to a marker popup, use `[leaflet-marker]Message here: click here[/leaflet-marker]` and add a link like you normally would with the WordPress editor.


### [leaflet-line]

![Fitted Colored Line](https://imgur.com/dixNDtF.jpg)

Add a line to the map by adding `[leaflet-line]`. You can specify the postions with a list separated by semi-colon `;` or bar `|` using lat/lng: `[leaflet-line latlngs="41, 29; 44, 18"]` or addresses: `[leaflet-line addresses="Istanbul; Sarajevo"]`, or x/y coordinates for image maps.

### [leaflet-geojson]

[![Random GeoJSON created by me](https://imgur.com/fJktgtI.jpg)](https://gist.github.com/bozdoz/064a7101b95a324e8852fe9381ab9a18)

Or you can add a geojson shape via a url: 

```
[leaflet-geojson src="https://rawgit.com/bozdoz/567817310f102d169510d94306e4f464/raw/2fdb48dafafd4c8304ff051f49d9de03afb1718b/map.geojson"]
```

Check it out [here](https://gist.github.com/bozdoz/064a7101b95a324e8852fe9381ab9a18).

### [leaflet-kml]

Same idea as geojson (above), but takes KML files and loads [Mapbox's togeojson library](https://github.com/mapbox/togeojson)

Contributing
-------------

[View Contribution guidelines](https://github.com/bozdoz/wp-plugin-leaflet-map/blob/master/CONTRIBUTING.md)

Wish List
---------

* A map editor/shortcode generator (so users can see what they're adding to the page)
* Make the plugin extendable (add hooks/filters/actions)