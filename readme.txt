=== Leaflet Map ===
Author: bozdoz
Author URI: https://bozdoz.com
Plugin URI: https://wordpress.org/plugins/leaflet-map/
Contributors: bozdoz, remigr, gerital, sal0max, thibault-barrat
Donate link: https://www.paypal.me/bozdoz
Tags: leaflet, map, mobile, javascript, openstreetmap, mapquest, interactive
Requires at least: 4.6
Tested up to: 5.8.1
Version: 3.0.4
Stable tag: 3.0.4
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Interactive maps and markers on your posts and pages with simple shortcodes.

== Description ==

Add a map generated with [LeafletJS](http://leafletjs.com/): an open-source JavaScript library for mobile-friendly interactive maps. Map tiles are provided by default through [OpenStreetMap](http://www.openstreetmap.org/), or [MapQuest](https://www.mapquest.ca/) (with an app key).  Can be set per map with shortcode attributes or through the dashboard settings.

= Maps =

Simply create a **map** with:

`[leaflet-map]`

Lookup an address with:

`[leaflet-map address="chicago"]`

Know the latitude and longitude of a location? Use them (and a zoom level) with:

`[leaflet-map lat=44.67 lng=-63.61 zoom=5]`

Add a **marker** under your map shortcode, like so:

`
[leaflet-map]
[leaflet-marker]
`

Want more? Make more (and fit the map to contain all of them):

`
[leaflet-map fitbounds]
[leaflet-marker address="tokyo"]
[leaflet-marker address="oslo"]
[leaflet-marker address="cairo"]
[leaflet-marker address="toronto"]
`

You can even add **popups** (to any shape) with their names:

`
[leaflet-map fitbounds]
[leaflet-marker address="tokyo"]Tokyo[/leaflet-marker]
[leaflet-marker address="oslo"]Oslo[/leaflet-marker]
...
`

Add a link to the popup messages the same way you would add any other link with the WordPress editor.

= Other Shapes, GeoJSON, and KML =

Add a line to the map by adding `[leaflet-line]`. You can specify the postions with a list separated by semi-colon `;` or bar `|` using lat/lng: `[leaflet-line latlngs="41, 29; 44, 18"]` or addresses: `[leaflet-line addresses="Istanbul; Sarajevo"]`, or x/y coordinates for image maps.

Add a circle to the map by adding `[leaflet-circle]`. You can specify the position using `lat` and `lng` and the radius in meters using `radius`. You can also customize the style using [Leaflet's Path options](https://leafletjs.com/reference-1.3.4.html#path-option). Example: `[leaflet-circle message="max distance" lng=5.117909610271454 lat=52.097914814706094 radius=17500 color="#0DC143" fillOpacity=0.1]`.

Or you can add a geojson shape via a url (make sure you are allowed to access it if it's not hosted on your own server): `[leaflet-geojson src="https://example.com/path/to.geojson"]`.  Add custom popups with field names; try out the default src file and fields like so: 

`
[leaflet-map fitbounds]
[leaflet-geojson]{name}[/leaflet-geojson]
`

`name` is a property on that GeoJSON, and it can be accessed with curly brackets and the property name.

= Image Maps =

Alternatively, you could use a plain image for visitors to zoom and pan around with `[leaflet-image src="path/to/image/file.jpg"]`.  See screenshots 3 - 5 for help setting that up.

= More =

Check out other examples on the Shortcode Helper page in the Leaflet Map admin section.

Check out the **source code** and **more** details on [GitHub](https://github.com/bozdoz/wp-plugin-leaflet-map)!

== Installation ==

1. Choose to add a new plugin, then click upload
2. Upload the leaflet-map zip
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Use the shortcodes in your pages or posts: e.g. `[leaflet-map]` and `[leaflet-marker]`

== Frequently Asked Questions ==

= Can I have an SVG Marker? =

Yes! Convert the default marker into an svg with a shortcode like this: `[leaflet-marker svg color="white" iconClass="fab fa-wordpress-simple" background="red"]` The `iconClass` is perfect for adding a [font-awesome](https://fontawesome.com/icons?d=gallery) icon.

= How do I change the style for lines/geojson? =

Pass the style attributes to the respective shortcodes (see all options on [LeafletJS](http://leafletjs.com/reference-1.0.3.html#path)):

`[leaflet-line color="red" weight=10 dasharray="2,15" addresses="Halifax, NS; Tanzania" classname=marching-ants]`

= Can I add geojson? =

Yes, just give it a source URL: `[leaflet-geojson src="https://example.com/path/to.geojson"]` It will also support leaflet geojson styles or geojson.io styles. Add a popup message with `[leaflet-geojson popup_text="hello!"]`, or add HTML by adding it to the content of the shortcode: `[leaflet-geojson]<a href="#">Link here, or use text from a feature property, like {title}</a>[/leaflet-geojson]` or identify a geojson property with `popup_property`, and each shape will use its own popup text if available.

= Can I add kml/gpx? =

Sure!? Use the same attributes as leaflet-geojson (above), but use the `[leaflet-kml]` or `[leaflet-gpx]` shortcode.

= Can I add a message to a marker? =

Yes: `[leaflet-marker visible]Hello there![/leaflet-marker]`, where visible designates if it is visible on page load. Otherwise it is only visible when clicked.

= Can I use your plugin with a picture instead of a map? =

Yes: Use `[leaflet-image src="path/to/image/file.jpg"]`.  See screenshots 3 - 5 for help setting that up.

= Can I use my own self-hosted Leaflet files? =

Yes: Add your custom URL to the options in the admin page.

= How can I add a link to a marker? =

Use the marker format `[leaflet-marker]Click here![/leaflet-marker]` and add a hyperlink like you normally would with the WordPress editor.

= Additional questions? =

For more FAQs, please visit the [FAQ section on GitHub here](https://github.com/bozdoz/wp-plugin-leaflet-map#frequently-asked-questions).

== Screenshots ==

1. Put the shortcode into the post.
2. See the shortcode play out on the front end.
3. For `[leaflet-image]` upload an image, and copy the URL from the right-hand side
4. For `[leaflet-image]` paste that image URL into an attribute titled `source`: example: `src="https://picsum.photos/1000/1000/"`.
5. See the `[leaflet-image]` on the front end.
6. If you use `[leaflet-marker draggable]`, then you can drag the marker where you want it, open a developers console, and see the specific shortcode to use.
7. Add geojson via URL: `[leaflet-geojson src="https://example.com/path/to.geojson"]`
8. MapQuest requires an app key, get it from their website; alternatively, you can use OpenStreetMap as a free tile service (remember to add an attribution where necessary).

== Changelog ==

= 3.0.4 =
* Fixes markers so that they can accept `0` as a value for x/y and lat/lng coordinates

= 3.0.3 =
* Fixes using `popupAnchor` without passing `iconUrl`

= 3.0.2 =
* Another fix for commas in float coordinates.
* Fix validation of tile urls.
* Fix for php 5.6 using static function methods.

= 3.0.1 =
* Fixes some issues with float coordinates that use commas instead of decimals
* Fixes some quotes in addresses for geocoding

= 3.0.0 =
* Fixes security issues in admin and in shortcode attributes.  Escapes and filters many inputs.

= 2.23.3 =
* changes 'leaflet_map_enqueue' action to fire for each map

= 2.23.2 =
* actual bugfix to multiple or missing enqueue map scripts

= 2.23.1 =
* possible bugfix to failing to enqueue map when shortcode rendered 

= 2.23.0 =
* Added iconUrl to leaflet-geojson shortcode.
* bugfix to number inputs in admin accepting either integers or decimals but not both (couldn't switch types)

= 2.22.1 =
* hotfix to tile url attributions not being numeric and stripping slashes.

= 2.22.0 =
* Adds tilesize, mapid, accesstoken, zoomoffset, nowrap to leaflet-map shortcode and default settings; helpful for mapbox tile urls

= 2.21.0 =
* Fixes issues with tilelayers when min_zoom and max_zoom are identical and detect_retina is true
* Adds (advanced) default liquid-like filter to template tags: [leaflet-geojson]{Properties.Name | default: No Name}[/leaflet-geojson]

= 2.20.0 =
* Adds tap and !tap option to [leaflet-map]
* Adds (advanced) raw filter syntax for map options: [leaflet-map dragging="{!L.Browser.mobile | raw}"]

= 2.19.1 =
* Bumps leaflet version to 1.7.1
* Removes "\r\n" from default attribution
* Uses min and max zoom in tileurl as well as map

= 2.19.0 =
* Adds [leaflet-scale] and global option in admin
* Removes unnecessary console.log

= 2.18.0 =
* Adds table-view to leaflet-geojson: [leaflet-geojson table-view]
* Creates maps in order their containers are rendered (no unique ids)

= 2.17.3 =
* Bugfix to detect retina breaking MapQuest maps since 2.17.0

= 2.17.2 =
* Unparenthesized ternaries are deprecated in php 7.4

= 2.17.1 =
* Lazy-loading svg and geojson scripts so that it can wait for Leaflet to be loaded under some circumstances (deferred scripts)

= 2.17.0 =
* Adds `detect-retina` to plugin options and `leaflet-map` shortcode
* Makes shortcode in excerpts conditional (enable it in admin->leaflet-map->settings)

= 2.16.2 =
* Fix to wpautop by removing spaces in javascript

= 2.16.1 =
* Updates default Leaflet to 1.6.0
* Adds optional circle markers as [leaflet-geojson circleMarker]
* removes random unique map id's for caching purposes

= 2.16.0 =
* allow author roles to see shortcodes
* adds [leaflet-polygon] shortcode
* makes map ids unique; removes auto-incremented map counts

= 2.15.0 =
* Updates rawgit URL's to use jsdelivr, unpkg, and githubusercontent.com
* Change logo to use FontAwesome SVG
* Adds referer to file_get_contents, in case curl is disabled
* Updates default Leaflet to 1.5.1
* Fixes reset default values button in settings
* Requires at least WordPress 4.6
* ToGeoJSON library URL is customizable

= 2.14.0 =
* Fix `visible` att in popups.
* Adds nested property accessors to geojson popup text: `{attributes.email}`
* Adds negation to shortcode attributes with an exclamation mark: e.g. Disable all interaction with `[leaflet-map address="las vegas" !boxZoom !doubleClickZoom !dragging !keyboard !scrollwheel !attribution !touchZoom]`
* Fix case-sensitive `boxZoom`
* Add `touchZoom` to map attributes
* Fix icon-related anchor attributes (numbers instead of strings)

= 2.13.0 =
* Updated LeafletJS to 1.4.0
* Replaced `fit_markers` with `fitbounds`, which now fits all shapes in map view

= 2.12.0 =
* Re-added Google Geocoder (optional), since they forced billing accounts
* Fixed links in shortcode helper page
* Fixed issue with marker popups that had single quotes
* Loading leaflet scripts and styles only when a map shortcode is used
* Added fitbounds to leaflet-map (to replace fit_markers someday)

= 2.11.5 =
* Added assumed-boolean attributes to all shortcodes; ex: `[leaflet-marker draggable svg]` would be the same as `[leaflet-marker draggable=1 svg=1]`

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

= 3.0.4 =
Fixes markers so that they can accept `0` as a value for x/y and lat/lng coordinates

= 3.0.3 =
Fixes using `popupAnchor` without passing `iconUrl`

= 3.0.2 =
Another fix for commas in float coordinates. Fix validation of tile urls. Fix for php 5.6 using static function methods.

= 3.0.1 =
Fixes some issues with float coordinates that use commas instead of decimals, and fixes some quotes in addresses for geocoding

= 3.0.0 =
Fixes security issues in admin and in shortcode attributes.  Escapes and filters many inputs.

= 2.23.3 =
Changes 'leaflet_map_enqueue' action to fire for each map

= 2.23.2 =
Actual bugfix to multiple or missing enqueue map scripts

= 2.23.1 =
Possible bugfix to ensuring javascript is enqueued when map is rendered

= 2.23.0 =
Minor bugfix to number-type inputs in admin that couldn't switch types between decimals and integers

= 2.21.0 =
Fixes issues with tilelayers when min_zoom and max_zoom are identical and detect_retina is true

= 2.19.1 =
Bumps leaflet version to 1.7.1
Removes "\r\n" from default attribution
Uses min and max zoom in tileurl as well as map

= 2.18.0 =
Changes the way maps are rendered: Now creates map containers before creating maps, in the same order the containers are rendered. This should help with ajax, caching, and script-altering plugins.

= 2.17.3 =
Bugfix to detect retina breaking MapQuest maps since 2.17.0

= 2.17.1 =
Lazy-loading svg and geojson scripts so that it can wait for Leaflet to be loaded under some circumstances (deferred scripts)

= 2.17.0 =
Makes shortcode in excerpts conditional (enable it in admin->leaflet-map->settings)

= 2.16.2 =
Fixes missing maps by removing spaces in the JavaScript which some themes turn into paragraphs

= 2.16.1 =
Updates default Leaflet to 1.6.0
Changes map generation from a pre-defined randomly identified div element to a JavaScript generated div which is created (insertBefore) each inserted script tag

= 2.16.0 =
Adds [leaflet-polygon]
Allows author roles to see shortcodes page

= 2.15.0 =
Adds referer to file_get_contents, in case curl is disabled
Updates default Leaflet to 1.5.1
Fixes reset default values button in settings
Requires at least WordPress 4.6

= 2.12.0 =
Fixed links in shortcode helper page; fixed issue with marker popups that had single quotes; loading leaflet scripts and styles only when a map shortcode is used

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
