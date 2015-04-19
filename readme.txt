=== Plugin Name ===
Author: bozdoz
Author URI: http://www.twitter.com/bozdoz/
Plugin URI: http://wordpress.org/plugins/leaflet-map/
Contributors: bozdoz, Remigr
Donate link: https://www.gittip.com/bozdoz/
Tags: leaflet, map, javascript, mapquest
Requires at least: 3.0.1
Tested up to: 4.1.1
Version: 1.10
Stable tag: 1.10
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A flexible plugin that adds basic shortcodes for creating multiple Leaflet maps and adding multiple markers to those maps.

== Description ==

Add a map generated with <a href="http://www.leafletjs.com/" target="_blank">leaflet JS</a>: a mobile friendly map application.  Map tiles are provided by default through <a href="http://developer.mapquest.com/web/products/open/map" target="_blank">MapQuest</a>.  Can be set per map with shortcode attributes or through the dashboard settings.

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

Check out the source code on [GitHub](https://github.com/bozdoz/wp-plugin-leaflet-map)!

== Installation ==

1. Choose to add a new plugin, then click upload
2. Upload the leaflet-map zip
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Use the shortcodes in your pages or posts: e.g. `[leaflet-map]` and `[leaflet-marker]`

== Frequently Asked Questions ==

* Can I add a message to a marker?

Yes: `[leaflet-marker message="Hello there!" visible="true"]`, where visible designates if it is visible on page load. Otherwise it is only visible when clicked.

* Can I use your plugin with a picture instead of a map?

Yes: Use `[leaflet-image source="path/to/image/file.jpg"]`.  See screenshots 3 - 5 for help setting that up. 

* Can I use my own self-hosted Leaflet files?

Yes: It's been added to the dashboard options! 

* How can I add a link to a marker?

Use the marker format `[leaflet-marker]Click here![/leaflet-marker]` and add a hyperlink like you normally would with the WordPress editor.

* Can I add a line to the map?

Use the line format `[leaflet-line]` with attributes `latlngs` or `addresses` separated by semi-colons to draw a line: `[leaflet-line addresses="Sayulita; Puerto Vallarta"]`.

Shoot me a question [@bozdoz](http://www.twitter.com/bozdoz/).

== Screenshots ==

1. Put the shortcode into the post.
2. See the shortcode play out on the front end.
3. For `[leaflet-image]` upload an image, and copy the URL from the right-hand side
4. For `[leaflet-image]` paste that image URL into an attribute titled `source`: example: `source="http://lorempixel.com/1000/1000/"`.
5. See the `[leaflet-image]` on the front end.
6. If you use `[leaflet-marker draggable=true]`, then you can drag the marker where you want it, open a developers console, and see the specific shortcode to use.
7. You can specify the URL of your leaflet files, if you don't want to use the CDN url.

== Changelog ==

= 1.10 =
* Added lines to the map, thanks to [@Remigr](https://github.com/Remigr)!

= 1.9 =
* Added ability to use hyperlinks in marker messages.

= 1.8 =
* Added ability to self-host leaflet files.

= 1.7 =
* Uploaded 1.6 to subversion incorrectly!

= 1.6 =
* Important fix to conflicts with other plugins!

= 1.5 =
* Some helpful js fixes for multiple window onload functions, and added the `leaflet-image` shortcode!

= 1.4 =
* Some fixes for Google geocoding, and switched cookies to db options.

= 1.3 =
* Added cookies for Google geocoding to cut back on requests.

= 1.2 =
* Added geocoding to map: `[leaflet-map address="halifax, ns"]`.

= 1.1 =
* Added messages to markers.

= 1.0 =
* First Version. Basic map creation and marker creation.

== Upgrade Notice ==

= 1.10 =
Added lines to the map, thanks to [@Remigr](https://github.com/Remigr)!

= 1.9 =
Added ability to use hyperlinks in marker messages.

= 1.8 =
Added ability to self-host leaflet files.

= 1.7 =
Fix to the poor upload of 1.6

= 1.6 =
Removed windows onload functions and created a construct and init js file for initiating the maps when Leaflet is ready (other plugins were overwriting windows.onload).

= 1.5 =
Improved stability for multiple plugins with windows onload functions.

= 1.4 =
Stable Google geocoding.

= 1.3 =
Added cookies for Google geocoding to cut back on requests.

= 1.2 =
Added Google geocoding.

= 1.1 =
Added messages to markers. Tested with 4.0.

= 1.0 =
First Version. Tested with 3.8.1.