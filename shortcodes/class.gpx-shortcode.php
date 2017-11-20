<?php
/**
* GPX Shortcode
*
* Use with [leaflet-gpx src="..."]
*
* @param array $atts        user-input array
* @return string JavaScript
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

include_once(LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.geojson-shortcode.php');

class Leaflet_Gpx_Shortcode extends Leaflet_Geojson_Shortcode {
	/**
	* @var string $type how leaflet renders the src
	*/
	public static $type = 'gpx';
}