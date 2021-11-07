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
class Leaflet_Marker_Shortcode extends Leaflet_Shortcode
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

        if (!empty($address)) {
            include_once LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php';
            $location = new Leaflet_Geocoder( $address );
            $lat = $location->lat;
            $lng = $location->lng;
        }

        $lat_set = isset($lat) || isset($y);
        $lng_set = isset($lng) || isset($x);

        $lat = empty($lat) ? ( empty($y) ? '0' : $y ) : $lat;
        $lng = empty($lng) ? ( empty($x) ? '0' : $x ) : $lng;

        // validate lat/lng
        $lat = $this->LM->filter_float($lat);
        $lng = $this->LM->filter_float($lng);

        $default_marker = 'L.marker';

        if (isset($svg)) {
            $svg = filter_var($svg, FILTER_VALIDATE_BOOLEAN);
            if ($svg) {
                wp_enqueue_script('leaflet_svg_icon_js');
                $default_marker = 'new L.SVGMarker';
            }
        }

        // optional pluggable marker
        $default_marker = apply_filters('leaflet_map_marker', $default_marker);

        $options = array(
            'draggable' => isset($draggable) ? $draggable : null,
            'title' => isset($title) ? $title : null,
            'alt' => isset($alt) ? $alt : null,
            'zIndexOffset' => isset($zindexoffset) ? $zindexoffset : null,
            'opacity' => isset($opacity) ? $opacity : null,
            'iconUrl' => isset($iconurl) ? $iconurl : null,
            'iconSize' => isset($iconsize) ? $iconsize : null,
            'iconAnchor' => isset($iconanchor) ? $iconanchor : null,
            'shadowUrl' => isset($shadowurl) ? $shadowurl : null,
            'shadowSize' => isset($shadowsize) ? $shadowsize : null,
            'shadowAnchor' => isset($shadowanchor) ? $shadowanchor : null,
            'popupAnchor' => isset($popupanchor) ? $popupanchor : null,
            'svg' => isset($svg) ? $svg : null,
            'background' => isset($background) ? $background : null,
            'iconClass' => isset($iconclass) ? $iconclass : null,
            'color' => isset($color) ? $color : null
        );

        $args = array(
            'draggable' => FILTER_VALIDATE_BOOLEAN,
            'title' => FILTER_SANITIZE_STRING,
            'alt' => FILTER_SANITIZE_STRING,
            'zIndexOffset' => FILTER_VALIDATE_INT,
            'opacity' => FILTER_VALIDATE_FLOAT,
            'iconUrl' => FILTER_SANITIZE_URL,
            'shadowUrl' => FILTER_SANITIZE_URL,
            'iconSize' => FILTER_SANITIZE_STRING,
            'iconAnchor' => FILTER_SANITIZE_STRING,
            'shadowSize' => FILTER_SANITIZE_STRING,
            'shadowAnchor' => FILTER_SANITIZE_STRING,
            'popupAnchor' => FILTER_SANITIZE_STRING,
            'svg' => FILTER_VALIDATE_BOOLEAN,
            'background' => FILTER_SANITIZE_STRING,
            'iconClass' => FILTER_SANITIZE_STRING,
            'color' => FILTER_SANITIZE_STRING
        );

        $options = $this->LM->json_sanitize($options, $args);

        ob_start();
        ?>/*<script>*/
var map = window.WPLeafletMapPlugin.getCurrentMap();
var group = window.WPLeafletMapPlugin.getCurrentGroup();
var marker_options = window.WPLeafletMapPlugin.getIconOptions(<?php echo $options; ?>);
var marker = <?php echo $default_marker; ?>(
    [<?php echo $lat . ',' . $lng; ?>], 
    marker_options
);
var is_image = map.is_image_map;
<?php
if (!$lat_set && !$lng_set) {
    /* update lat lng to previous map's center */
?>
    marker.setLatLng( map.getCenter() );
<?php
}
?>
if (marker_options.draggable) {
    marker.on('dragend', function () {
        var latlng = this.getLatLng();
        var lat = latlng.lat;
        var lng = latlng.lng;
        if (is_image) {
            console.log('leaflet-marker y=' + lat + ' x=' + lng);
        } else {
            console.log('leaflet-marker lat=' + lat + ' lng=' + lng);
        }
    });
}
marker.addTo( group );
<?php
    $this->LM->add_popup_to_shape($atts, $content, 'marker');
?>
window.WPLeafletMapPlugin.markers.push( marker );
        <?php
        
        $script = ob_get_clean();

        return $this->wrap_script($script, 'WPLeafletMarkerShortcode');
    }
}