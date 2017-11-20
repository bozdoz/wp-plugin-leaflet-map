<?php
/**
* KML/KMZ Shortcode
*
* Use with [leaflet-kml src="..."]
*
* @param array $atts        user-input array
* @return string JavaScript
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

include_once(LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.geojson-shortcode.php');

class Leaflet_Kml_Shortcode extends Leaflet_Geojson_Shortcode {
	/**
	* @var string $type how leaflet renders the src
	*/
	public static $type = 'kml';
	/**
	* @var string $default_src default src
	*/
	public static $default_src = 'https://cdn.rawgit.com/mapbox/togeojson/master/test/data/polygon.kml';
}