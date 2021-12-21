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
     * @param string $atts could be an array
     * @param string $content optional
     *
     * @return null
     */
    protected function getHTML($atts = '', $content = null)
    {
        if (!empty($atts)) {
            extract($atts, EXTR_SKIP);
        }

        wp_enqueue_script('leaflet_markercluster_js');
        wp_enqueue_style('leaflet_markercluster_css');
        wp_enqueue_style('leaflet_markercluster_default_css');

        $options = array(
            'showCoverageOnHover' => isset($showcoverageonhover) ? $showcoverageonhover : null,
            'zoomToBoundsOnClick' => isset($zoomtoboundsonclick) ? $zoomtoboundsonclick : null,
            'spiderfyOnMaxZoom' => isset($spiderfyonmaxzoom) ? $spiderfyonmaxzoom : null,
            'removeOutsideVisibleBounds' => isset($removeoutsidevisiblebounds) ? $removeoutsidevisiblebounds : null,
            'animate' => isset($animate) ? $animate : null,
            'animateAddingMarkers' => isset($animateaddingmarkers) ? $animateaddingmarkers : null,
            'disableClusteringAtZoom' => isset($disableclusteringatzoom) ? $disableclusteringatzoom : null,
            'maxClusterRadius' => isset($maxclusterradius) ? $maxclusterradius : null,
            'singleMarkerMode' => isset($singlemarkermode) ? $singlemarkermode : null,
            'spiderfyDistanceMultiplier' => isset($spiderfydistancemultiplier) ? $spiderfydistancemultiplier : null,
            'chunkedLoading' => isset($chunkedloading) ? $chunkedloading : null,
            'chunkInterval' => isset($chunkinterval) ? $chunkinterval : null,
            'chunkDelay' => isset($chunkdelay) ? $chunkdelay : null,
        );

        $args = array(
            'showCoverageOnHover' => FILTER_VALIDATE_BOOLEAN,
            'zoomToBoundsOnClick' => FILTER_VALIDATE_BOOLEAN,
            'spiderfyOnMaxZoom' => FILTER_VALIDATE_BOOLEAN,
            'removeOutsideVisibleBounds' => FILTER_VALIDATE_BOOLEAN,
            'animate' => FILTER_VALIDATE_BOOLEAN,
            'animateAddingMarkers' => FILTER_VALIDATE_BOOLEAN,
            'disableClusteringAtZoom' => FILTER_VALIDATE_INT,
            'maxClusterRadius' => FILTER_VALIDATE_INT,
            'singleMarkerMode' => FILTER_VALIDATE_BOOLEAN,
            'spiderfyDistanceMultiplier' => FILTER_VALIDATE_INT,
            'chunkedLoading' => FILTER_VALIDATE_BOOLEAN,
            'chunkInterval' => FILTER_VALIDATE_INT,
            'chunkDelay' => FILTER_VALIDATE_INT,
        );

        $options = $this->LM->json_sanitize($options, $args);

        ob_start();
        ?>/*
      <script>*/
        //console.log('set WPLeafletMapPlugin.markerGroupConstructor = L.markerClusterGroup', `<?php //echo $options ?>//`);
        window.WPLeafletMapPlugin.markerGroupConstructor = function() { return new L.markerClusterGroup(<?php echo $options; ?>); };
        // and remove previous group from map to ensure creation of a new one
        var mapid = window.WPLeafletMapPlugin.maps.length;
        delete window.WPLeafletMapPlugin.markergroups[mapid];
        <?php

        $script = ob_get_clean();

        return $this->wrap_script($script, 'WPLeafletMarkerClusterGroupShortcode');
    }
}
