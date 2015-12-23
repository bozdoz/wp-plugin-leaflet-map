Leaflet Map WordPress Plugin
========

How to Use
----------

Add a map generated with <a href="http://www.leafletjs.com/" target="_blank">Leaflet JS</a>: a mobile friendly map application.  Map tiles are provided by default through <a href="http://developer.mapquest.com/web/products/open/map" target="_blank">MapQuest</a>.  Can be set per map with shortcode attributes or through the dashboard settings.

Some shortcode attributes:

Height, width, latitude, longitude and zoom are the basic attributes: 

`[leaflet-map height=250 width=250 lat=44.67 lng=-63.61 zoom=5]`

However, you can also just give it an address, and Google will look it up for you:

`[leaflet-map address="Oslo, Norway"]`

The default URL requires attribution by its terms of use.  If you want to change the URL, you may remove the attribution.  Also, you can set this per map in the shortcode (1 for enabled and 0 for disabled): 

`[leaflet-map show_attr="1"]`

The zoom buttons can be large and annoying.  Enabled or disable per map in shortcode: 

`[leaflet-map zoomcontrol="0"]`

Add a marker to any map by adding `[leaflet-marker]` after any `[leaflet-map]` shortcode.  You can adjust the lat/lng in the same way, as well as some other basic functionality (popup message, draggable, visible on load).  Also, if you want to add a link to a marker popup, use `[leaflet-marker]Message here: click here[/leaflet-marker]` and add a link like you normally would with the WordPress editor.

Add a line to the map by adding `[leaflet-line]`. You can specify the postions with a list separated by semi-colon `;` or bar `|` using lat/lng: `[leaflet-line latlngs="41, 29; 44, 18"]` or addresses: `[leaflet-line addresses="Istanbul; Sarajevo"]`, or x/y coordinates for image maps.

Alternatively, you could use a plain image for visitors to zoom and pan around with `[leaflet-image source="path/to/image/file.jpg"]`.  See screenshots 3 - 5 for help setting that up.

Installation
------------

* Copy this repo into your WordPress plugins directory: /wp-content/plugins/
* Install via the WordPress plugins page on your WordPress site: /wp-admin/plugin-install.php (search Leaflet)

Pull Requests
----------

I believe the purpose of this plugin is to provide simple creation of maps on their WordPress sites, using a basic Leaflet JS setup.  I will not accept pull requests that incorporate any other [Leaflet plugin](http://leafletjs.com/plugins.html) into this one, or any copies of them.  Also, please keep your pull requests limited to one feature/improvement each, as a courtesy to me who has to look through it trying to figure out what does what (and if it works at all).  Any number of bug fixes is completely acceptable. :)

Wish List
---------

* A way to customize markers (likely a marker media library)
* A map editor/shortcode generator (so users can see what they're adding to the page)
* More shape options than just lines