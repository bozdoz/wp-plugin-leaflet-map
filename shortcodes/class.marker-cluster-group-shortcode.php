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
      //debugger
      console.log('set WPLeafletMapPlugin.markerGroupConstructor = L.markerClusterGroup');
      window.WPLeafletMapPlugin.markerGroupConstructor = L.markerClusterGroup;
      // and remove previous group from map to ensure creation of a new one
      var mapid = window.WPLeafletMapPlugin.maps.length;
      window.WPLeafletMapPlugin.markergroups[mapid] = null;
        <?php
        
        $script = ob_get_clean();

        return $this->wrap_script($script, 'WPLeafletMarkerClusterGroupShortcode');
    }
}
