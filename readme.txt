=== Leaflet Map ===
Author: bozdoz
Author URI: https://www.twitter.com/bozdoz/
Plugin URI: https://wordpress.org/plugins/leaflet-map/
Contributors: bozdoz, Remigr, nielsalstrup, jeromelebleu, gerital, peteruithoven
Donate link: https://www.paypal.me/bozdoz
Tags: leaflet, map, mobile, javascript, openstreetmap, mapquest, interactive
Requires at least: 3.0.1
Tested up to: 4.9.8
Version: 2.11.4
Stable tag: 2.11.4
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Interactive maps and markers on your posts and pages with simple shortcodes.

== Description ==

Add a map generated with [LeafletJS](http://leafletjs.com/): an open-source JavaScript library for mobile-friendly interactive maps. Map tiles are provided by default through [OpenStreetMap](http://www.openstreetmap.org/), or [MapQuest](https://www.mapquest.ca/) (with an app key).  Can be set per map with shortcode attributes or through the dashboard settings.

= Maps =

Height, width, latitude, longitude and zoom are the basic attributes: 

`[leaflet-map height=250 width=250 lat=44.67 lng=-63.61 zoom=5]`

However, you can also just give it an **address**, and Google (by default) will look it up for you:

`[leaflet-map address="Oslo, Norway"]`

The default URL requires attribution by its terms of use.  If you want to change the URL, you may define a new attribution, or remove the attribution.  You can define this site-wide in the admin, or you can set this per map in the shortcode (0 for disabled):

`[leaflet-map attribution=0]`

OR 

`[leaflet-map attribution="Copyright @bozdoz"]`

Look at other examples on the Shortcode Helper in the Leaflet Map admin section.

= Markers =

Add a marker to any map by adding `[leaflet-marker]` after any `[leaflet-map]` shortcode.  You can adjust the lat/lng in the same way, as well as some other basic functionality (popup message, draggable, visible on load).  Also, if you want to add a link to a marker popup, use this syntax: `[leaflet-marker]Message here: click here[/leaflet-marker]` and add a link like you normally would with the WordPress editor.

= Lines and other Shapes =

Add a line to the map by adding `[leaflet-line]`. You can specify the postions with a list separated by semi-colon `;` or bar `|` using lat/lng: `[leaflet-line latlngs="41, 29; 44, 18"]` or addresses: `[leaflet-line addresses="Istanbul; Sarajevo"]`, or x/y coordinates for image maps.

Add a circle to the map by adding `[leaflet-circle]`. You can specify the position using `lat` and `lng` and the radius in meters using `radius`. You can also customize the style using [Leaflet's Path options](https://leafletjs.com/reference-1.3.4.html#path-option). Example: `[leaflet-circle message="max distance" lng=5.117909610271454 lat=52.097914814706094 radius=17500 color="#0DC143" fillOpacity=0.1]`.

Or you can add a geojson shape via a url (work in progress): `[leaflet-geojson src="https://example.com/path/to.geojson"]`

= Image Maps =

Alternatively, you could use a plain image for visitors to zoom and pan around with `[leaflet-image src="path/to/image/file.jpg"]`.  See screenshots 3 - 5 for help setting that up.

Check out the source code on [GitHub](https://github.com/bozdoz/wp-plugin-leaflet-map)!

Shoot me a question [@bozdoz](https://www.twitter.com/bozdoz/).

== Installation ==

1. Choose to add a new plugin, then click upload
2. Upload the leaflet-map zip
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Use the shortcodes in your pages or posts: e.g. `[leaflet-map]` and `[leaflet-marker]`

== Frequently Asked Questions ==

= Can I have an SVG Marker? =

Yes! Convert the default marker into an svg with a shortcode like this: `[leaflet-marker svg="true" color="white" iconClass="fab fa-wordpress-simple" background="red"]` The `iconClass` is perfect for adding a [font-awesome](https://fontawesome.com/icons?d=gallery) icon.

= How do I change the style for lines/geojson? =

Pass the style attributes to the respective shortcodes (see all options on [LeafletJS](http://leafletjs.com/reference-1.0.3.html#path)):

`[leaflet-line color="red" weight=10 dasharray="2,15" addresses="Halifax, NS; Tanzania" classname=marching-ants]`

= My map now says direct tile access has been discontinued (July 11, 2016); can you fix it? =

Yes. Update to the newest plugin version, and reset defaults in settings.  You can choose to use MapQuest by signing up and supplying an app key, or use the default OpenStreetMap tiles (with attribution).  See Screenshot 8.

= Can I add geojson? =

Yes, just give it a source URL: `[leaflet-geojson src="https://example.com/path/to.geojson"]` It will also support leaflet geojson styles or geojson.io styles. Add a popup message with `[leaflet-geojson popup_text="hello!"]`, or add HTML by adding it to the content of the shortcode: `[leaflet-geojson]<a href="#">Link here, or use text from a feature property, like {title}</a>[/leaflet-geojson]` or identify a geojson property with `popup_property`, and each shape will use its own popup text if available.

= Can I add kml/gpx? =

Sure!? Use the same attributes as leaflet-geojson (above), but use the `[leaflet-kml]` or `[leaflet-gpx]` shortcode.

= Can I add a message to a marker? =

Yes: `[leaflet-marker message="Hello there!" visible="true"]`, where visible designates if it is visible on page load. Otherwise it is only visible when clicked.

= Can I use your plugin with a picture instead of a map? =

Yes: Use `[leaflet-image src="path/to/image/file.jpg"]`.  See screenshots 3 - 5 for help setting that up. 

= Can I use my own self-hosted Leaflet files? =

Yes: It's been added to the dashboard options! 

= How can I add a link to a marker? =

Use the marker format `[leaflet-marker]Click here![/leaflet-marker]` and add a hyperlink like you normally would with the WordPress editor.

= Can I add a line to the map? =

Use the line format `[leaflet-line]` with attributes `latlngs` or `addresses` separated by semi-colons to draw a line: `[leaflet-line addresses="Sayulita; Puerto Vallarta"]`.

= Can I add my own attributions to custom tile layers? =

Yes: use the keyword `attribution` in your shortcode (semi-colon separated list of attributions): `[leaflet-map attribution="Tiles courtesy of MapBox; Locations contributed by viewers"]`

== Screenshots ==

1. Put the shortcode into the post.
2. See the shortcode play out on the front end.
3. For `[leaflet-image]` upload an image, and copy the URL from the right-hand side
4. For `[leaflet-image]` paste that image URL into an attribute titled `source`: example: `src="http://lorempixel.com/1000/1000/"`.
5. See the `[leaflet-image]` on the front end.
6. If you use `[leaflet-marker draggable=true]`, then you can drag the marker where you want it, open a developers console, and see the specific shortcode to use.
7. Add geojson via URL: `[leaflet-geojson src="https://example.com/path/to.geojson"]`
8. MapQuest requires an app key, get it from their website; alternatively, you can use OpenStreetMap as a free tile service (remember to add an attribution where necessary).

== Changelog ==

= 2.11.4 =
* Fix to a race condition issue with custom scripts changing leaflet rendering methods

= 2.11.3 =
* Fix to an issue with rendering circles

= 2.11.2 =
* Allow scripts to be deferred, and still render maps reliably
* Add a circle to the map by adding `[leaflet-circle]`

= 2.11.1 =
* Added Dockerfiles to github
* Made OpenStreetMap default geocoder in light of new Google API payment plans

= 2.11.0 =
* Added Popup Anchor for custom markers
* Added SVG Markers
* Added actions and filters

= 2.10.1 =
* Fix for plugin settings not being included (somehow)

= 2.10.0 =
* Added functions for translating text
* Added string interpolation for GeoJSON popups to use feature properties (thanks to [@geraldo](https://github.com/geraldo))

= 2.9.1 =
* Fixes for PHP 7.2: made all method arguments identical 
* Added minified JavaScript files for site speed

= 2.8.6 =
* Added [leaflet-gpx] for GPX format

= 2.8.6 =
* Fix image shortcode ratio

= 2.8.5 =
* Added missing files from 2.8.4

= 2.8.4 =
* Fixed issues with css and js CDN; removed version from querystring
* Split admin into new class

= 2.8.3 =
* Fix to [leaflet-kml]
* Standardized `src` in leaflet-image and leaflet-geojson/kml

= 2.8.2 =
* Fix to image maps
* Fixes to geocoder
* Added multi-line popups to markers and geojson/kml

= 2.8.1 =
* Code cleanup
* Added server-side and client-side methods to prevent WordPress from adding paragraph tags within shortcode tags

= 2.8.0 =
* Added Fit Markers to settings and map shortcode: [leaflet-map fit_markers=1]
* Moved geojson/kml popup text to the shortcode content instead of a property so that you can use links or other HTML

= 2.7.8 =
* update default Leaflet version (1.1.0 from 1.0.3)

= 2.7.7 =
* added default lat/lngs in the admin; and reordered admin fields to be more user friendly

= 2.7.6 =
* added optional cURL to get geolocations if file_get_contents is not allowed on a server (note: cURL needs to be enabled, obviously)

= 2.7.5 =
* fixed filter_var_array throwing errors in old PHP too

= 2.7.4 =
* Added settings link to plugins page

= 2.7.3 =
* Fix to array_filter on some PHP versions.

= 2.7.2 =
* Fix to possible JavaScript error "Unexpected token <"; only happened when a plugin/theme used `wpautop`; fix was to remove spaces.

= 2.7.1 =
* Removed unnecessary map counts;

= 2.7.0 =
* Added basic marker icon support (with attributes "iconUrl", "iconAnchor", etc.);

= 2.6.0 =
* Changes to map creation which may solve an occasional marker creation JavaScript error 
* Added more attributes to marker shortcode (draggable, title, alt, opacity)
* Added doubleClickZoom global, database option to globally disable double click zooming (by default), because it's more inline with disabling scroll zooming by default. Box zooming on the other hand is more intentional

= 2.5.0 =
* Some improvements to the codebase; 
* added the same styling options for lines as there are for geojson; 
* added popups to lines, as there are for markers;
* added an example to the shortcode admin page for the style attributes on lines;
* added code and another example for disabling all map interactions (all zooms, keyboard, etc);

= 2.4.0 =
* Added Leaflet 1.0.2 scripts by default (works!);

= 2.3.0 =
* Added KML support `[leaflet-kml]`;

= 2.2.0 =
* Added popup_text and popup_property to leaflet-geojson to bind popups via a shortcode property or geojson properties

= 2.1.0 =
* Added Leaflet and GeoJSON.io styles to geojson shortcode

= 2.0.2 =
* OpenStreetMap.org has an SSL certificate (osm.org didn't); not a significant upgrade.

= 2.0.1 =
* Changed ajax request to be compatible with some versions of IE.

= 2.0 =
* Needed to add support for a MapQuest app key, since they discontinued the direct access of their tiles on July 11, 2016; added an alternate OpenStreetMap tile URL as the new default. Remember to reset options to default values!

= 1.16 =
* Added bare geojson support

= 1.15 =
* Removed shortcode brackets from leaflet-marker shortcode

= 1.14 =
* Fixed slashes in optional map attribution

= 1.13 =
* Added new geocoder options (thanks to [@nielsalstrup](https://github.com/nielsalstrup) for DAWA and [@jeromelebleu](https://github.com/nielsalstrup) for OSM)

= 1.12 =
* Added htmlspecialchars in admin.php, and custom attributions. Bugfix : removed position in admin menu so it doesn't overwrite or be overwritten (thanks to [@ziodave](https://github.com/ziodave))

= 1.11 =
* Added HTTPS support.

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

= 2.11.4 =
Fix to a race condition issue with custom scripts changing leaflet rendering methods

= 2.11.3 =
Fix for rendering circles (fix to 2.11.2)

= 2.10.1 =
Fix for plugin settings not being included (somehow)

= 2.9.1 =
Fix for PHP7.2; added minified JavaScript files

= 2.8.6 =
Added [leaflet-gpx] for GPX format

= 2.8.6 =
Fix image shortcode ratio

= 2.8.5 =
Fix to missing files in 2.8.4

= 2.8.4 =
Fixed issues with css and js CDN; removed version from querystring

= 2.8.3 =
Fixed issues with leaflet-kml

= 2.8.2 =
Fixed issues with image maps and geocoder addresses

= 2.7.6 =
added optional cURL to get geolocations if file_get_contents is not allowed on a server (cURL needs to be enabled, obviously)

= 2.7.5 =
fixed filter_var_array throwing errors in old PHP

= 2.7.3 =
Fixed array_filter on some older PHP versions

= 2.7.2 =
Fixed possible JavaScript error "Unexpected token <"; fix was to remove spaces

= 2.6.0 =
Changes to map creation which may solve an occasional marker creation JavaScript error 

= 2.0.2 =
OpenStreetMap.org has an SSL certificate (osm.org didn't)

= 2.0.1 =
GeoJSON ajax requests now work with Internet Explorer (some versions)

= 2.0 =
MapQuest tiles will no longer work without an app key!

= 1.15 =
Fixed incompatibility with plugins that execute recursive shortcodes

= 1.14 =
Fixed slashes in optional map attribution

= 1.12 =
Added htmlspecialchars in admin.php, and custom attributions. Bugfix : removed position in admin menu so it doesn't overwrite or be overwritten (thanks to [@ziodave](https://github.com/ziodave))

= 1.6 =
Removed windows onload functions and created a construct and init js file for initiating the maps when Leaflet is ready (other plugins were overwriting windows.onload).

= 1.5 =
Improved stability for multiple plugins with windows onload functions.

= 1.0 =
First Version. Tested with 3.8.1.