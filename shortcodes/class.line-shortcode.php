<?php
/**
 * Line Shortcode
 *
 * Use with [leaflet-line ...]
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

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.shortcode.php';

/**
 * Leaflet Line Shortcode Class
 */
class Leaflet_Line_Shortcode extends Leaflet_Shortcode
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

        // backwards compatible `fitline`
        if (!empty($fitline)) {
            $fitbounds = $fitline;
        }

        $locations = Array();

        if (!empty($addresses)) {
            include_once LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php';
            $addresses = preg_split('/\s?[;|\/]\s?/', $addresses);
            foreach ($addresses as $address) {
                if (trim($address)) {
                    $location = new Leaflet_Geocoder($address);
                    $locations[] = Array($location->lat, $location->lng);
                }
            }
        } else if (!empty($latlngs)) {
            $latlngs = preg_split('/\s?[;|\/]\s?/', $latlngs);
            foreach ($latlngs as $latlng) {
                if (trim($latlng)) {
                    $locations[] = array_map('floatval', preg_split('/\s?,\s?/', $latlng));
                }
            }
        } else if (!empty($coordinates)) {
            $coordinates = preg_split('/\s?[;|\/]\s?/', $coordinates);
            foreach ($coordinates as $xy) {
                if (trim($xy)) {
                    $locations[] = array_map('floatval', preg_split('/\s?,\s?/', $xy));
                }
            }
        }

        $location_json = json_encode($locations);
        ob_start();
        ?>
        <script>
        window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
        window.WPLeafletMapPlugin.push(function () {
            var previous_map = window.WPLeafletMapPlugin.getCurrentMap(),
                line = L.polyline(<?php echo $location_json; ?>, <?php echo $style_json; ?>),
                fitbounds = <?php echo $fitbounds; ?>,
                group = window.WPLeafletMapPlugin.getCurrentGroup();
            line.addTo( group );
            if (fitbounds) {
                // zoom the map to the polyline
                previous_map.fitBounds( line.getBounds() );
            }
            <?php
                $this->LM->add_popup_to_shape($atts, $content, 'line');
            ?>
            window.WPLeafletMapPlugin.lines.push( line );
        });
        </script>
        <?php
        return ob_get_clean();
    }
}