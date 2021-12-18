<?php
/**
 * Marker Shortcode
 *
 * Use with [leaflet-marker ...]
 * 
 * @category Shortcode
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.shortcode.php';

/**
 * Leaflet Marker Shortcode Class
 */
class Leaflet_Marker_Cluster_Group_Shortcode extends Leaflet_Shortcode
{
    /**
     * Get Script for Shortcode
     * 
     * @param string $atts    could be an array
     * @param string $content optional
     * 
     * @return null
     */
    protected function getHTML($atts='', $content=null)
    {
        if (!empty($atts)) {
            extract($atts, EXTR_SKIP);
        }

        wp_enqueue_script('leaflet_markercluster_js');
        wp_enqueue_style('leaflet_markercluster_css');
        wp_enqueue_style('leaflet_markercluster_default_css');

        ob_start();
        ?>/*<script>*/
      debugger
var map = window.WPLeafletMapPlugin.getCurrentMap();
var markers = L.markerClusterGroup();
markers.addLayers([L.marker([44.65986223989897, -63.590927124023445]), L.marker([44.645452487688495, -63.60431671142579]), L.marker([44.65155875202518, -63.58612060546876])]);
map.addLayer(markers);
        <?php
        
        $script = ob_get_clean();

        return $this->wrap_script($script, 'WPLeafletMarkerClusterGroupShortcode');
    }
}
