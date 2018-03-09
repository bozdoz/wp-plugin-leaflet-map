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
	* @var string $default_src default src
	*/
	public static $default_src = 'https://rawgit.com/bozdoz/567817310f102d169510d94306e4f464/raw/2fdb48dafafd4c8304ff051f49d9de03afb1718b/map.geojson';

    /**
    * @var string $type how leaflet renders the src
    */
    public static $type = 'json';

	protected function getHTML ($atts='', $content=null) {

        // need to get the called class to extend above variables
        $class = self::getClass();
        
        if ($atts) extract($atts);

		wp_enqueue_script( 'leaflet_ajax_geojson_js' );

        if ($content) {
            $content = str_replace(array("\r\n", "\n", "\r"), '<br>', $content);
            $content = htmlspecialchars($content);
        }

        /* only required field for geojson; accept either src or source */
        $source = empty($source) ? '' : $source;
        $src = empty($src) ? $class::$default_src : $src;
        $src = empty($source) ? $src : $source;

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
                    layer = L.ajaxGeoJson(src, {
                        type: '<?php echo $class::$type; ?>',
                        style : layerStyle,
                        onEachFeature : onEachFeature,
                        /* function to add custom icons when defined in feature.properties */
                        pointToLayer: function(feature, latlng) {
                            return feature.properties.iconUrl ? new L.Marker(latlng, {icon: L.icon({
                                iconUrl: feature.properties.iconUrl,
                                iconSize: feature.properties.iconSize ? feature.properties.iconSize : [25, 41], // size of the icon
                            })}) : new L.Marker(latlng);
                        }
                    }),
                    fitbounds = <?php echo $fitbounds; ?>,
                    popup_text = WPLeafletMapPlugin.unescape('<?php echo $popup_text; ?>'),
                    popup_property = '<?php echo $popup_property; ?>',
                    legendVals = {};
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
                        text = popup_property && props[ popup_property ] || template(popup_text, feature.properties),
                        featureName = feature.properties.name,
                        iconUrl = feature.properties.iconUrl;
                    if (text) {
                        layer.bindPopup( text );
                    }
                    if (iconUrl) {
                        // create the legend entities for the feature names, assumes the filename pattern is *-[iconColor].*
                        legendVals[featureName] = iconUrl.slice(iconUrl.lastIndexOf("-") + 1, iconUrl.lastIndexOf("."));
                    }
                }
                layer.on('ready', function () {
                    if('<?php echo $legend; ?>' && JSON.stringify(legendVals) !== JSON.stringify({})) {
                        // add a legend for the icons
                        var legend = L.control({position: 'bottomright'});
                        legend.onAdd = function (previousMap) {
                            var div = L.DomUtil.create('div', 'info legend');

                            // loop through our legend items and generate a label with a colored square for each distinct feature name
                            for (var key in legendVals) {
                                div.innerHTML += '<i style="background: ' + legendVals[key] + '"></i>' + key + '<br>';
                            }
                            return div;
                       };
                       legend.addTo(previous_map);
                    }
                });
            });
        </script>
        <?php
        return ob_get_clean();
	}
}