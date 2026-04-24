<?php
/**
 * Line Shortcode
 *
 * Use with [leaflet-line ...]
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
     * How leaflet renders the shape
     * 
     * @var string $type 
     */
    protected $type = 'line';

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

        // backwards compatible `fitline`
        if (!empty($fitline)) {
            $fitbounds = $fitline;
        }

        $fitbounds = filter_var($fitbounds, FILTER_VALIDATE_BOOLEAN);

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

        $js_factory = 'L.polyline';
        $collection = 'lines';
        
        if ($this->type == 'polygon') {
            $js_factory = 'L.polygon';
            $locations = "[$location_json]";
            $collection = 'polygons';
        }

        ob_start();
        ?>/*<script>*/
var previous_map = window.WPLeafletMapPlugin.getCurrentMap();
var group = window.WPLeafletMapPlugin.getCurrentGroup();
var shape = <?php echo $js_factory; ?>(<?php echo $location_json; ?>, <?php echo $style_json; ?>);
var fitbounds = <?php echo $fitbounds ? '1' : '0'; ?>;
shape.addTo( group );
if (fitbounds) {
    // zoom the map to the shape
    previous_map.fitBounds( shape.getBounds() );
}
<?php 
    $this->LM->add_popup_to_shape($atts, $content, 'shape'); 
?>
window.WPLeafletMapPlugin.<?php echo $collection; ?>.push( shape );<?php
        $script = ob_get_clean();

        return $this->wrap_script($script, 'WPLeafletLineShortcode');
    }
}