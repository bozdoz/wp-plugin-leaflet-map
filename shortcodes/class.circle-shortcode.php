<?php
/**
 * Circle Shortcode
 *
 * Use with [leaflet-circle ...]
 *
 * @category Shortcode
 * @author   Peter Uithoven <peter@peteruithoven.nl>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.shortcode.php';

/**
 * Leaflet Line Shortcode Class
 */
class Leaflet_Circle_Shortcode extends Leaflet_Shortcode
{
    /**
     * Get Script for Shortcode
     *
     * @param string $atts    shortcode attributes
     * @param string $content optional
     *
     * @return string HTML
     */
    protected function getHTML($atts='', $content=null)
    {
        if (!empty($atts)) {
            extract($atts, EXTR_SKIP);
        }

        $style_json = $this->LM->get_style_json($atts);

        $fitbounds = empty($fitbounds) ? 0 : $fitbounds;
        $fitbounds = filter_var($fitbounds, FILTER_VALIDATE_BOOLEAN);

        if (!empty($address)) {
            include_once LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php';
            $location = new Leaflet_Geocoder( $address );
            $lat = $location->lat;
            $lng = $location->lng;
        }

        $lat = empty($lat) ? ( empty($y) ? '0' : $y ) : $lat;
        $lng = empty($lng) ? ( empty($x) ? '0' : $x ) : $lng;
        
        // validate lat/lng
        $lat = $this->LM->filter_float($lat);
        $lng = $this->LM->filter_float($lng);

        $radius = empty($radius) ? '1000' : $radius;

        $radius = $this->LM->filter_float($radius);

        ob_start();
        ?>/*<script>*/
var previous_map = window.WPLeafletMapPlugin.getCurrentMap();
var group = window.WPLeafletMapPlugin.getCurrentGroup();
var fitbounds = <?php echo $fitbounds ? '1' : '0'; ?>;
var is_image = previous_map.is_image_map;
var lat = <?php echo $lat; ?>;
var lng = <?php echo $lng; ?>;
var radius = <?php echo $radius; ?>;
// update lat lng to previous map's center
if (!lat && !lng && !is_image) {
    var map_center = previous_map.getCenter();
    lat = map_center.lat;
    lng = map_center.lng;
}
var circle = L.circle([lat, lng], {radius: radius});
circle.setStyle(<?php echo $style_json; ?>);
circle.addTo( group );
window.WPLeafletMapPlugin.circles.push( circle );
if (fitbounds) {
    // zoom the map to the polyline
    previous_map.fitBounds( circle.getBounds() );
}<?php
        $this->LM->add_popup_to_shape($atts, $content, 'circle');

        $script = ob_get_clean();

        return $this->wrap_script($script, 'WPLeafletCircleShortcode');
    }
}
