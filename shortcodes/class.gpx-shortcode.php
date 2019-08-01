<?php
/**
 * GPX Shortcode
 *
 * Use with [leaflet-gpx src="..."]
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
 * Leaflet GPX Shortcode
 */
class Leaflet_Gpx_Shortcode extends Leaflet_Geojson_Shortcode
{
    /**
     * How leaflet renders the src
     * 
     * @var string $type
     */
    public static $type = 'gpx';

    /**
     * Default src
     * 
     * @var string $default_src
     */
    public static $default_src = '';
}