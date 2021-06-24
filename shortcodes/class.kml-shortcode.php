<?php
/**
 * KML/KMZ Shortcode
 *
 * Use with [leaflet-kml src="..."]
 * 
 * @category Shortcode
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.geojson-shortcode.php';

/**
 * Leaflet KML Shortcode
 */
class Leaflet_Kml_Shortcode extends Leaflet_Geojson_Shortcode
{
    
    /**
     * How leaflet renders the src
     * 
     * @var string $type 
     */
    protected $type = 'kml';
    
    /**
     * Default src
     * 
     * @var string $default_src
     */
    protected $default_src = 'https://cdn.jsdelivr.net/gh/mapbox/togeojson@master/test/data/polygon.kml';
}