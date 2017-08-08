<?php
/**
* Marker Shortcode
*
* Use with [leaflet-marker ...]
*
* @param array $atts        user-input array
* @param string $content    user-input content (allows HTML)
* @return string content for post/page
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

include_once(LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.shortcode.php');

class Leaflet_Marker_Shortcode extends Leaflet_Shortcode {
	protected function getHTML ($atts, $content) {
        if (!empty($atts)) extract($atts);

        if (!empty($address)) {
            include_once(LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php');
            $location = new Leaflet_Geocoder( $address );
            $lat = $location->lat;
            $lng = $location->lng;
        }

        /* add to user contributed lat lng */
        $lat = empty($lat) ? ( empty($y) ? '0' : $y ) : $lat;
        $lng = empty($lng) ? ( empty($x) ? '0' : $x ) : $lng;

        $options = array(
            'draggable' => isset($draggable) ? $draggable : NULL,
            'title' => isset($title) ? $title : NULL,
            'alt' => isset($alt) ? $alt : NULL,
            'zIndexOffset' => isset($zindexoffset) ? $zindexoffset : NULL,
            'opacity' => isset($opacity) ? $opacity : NULL,
            'iconUrl' => isset($iconurl) ? $iconurl : NULL,
            'iconSize' => isset($iconsize) ? $iconsize : NULL,
            'iconAnchor' => isset($iconanchor) ? $iconanchor : NULL,
            'shadowUrl' => isset($shadowurl) ? $shadowurl : NULL,
            'shadowSize' => isset($shadowsize) ? $shadowsize : NULL,
            'shadowAnchor' => isset($shadowanchor) ? $shadowanchor : NULL
        );

        $args = array(
            'draggable' => FILTER_VALIDATE_BOOLEAN,
            'title' => FILTER_SANITIZE_STRING,
            'alt' => FILTER_SANITIZE_STRING,
            'zIndexOffset' => FILTER_VALIDATE_INT,
            'opacity' => FILTER_VALIDATE_FLOAT,
            'iconUrl' => FILTER_SANITIZE_URL,
            'shadowUrl' => FILTER_SANITIZE_URL,
            'iconSize' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_FORCE_ARRAY
                ),
            'iconAnchor' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_FORCE_ARRAY
                ),
            'shadowSize' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_FORCE_ARRAY
                ),
            'shadowAnchor' => array(
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
        WPLeafletMapPlugin.add(function () {
            var marker_options = (function () {
                    var _options = <?php echo $options; ?>,
                        iconArrays = ['iconSize', 'iconAnchor', 
                            'shadowSize', 'shadowAnchor'];
                    if (_options.iconUrl) {
                        // arrays are strings, unfortunately...
                        for (var i = 0, len = iconArrays.length; i < len; i++) {
                            var option_name = iconArrays[i],
                                option = _options[ option_name ];
                            if (option) {
                                _options[ option_name ] = option.join('').split(',');
                            }
                        }
                        _options.icon = new L.Icon( _options );
                    }
                    return _options;
                })(),
                draggable = marker_options.draggable,
                marker = L.marker([<?php echo $lat . ',' . $lng; ?>], marker_options),
                previous_map = WPLeafletMapPlugin.getCurrentMap(),
                is_image = previous_map.is_image_map,
                previous_map_onload,
                markergroup = WPLeafletMapPlugin.getCurrentMarkerGroup();
        <?php
            if (empty($lat) && empty($lng)) {
                /* update lat lng to previous map's center */
        ?>
                if (!is_image) {
                    marker.setLatLng( previous_map.getCenter() );
                } else {
                    console.warn("hello");
                    marker.setLatLng( [0, 0] );
                }
        <?php
            }
        ?>
            if (draggable) {
                marker.on('dragend', function () {
                    var latlng = this.getLatLng(),
                        lat = latlng.lat,
                        lng = latlng.lng;
                    if (is_image) {
                        console.log('leaflet-marker y=' + lat + ' x=' + lng);
                    } else {
                        console.log('leaflet-marker lat=' + lat + ' lng=' + lng);
                    }
                });
            }

            marker.addTo( markergroup );
        <?php
            $this->LM->add_popup_to_shape($atts, $content, 'marker');
        ?>
            WPLeafletMapPlugin.markers.push( marker );
        }); // end add function
        </script>
        <?php

        return ob_get_clean();
	}
}