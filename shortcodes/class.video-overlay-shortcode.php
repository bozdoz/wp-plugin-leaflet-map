<?php
/**
 * Video Overlay Shortcode
 *
 * Use with [leaflet-video-overlay ...]
 *
 * @category Shortcode
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.image-overlay-shortcode.php';

class Leaflet_Video_Overlay_Shortcode extends Leaflet_Image_Overlay_Shortcode
{
    protected $type = 'L.videoOverlay';
    protected $default_src = 'https://labs.mapbox.com/bites/00188/patricia_nasa.webm';
    protected $default_bounds = "32,-130;13,-100";
}
