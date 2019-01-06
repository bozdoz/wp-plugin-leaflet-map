<?php
/**
 * Circle Shortcode
 *
 * Use with [leaflet-circle ...]
 *
 * PHP Version 5.5
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
            extract($atts);
        }

        $style_json = $this->LM->get_style_json($atts);

        $fitbounds = empty($fitbounds) ? 0 : $fitbounds;

        if (!empty($address)) {
            include_once LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php';
            $location = new Leaflet_Geocoder( $address );
            $lat = $location->lat;
            $lng = $location->lng;
        }

        $lat = empty($lat) ? ( empty($y) ? '0' : $y ) : $lat;
        $lng = empty($lng) ? ( empty($x) ? '0' : $x ) : $lng;
        $radius = empty($radius) ? '1000' : $radius;

        ob_start();
        ?>
        <script>
        window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
        window.WPLeafletMapPlugin.push(function () {
            var previous_map = window.WPLeafletMapPlugin.getCurrentMap(),
                fitbounds = <?php echo $fitbounds; ?>,
                is_image = previous_map.is_image_map,
                lat = <?php echo $lat; ?>,
                lng = <?php echo $lng; ?>,
                radius = <?php echo $radius; ?>,
                group = window.WPLeafletMapPlugin.getCurrentGroup();

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
            }
        <?php
            $this->LM->add_popup_to_shape($atts, $content, 'circle');
        ?>
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
