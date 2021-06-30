<?php
/**
 * GPX Shortcode
 *
 * Use with [leaflet-gpx src="..."]
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
 * Leaflet GPX Shortcode
 */
class Leaflet_Gpx_Shortcode extends Leaflet_Geojson_Shortcode
{
    /**
     * How leaflet renders the src
     * 
     * @var string $type
     */
    protected $type = 'gpx';

    /**
     * Default src
     * 
     * @var string $default_src
     */
    protected $default_src = 'https://cdn.jsdelivr.net/gh/mapbox/togeojson@master/test/data/run.gpx';
}