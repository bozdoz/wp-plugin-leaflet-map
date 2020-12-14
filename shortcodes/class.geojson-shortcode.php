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
     * @param string $content
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
        $circleMarker = empty($circleMarker) ? 0 : $circleMarker;

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
            'iconSize' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_FORCE_ARRAY
            ),
            'iconAnchor' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_FORCE_ARRAY
            ),
            'popupAnchor' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_FORCE_ARRAY
            )
        );

        $options = $this->LM->json_sanitize($options, $args);

        if ($options === '[]') {
            $options = '{}';
        }

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
                        pointToLayer: pointToLayer
                    }),
                    fitbounds = <?php echo $fitbounds; ?>,
                    circleMarker = <?php echo $circleMarker; ?>,
                    popup_text = window.WPLeafletMapPlugin.unescape('<?php echo $popup_text; ?>'),
                    popup_property = '<?php echo $popup_property; ?>',
                    group = window.WPLeafletMapPlugin.getCurrentGroup(),   
                    options = <?php echo $options; ?>;
                    if(options.iconUrl) {
                        var iconArrays = [
                            'iconSize', 
                            'iconAnchor', 
                            'popupAnchor'
                        ];
                        // arrays are strings, unfortunately...
                        for (var i = 0, len = iconArrays.length; i < len; i++) {
                            var option_name = iconArrays[i],
                                option = options[ option_name ];
                            // convert "1,2" to [1, 2];
                            if (option) {
                                var arr = option.join('').split(',');
                                // array.map for ie<9
                                for (var j = 0, lenJ = arr.length; j < lenJ; j++) {
                                    arr[j] = Number(arr[j]);
                                }
                                options[ option_name ] = arr;
                            }
                        }
                        
                        // default popupAnchor
                        if (!options.popupAnchor) {
                            // set (roughly) to size of icon
                            options.popupAnchor = (function (i_size) {
                                // copy array
                                i_size = i_size.slice();
                                // inverse coordinates
                                i_size[0] = 0;
                                i_size[1] *= -1;
                                // bottom position on popup is 7px
                                i_size[1] -= 3;
                                return i_size;
                            })(options.iconSize || L.Icon.Default.prototype.options.iconSize);
                        }
                    
                        options.icon = new L.Icon( options );          
                    } 
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
                    } else {
                        return L.marker(latlng, options);
                    }
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }
}
