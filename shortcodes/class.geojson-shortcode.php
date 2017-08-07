<?php
/**
* GeoJSON Shortcode
*
* Use with [leaflet-geojson src="..."]
*
* @param array $atts        user-input array
* @return string JavaScript
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

include_once(LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.shortcode.php');

class Leaflet_Geojson_Shortcode extends Leaflet_Shortcode {
	/**
	* @var string $wp_script to enqueue
	*/
	private $wp_script = 'leaflet_ajax_geojson_js';
	/**
	* @var string $L_method how leaflet renders the src
	*/
	private $L_method = 'ajaxGeoJson';
	/**
	* @var string $default_src default src
	*/
	private $default_src = 'https://rawgit.com/bozdoz/567817310f102d169510d94306e4f464/raw/2fdb48dafafd4c8304ff051f49d9de03afb1718b/map.geojson';

	protected function getHTML ($atts, $content) {
        
        if ($atts) extract($atts);

		wp_enqueue_script( $this->wp_script );

        if ($content) {
            $content = htmlspecialchars($content);
        }

        /* only required field for geojson */
        $src = empty($src) ? $this->default_src : $src;

        $style_json = $this->LM->get_style_json( $atts );

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
            WPLeafletMapPlugin.add(function () {
                var previous_map = WPLeafletMapPlugin.getCurrentMap(),
                    src = '<?php echo $src; ?>',
                    default_style = <?php echo $style_json; ?>,
                    rewrite_keys = {
                        fill : 'fillColor',
                        'fill-opacity' : 'fillOpacity',
                        stroke : 'color',
                        'stroke-opacity' : 'opacity',
                        'stroke-width' : 'width',
                    },
                    layer = L.<?php echo $this->L_method; ?>(src, {
                        style : layerStyle,
                        onEachFeature : onEachFeature
                    }),
                    fitbounds = <?php echo $fitbounds; ?>,
                    popup_text = WPLeafletMapPlugin.unescape('<?php echo $popup_text; ?>'),
                    popup_property = '<?php echo $popup_property; ?>';
                if (fitbounds) {
                    layer.on('ready', function () {
                        this.map.fitBounds( this.getBounds() );
                    });
                }
                layer.addTo( previous_map );
                function layerStyle (feature) {
                    var props = feature.properties || {},
                        style = {};
                    for (var key in props) {
                        if (key.match('-')) {
                            var camelcase = key.replace(/-(\w)/, function (_, first_letter) {
                                return first_letter.toUpperCase();
                            });
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
                    var props = feature.properties || {},
                        text = popup_property && props[ popup_property ] || popup_text;
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