<?php
/**
 * Image Shortcode
 *
 * Displays map with [leaflet-image src="path/to/image.jpg"] 
 *
 * JavaScript equivalent : L.imageOverlay('path/to/image.jpg');
 *
 * @category Shortcode
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.map-shortcode.php';

/**
 * Leaflet Image Shortcode Class
 */
class Leaflet_Image_Shortcode extends Leaflet_Map_Shortcode
{
    /**
     * Get HTML for shortcode
     * 
     * @param string $atts    or Array
     * @param string $content produced by adding atts to JavaScript
     * 
     * @return string HTML script
     */
    protected function getHTML($atts='', $content=null)
    {
        extract($this->getAtts($atts));

        /* only required field for image map (src/source) */
        $src = empty($src) ? '' : $src;
        $source = empty($source) ? 'https://picsum.photos/1000/1000/' : $source;
        $source = empty($src) ? $source : $src;

        ob_start(); ?>/*<script>*/
var options = L.Util.extend({}, {
        attributionControl: false
    }, <?php echo $map_options; ?>, {
        crs: L.CRS.Simple
    });
var image_src = '<?php echo htmlspecialchars($source, ENT_QUOTES); ?>';
var img = new Image();
var zoom = <?php echo $zoom; ?>;
var map = window.WPLeafletMapPlugin.createImageMap(options).setView([0, 0], zoom);
img.onload = function() {
    var h = img.height,
        w = img.width,
        projected_zoom = zoom + 1,
        southWest = map.unproject([-w, h], projected_zoom),
        northEast = map.unproject([w, -h], projected_zoom),
        bounds = new L.LatLngBounds(southWest, northEast);
    L.imageOverlay( image_src, bounds ).addTo( map );
    map.setMaxBounds(bounds);
};
img.src = image_src;<?php

        $script = ob_get_clean();

        return $this->getDiv($height, $width) . $this->wrap_script($script, 'WPLeafletImageShortcode');
    }
}
