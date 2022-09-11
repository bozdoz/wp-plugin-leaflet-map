<?php
/**
 * GeoJSON Shortcode
 *
 * Use with [leaflet-geojson src="..."]
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
 * GeoJSON Shortcode Class
 */
class Leaflet_Geojson_Shortcode extends Leaflet_Shortcode
{
    /**
     * Default src for geoJSON
     * 
     * @var string $default_src
     */
    protected $default_src = 'https://gist.githubusercontent.com/bozdoz/064a7101b95a324e8852fe9381ab9a18/raw/03f4f54b13a3a7e256732760a8b679818d9d36fc/map.geojson';

    /**
     * How leaflet renders the src
     * 
     * @var string $type 
     */
    protected $type = 'json';

    /**
     * Get Script for Shortcode
     * 
     * @param string $atts    could be an array
     * @param string $content
     * 
     * @return string HTML
     */
    protected function getHTML($atts='', $content=null)
    {
        if ($atts) {
            extract($atts, EXTR_SKIP);
        } 

        wp_enqueue_script('leaflet_ajax_geojson_js');

        if ($content) {
            $content = str_replace(array("\r\n", "\n", "\r"), '<br>', $content);
            $content = htmlspecialchars($content);
        }

        /* only required field for geojson; accept either src or source */
        $source = empty($source) ? '' : $source;
        $src = empty($src) ? $this->default_src : $src;
        $src = empty($source) ? $src : $source;

        $style_json = $this->LM->get_style_json($atts);

        $fitbounds = empty($fitbounds) ? 0 : $fitbounds;
        $fitbounds = filter_var($fitbounds, FILTER_VALIDATE_BOOLEAN);
        $circleMarker = empty($circleMarker) ? 0 : $circleMarker;
        $circleMarker = filter_var($circleMarker, FILTER_VALIDATE_BOOLEAN);

        // shortcode content becomes popup text
        $content_text = empty($content) ? '' : $content;
        // alternatively, the popup_text attribute works as popup text
        $popup_text = empty($popup_text) ? '' : $popup_text;
        // choose which one takes priority (content_text)
        $popup_text = empty($content_text) ? $popup_text : $content_text;

        $popup_property = empty($popup_property) ? '' : $popup_property;

        $popup_text = trim($popup_text);

        $table_view = filter_var(empty($table_view) ? 0 : $table_view, FILTER_VALIDATE_INT);
        
        //options of iconUrl feature
        $options = array(
            'iconUrl' => isset($iconurl) ? $iconurl : null,
            'iconSize' => isset($iconsize) ? $iconsize : null,
            'iconAnchor' => isset($iconanchor) ? $iconanchor : null,
            'popupAnchor' => isset($popupanchor) ? $popupanchor : null
        );

        $args = array(
            'iconUrl' => FILTER_SANITIZE_URL,
            'iconSize' => FILTER_SANITIZE_STRING,
            'iconAnchor' => FILTER_SANITIZE_STRING,
            'popupAnchor' => FILTER_SANITIZE_STRING,
        );

        $options = $this->LM->json_sanitize($options, $args);

        ob_start();
        ?>/*<script>*/
var src = '<?php echo htmlspecialchars($src, ENT_QUOTES); ?>';
var default_style = <?php echo $style_json; ?>;
var rewrite_keys = {
    stroke : 'color',
    'stroke-width' : 'weight',
    'stroke-opacity' : 'opacity',
    fill : 'fillColor',
    'fill-opacity' : 'fillOpacity',
};
var layer = L.ajaxGeoJson(src, {
    type: '<?php echo $this->type; ?>',
    style : layerStyle,
    onEachFeature : onEachFeature,
    pointToLayer: pointToLayer
});
var fitbounds = <?php echo $fitbounds ? '1' : '0'; ?>;
var circleMarker = <?php echo $circleMarker ? '1' : '0'; ?>;
var popup_text = window.WPLeafletMapPlugin.unescape("<?php echo $popup_text; ?>");
var popup_property = "<?php echo $popup_property; ?>";
var group = window.WPLeafletMapPlugin.getCurrentGroup();
var markerOptions = window.WPLeafletMapPlugin.getIconOptions(<?php echo $options; ?>);
layer.addTo( group );
window.WPLeafletMapPlugin.geojsons.push( layer );
if (fitbounds) {
    layer.on('ready', function () {
        this.map.fitBounds( this.getBounds() );
    });
}
function layerStyle (feature) {
    var props = feature.properties || {};
    var style = {};
    function camelFun (_, first_letter) {
        return first_letter.toUpperCase();
    };
    for (var key in props) {
        if (key.match('-')) {
            var camelcase = key.replace(/-(\w)/, camelFun);
            style[ camelcase ] = props[ key ];
        }
        // rewrite style keys from geojson.io
        if (rewrite_keys[ key ]) {
            style[ rewrite_keys[ key ] ] = props[ key ];
        }
    }
    return L.Util.extend(style, default_style);
}
function onEachFeature (feature, layer) {
    var props = feature.properties || {};
    var text;
    if (<?php echo $table_view; ?>) {
        text = window.WPLeafletMapPlugin.propsToTable(props);
    } else {
        text = popup_property
            ? props[ popup_property ]
            : window.WPLeafletMapPlugin.template(
                popup_text, 
                feature.properties
            );
    }
    if (text) {
        layer.bindPopup( text );
    }
}
function pointToLayer (feature, latlng) {
    if (circleMarker) {
        return L.circleMarker(latlng);
    }
    return L.marker(latlng, markerOptions);
}<?php
        $script = ob_get_clean();

        return $this->wrap_script($script, 'WPLeaflet' . $this->type .'Shortcode');
    }
}
