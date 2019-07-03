<?php
/**
 * GeoJSON Shortcode
 *
 * Use with [leaflet-geojson src="..."]
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
 * GeoJSON Shortcode Class
 */
class Leaflet_Geojson_Shortcode extends Leaflet_Shortcode
{
    /**
     * Default src for geoJSON
     * 
     * @var string $default_src
     */
    public static $default_src = 'https://gist.githubusercontent.com/bozdoz/064a7101b95a324e8852fe9381ab9a18/raw/03f4f54b13a3a7e256732760a8b679818d9d36fc/map.geojson';

    /**
     * How leaflet renders the src
     * 
     * @var string $type 
     */
    public static $type = 'json';

    /**
     * Get Script for Shortcode
     * 
     * @param string $atts    could be an array
     * @param string $content could be an array
     * 
     * @return string HTML
     */
    protected function getHTML($atts='', $content=null)
    {

        // need to get the called class to extend above variables
        $class = self::getClass();
        
        if ($atts) {
            extract($atts);
        } 

        wp_enqueue_script('leaflet_ajax_geojson_js');

        if ($content) {
            $content = str_replace(array("\r\n", "\n", "\r"), '<br>', $content);
            $content = htmlspecialchars($content);
        }

        /* only required field for geojson; accept either src or source */
        $source = empty($source) ? '' : $source;
        $src = empty($src) ? $class::$default_src : $src;
        $src = empty($source) ? $src : $source;

        $style_json = $this->LM->get_style_json($atts);

        $fitbounds = empty($fitbounds) ? 0 : $fitbounds;

        // shortcode content becomes popup text
        $content_text = empty($content) ? '' : $content;
        // alternatively, the popup_text attribute works as popup text
        $popup_text = empty($popup_text) ? '' : $popup_text;
        // choose which one takes priority (content_text)
        $popup_text = empty($content_text) ? $popup_text : $content_text;

        $popup_property = empty($popup_property) ? '' : $popup_property;

        $popup_text = trim($popup_text);

        ob_start();
        ?>
        <script>
            window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
            window.WPLeafletMapPlugin.push(function () {
                var src = '<?php echo $src; ?>',
                    default_style = <?php echo $style_json; ?>,
                    rewrite_keys = {
                        stroke : 'color',
                        'stroke-width' : 'weight',
                        'stroke-opacity' : 'opacity',
                        fill : 'fillColor',
                        'fill-opacity' : 'fillOpacity',
                    },
                    layer = L.ajaxGeoJson(src, {
                        type: '<?php echo $class::$type; ?>',
                        style : layerStyle,
                        onEachFeature : onEachFeature,
                    }),
                    fitbounds = <?php echo $fitbounds; ?>,
                    popup_text = window.WPLeafletMapPlugin.unescape('<?php echo $popup_text; ?>'),
                    popup_property = '<?php echo $popup_property; ?>',
                    group = window.WPLeafletMapPlugin.getCurrentGroup();   
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
                    var camelFun = function camelFun (_, first_letter) {
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
                    style = L.Util.extend(style, default_style);
                    return style;
                }      
                function onEachFeature (feature, layer) {
                    var props = feature.properties || {};
                    var text = popup_property
                        ? props[ popup_property ]
                        : window.WPLeafletMapPlugin.template(
                            popup_text, 
                            feature.properties
                        );
                    if (text) {
                        layer.bindPopup( text );
                    }
                }  
            });
        </script>
        <?php
        return ob_get_clean();
    }
}
