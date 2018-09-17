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

        /* add to user contributed lat lng */
        $lat = empty($lat) ? ( empty($y) ? '0' : $y ) : $lat;
        $lng = empty($lng) ? ( empty($x) ? '0' : $x ) : $lng;
        $radius = empty($radius) ? '0' : $radius;

        ob_start();
        ?>
        <script>
        WPLeafletMapPlugin.add(function () {
            var previous_map = WPLeafletMapPlugin.getCurrentMap(),
                circle = L.circle([<?php echo $lat . ', ' . $lng . '], {radius: ' . $radius . '}'; ?>),
                fitbounds = <?php echo $fitbounds; ?>;
            circle.setStyle(<?php echo $style_json; ?>);
            circle.addTo( previous_map );
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
