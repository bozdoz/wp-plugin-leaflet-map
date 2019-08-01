<?php
/**
 * Polygon Shortcode
 *
 * Use with [leaflet-polygon ...]
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

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.line-shortcode.php';

/**
 * Leaflet Polygon Shortcode Class
 */
class Leaflet_Polygon_Shortcode extends Leaflet_Line_Shortcode
{
    /**
     * How leaflet renders the src
     * 
     * @var string $type 
     */
    public static $type = 'polygon';
}