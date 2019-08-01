<?php
/**
 * KML/KMZ Shortcode
 *
 * Use with [leaflet-kml src="..."]
 *
 * PHP Version 5.5
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
    public static $type = 'kml';
    
    /**
     * Default src
     * 
     * @var string $default_src
     */
    public static $default_src = '';
}