<?php
/*
Author: bozdoz
Author URI: http://twitter.com/bozdoz/
Plugin URI: http://wordpress.org/plugins/leaflet-map/
Plugin Name: Leaflet Map
Description: A plugin for creating a Leaflet JS map with a shortcode.
Version: 1.14
License: GPL2
*/
if (!class_exists('Leaflet_Map_Plugin')) {

    class Leaflet_Map_Plugin
    {

        public static $geocoders = array(
            'google' => 'Google Maps',
            'osm' => 'OpenStreetMap Nominatim',
            'dawa' => 'Danmarks Adressers'
        );

        public static $defaults = array(
            'text' => array(
                'leaflet_map_tile_url' => '//otile{s}-s.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpg',
                'leaflet_map_tile_url_subdomains' => '1234',
                'leaflet_js_url' => '//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js',
                'leaflet_css_url' => '//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css',

                'leaflet_js_heatmap' => '//cdn.rawgit.com/pa7/heatmap.js/v2.0/build/heatmap.min.js',
                'leaflet_js_heatmap_plugin' => '//cdn.rawgit.com/pa7/heatmap.js/v2.0/plugins/leaflet-heatmap.js',
                'leaflet_default_zoom' => '16',
                'leaflet_default_height' => '250',
                'leaflet_default_width' => '100%',
            ),
            'textarea' => array(
                'leaflet_default_attribution' => 'Tiles Courtesy of <a href=\"http://www.mapquest.com/\" target=\"_blank\">MapQuest</a> <img src=\"http://developer.mapquest.com/content/osm/mq_logo.png\" />; © <a href=\"http://www.openstreetmap.org/\">OpenStreetMap</a> contributors',
            ),
            'select' => array(
                'leaflet_geocoder' => 'google',
            ),
            'checkbox' => array(
                'leaflet_show_attribution' => '1',
                'leaflet_show_zoom_controls' => '0',
                'leaflet_scroll_wheel_zoom' => '0',
            ),
            'serialized' => array(
                'leaflet_geocoded_locations' => array()
            )
        );

        public static $helptext = array(
            'leaflet_map_tile_url' => 'See some example tile URLs at <a href="http://developer.mapquest.com/web/products/open/map" target="_blank">MapQuest</a>.  Can be set per map with shortcode attribute <br/> <code>[leaflet-map tileurl="http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpg"]</code>',
            'leaflet_map_tile_url_subdomains' => 'Some maps get tiles from multiple servers with subdomains such as a,b,c,d or 1,2,3,4; can be set per map with the shortcode <br/> <code>[leaflet-map subdomains="1234"]</code>',
            'leaflet_js_url' => 'If you host your own Leaflet files, specify the URL here.',
            'leaflet_css_url' => 'Same as above.',
            'leaflet_js_heatmap' => '(Advanced) Url to Heatmap.js library script',
            'leaflet_js_heatmap_plugin' => '(Advanced) Url to Heatmap.js Leaflet plugin script',
            'leaflet_default_zoom' => 'Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map zoom="5"]</code>',
            'leaflet_default_height' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>',
            'leaflet_default_width' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>',
            'leaflet_show_attribution' => 'The default URL requires attribution by its terms of use.  If you want to change the URL, you may remove the attribution.  Also, you can set this per map in the shortcode (1 for enabled and 0 for disabled): <br/> <code>[leaflet-map show_attr="1"]</code>',
            'leaflet_show_zoom_controls' => 'The zoom buttons can be large and annoying.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map zoomcontrol="0"]</code>',
            'leaflet_scroll_wheel_zoom' => 'Disable zoom with mouse scroll wheel.  Sometimes someone wants to scroll down the page, and not zoom the map.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map scrollwheel="0"]</code>',
            'leaflet_default_attribution' => 'Attribution to a custom tile url.  Use semi-colons (;) to separate multiple.',
            'leaflet_geocoder' => 'Select the Geocoding provider to use to retrieve addresses defined in shortcode.',
        );

        public function __construct()
        {
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'admin_menu'));

            add_shortcode('leaflet-map', array(&$this, 'map_shortcode'));
            add_shortcode('leaflet-marker', array(&$this, 'marker_shortcode'));
            add_shortcode('leaflet-line', array(&$this, 'line_shortcode'));
            add_shortcode('leaflet-image', array(&$this, 'image_shortcode'));
            add_shortcode('leaflet-line', array(&$this, 'line_shortcode'));

            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_and_register'));
            add_action('admin_enqueue_scripts', array(&$this, 'enqueue_and_register'));

            /* allow maps on excerpts */
            add_filter('the_excerpt', 'do_shortcode');
        }

        public static function activate()
        {
            /* set default values to db */
            foreach (self::$defaults as $arrs) {
                foreach ($arrs as $k => $v) {
                    add_option($k, $v);
                }
            }
        }

        public static function uninstall()
        {

            /* remove geocoded locations */
            $locations = get_option('leaflet_geocoded_locations', array());

            foreach ($locations as $address => $latlng) {
                delete_option('leaflet_' + $address);
            }

            /* remove values from db */
            foreach (self::$defaults as $arrs) {
                foreach ($arrs as $k => $v) {
                    delete_option($k);
                }
            }
        }

        public function enqueue_and_register()
        {
            $defaults = self::$defaults['text'];

            /* backwards compatible */
            $version = get_option('leaflet_js_version', '');

            /* defaults from db */
            $js_url = get_option('leaflet_js_url', $defaults['leaflet_js_url']);
            $css_url = get_option('leaflet_css_url', $defaults['leaflet_css_url']);

            $js_url = sprintf($js_url, $version);
            $css_url = sprintf($css_url, $version);

            wp_register_style('leaflet_stylesheet', $css_url, Array(), $version, false);
            wp_register_script('leaflet_js', $js_url, Array(), $version, true);


            /* heatmap.js JS libraries */
            wp_register_script('leaflet_js_heatmap',  get_option('leaflet_js_heatmap', $defaults['leaflet_js_heatmap']), Array(), $version, true);
            wp_register_script('leaflet_js_heatmap_plugin',  get_option('leaflet_js_heatmap_plugin', $defaults['leaflet_js_heatmap_plugin']), Array(), $version, true);

            /* run an init function because other wordpress plugins don't play well with their window.onload functions */
            wp_register_script('leaflet_map_init', plugins_url('scripts/init-leaflet-map.js', __FILE__), Array('leaflet_js'), '1.0', true);

            /* run a construct function in the document head for the init function to use */
            wp_enqueue_script('leaflet_map_construct', plugins_url('scripts/construct-leaflet-map.js', __FILE__), Array(), '1.0', false);

        }

        public function admin_init()
        {
            wp_register_style('leaflet_admin_stylesheet', plugins_url('style.css', __FILE__));
        }

        public function admin_menu()
        {
            add_menu_page("Leaflet Map", "Leaflet Map", 'manage_options', "leaflet-map", array(&$this, "settings_page"), plugins_url('images/leaf.png', __FILE__));
            add_submenu_page("leaflet-map", "Default Values", "Default Values", 'manage_options', "leaflet-map", array(&$this, "settings_page"));
            add_submenu_page("leaflet-map", "Shortcodes", "Shortcodes", 'manage_options', "leaflet-get-shortcode", array(&$this, "shortcode_page"));
        }

        public function settings_page()
        {

            wp_enqueue_style('leaflet_admin_stylesheet');

            include 'templates/admin.php';
        }

        public function shortcode_page()
        {
            wp_enqueue_style('leaflet_admin_stylesheet');
            wp_enqueue_script('custom_plugin_js', plugins_url('scripts/get-shortcode.js', __FILE__), Array('leaflet_js'), false);

            include 'templates/find-on-map.php';
        }

        public function geocoder($address)
        {

            $address = urlencode($address);

            $default = self::$defaults['select']['leaflet_geocoder'];

            $geocoder = get_option('leaflet_geocoder', $default);

            $cached_address = 'leaflet_' . $geocoder . '_' . $address;

            /* retrieve cached geocoded location */
            if (get_option($cached_address)) {
                return get_option($cached_address);
            }

            $geocoding_method = $geocoder . '_geocode';
            $location = (Object)$this::$geocoding_method($address);

            /* add location */
            add_option($cached_address, $location);

            /* add option key to locations for clean up purposes */
            $locations = get_option('leaflet_geocoded_locations', array());
            array_push($locations, $cached_address);
            update_option('leaflet_geocoded_locations', $locations);

            return $location;
        }

        public function google_geocode($address)
        {
            /* Google */

            $geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
            $geocode_url .= $address;
            $json = file_get_contents($geocode_url);
            $json = json_decode($json);

            /* found location */
            if ($json->{'status'} == 'OK') {

                $location = $json->{'results'}[0]->{'geometry'}->{'location'};

                return $location;
            }

            /* else */
            return array('lat' => 0, 'lng' => 0);
        }

        public function osm_geocode($address)
        {
            /* OpenStreetMap Nominatim */

            $osm_geocode = 'https://nominatim.openstreetmap.org/?format=json&limit=1&q=';
            $geocode_url = $osm_geocode . $address;
            $json = file_get_contents($geocode_url);
            $json = json_decode($json);

            /* found location */
            if (count($json) > 0) {

                $location = (Object)array(
                    'lat' => $json[0]->{'lat'},
                    'lng' => $json[0]->{'lon'},
                );

                return $location;
            }

            /* else */
            return (Object)array('lat' => 0, 'lng' => 0);
        }

        public function dawa_geocode($address)
        {
            /* Danish Addresses Web Application */

            $dawa_geocode = 'https://dawa.aws.dk/adresser?format=json&q=';
            $geocode_url = $dawa_geocode . $address;
            $json = file_get_contents($geocode_url);
            $json = json_decode($json);

            /* found location */
            if ($json && $json[0]->{'status'} == 1) {

                $location = (Object)array(
                    'lat' => $json[0]->{'adgangsadresse'}->{'adgangspunkt'}->{'koordinater'}[1],
                    'lng' => $json[0]->{'adgangsadresse'}->{'adgangspunkt'}->{'koordinater'}[0]
                );

                return $location;
            }

            /* else */
            return (Object)array('lat' => 0, 'lng' => 0);
        }

        /* count map shortcodes to allow for multiple */
        public static $leaflet_map_count;

        public function map_shortcode($atts)
        {

            if (!$this::$leaflet_map_count) {
                $this::$leaflet_map_count = 0;
            }
            $this::$leaflet_map_count++;

            $leaflet_map_count = $this::$leaflet_map_count;

            $defaults = array_merge(self::$defaults['text'], self::$defaults['textarea'], self::$defaults['checkbox']);

            /* defaults from db */
            $default_zoom = get_option('leaflet_default_zoom', $defaults['leaflet_default_zoom']);
            $default_zoom_control = get_option('leaflet_show_zoom_controls', $defaults['leaflet_show_zoom_controls']);
            $default_height = get_option('leaflet_default_height', $defaults['leaflet_default_height']);
            $default_width = get_option('leaflet_default_width', $defaults['leaflet_default_width']);
            $default_show_attr = get_option('leaflet_show_attribution', $defaults['leaflet_show_attribution']);
            $default_tileurl = get_option('leaflet_map_tile_url', $defaults['leaflet_map_tile_url']);
            $default_subdomains = get_option('leaflet_map_tile_url_subdomains', $defaults['leaflet_map_tile_url_subdomains']);
            $default_scrollwheel = get_option('leaflet_scroll_wheel_zoom', $defaults['leaflet_scroll_wheel_zoom']);
            $default_attribution = get_option('leaflet_default_attribution', $defaults['leaflet_default_attribution']);

            /* leaflet script */
            wp_enqueue_style('leaflet_stylesheet');
            wp_enqueue_script('leaflet_js');
            wp_enqueue_script('leaflet_map_init');



            if ($atts) {
                extract($atts);
            }

            /* only really necessary $atts are the location variables */
            if (!empty($address)) {
                /* try geocoding */
                $location = $this::geocoder($address);
                $lat = $location->{'lat'};
                $lng = $location->{'lng'};
            }

            $lat = empty($lat) ? '44.67' : $lat;
            $lng = empty($lng) ? '-63.61' : $lng;


            /* check more user defined $atts against defaults */
            $tileurl = empty($tileurl) ? $default_tileurl : $tileurl;
            $show_attr = empty($show_attr) ? $default_show_attr : $show_attr;
            $subdomains = empty($subdomains) ? $default_subdomains : $subdomains;
            $zoomcontrol = empty($zoomcontrol) ? $default_zoom_control : $zoomcontrol;
            $zoom = empty($zoom) ? $default_zoom : $zoom;
            $scrollwheel = empty($scrollwheel) ? $default_scrollwheel : $scrollwheel;
            $height = empty($height) ? $default_height : $height;
            $width = empty($width) ? $default_width : $width;
            $attribution = empty($attribution) ? $default_attribution : $attribution;
            $heatmap = !empty($heatmap) && !!json_encode($heatmap);

            /* allow percent, but add px for ints */
            $height .= is_numeric($height) ? 'px' : '';
            $width .= is_numeric($width) ? 'px' : '';

            /* should be iterated for multiple maps */
            $content = '<div id="leaflet-wordpress-map-' . $leaflet_map_count . '" class="leaflet-wordpress-map" style="height:' . $height . '; width:' . $width . ';"></div>';

            $content .= "<script>
            WPLeafletMapPlugin.add(function () {
                var map,
                    baseURL = '{$tileurl}',
                    base = L.tileLayer(baseURL, {
                       subdomains: '{$subdomains}'
                    });

                map = L.map('leaflet-wordpress-map-{$leaflet_map_count}',
                    {
                        layers: [base],
                        zoomControl: {$zoomcontrol},
                        scrollWheelZoom: {$scrollwheel}
                    }).setView([{$lat}, {$lng}], {$zoom});";






            if($heatmap){

                // exampe [leaflet-map heatmap=1 heatmap-radius=20 heatmap-max-opacity=0.5]
                $heatMapConfig = array_merge(array(
                    "radius" => 20
                ), $this->getValuesFromPrefix("heatmap", $atts ?: []));


                wp_enqueue_script('leaflet_js_heatmap');
                wp_enqueue_script('leaflet_js_heatmap_plugin');


                $content .= "
                    var heatMapLayer = new HeatmapOverlay(".json_encode($heatMapConfig).");
                    heatMapLayer.setData({
                      data: []
                    });

                    map.addLayer(heatMapLayer);
                    map.on('mousemove', function(e){
                        heatMapLayer.addData({
                            lat: e.latlng.lat,
                            lng: e.latlng.lng,
                            value: 1
                          });
                    });

                ";
            }

            if ($show_attr) {
                /* add attribution to MapQuest and OSM */
                $attributions = explode(';', $attribution);

                foreach ($attributions as $a) {
                    $content .= "
                            map.attributionControl.addAttribution('{$a}');";
                }
            }




            $content .= '
                WPLeafletMapPlugin.maps.push(map);
            }); // end add
            </script>
            ';

            return $content;
        }

        public function image_shortcode($atts)
        {

            /* get map count for unique id */
            if (!$this::$leaflet_map_count) {
                $this::$leaflet_map_count = 0;
            }
            $this::$leaflet_map_count++;

            $leaflet_map_count = $this::$leaflet_map_count;

            $defaults = array_merge($this::$defaults['text'], $this::$defaults['checkbox']);

            /* defaults from db */
            $default_zoom_control = get_option('leaflet_show_zoom_controls', $defaults['leaflet_show_zoom_controls']);
            $default_height = get_option('leaflet_default_height', $defaults['leaflet_default_height']);
            $default_width = get_option('leaflet_default_width', $defaults['leaflet_default_width']);
            $default_scrollwheel = get_option('leaflet_scroll_wheel_zoom', $defaults['leaflet_scroll_wheel_zoom']);

            /* leaflet script */
            wp_enqueue_style('leaflet_stylesheet');
            wp_enqueue_script('leaflet_js');
            wp_enqueue_script('leaflet_map_init');

            if ($atts) {
                extract($atts);
            }

            /* only required field for image map */
            $source = empty($source) ? 'http://lorempixel.com/1000/1000/' : $source;

            /* check more user defined $atts against defaults */
            $height = empty($height) ? $default_height : $height;
            $width = empty($width) ? $default_width : $width;
            $zoomcontrol = empty($zoomcontrol) ? $default_zoom_control : $zoomcontrol;
            $zoom = empty($zoom) ? 1 : $zoom;
            $scrollwheel = empty($scrollwheel) ? $default_scrollwheel : $scrollwheel;

            /* allow percent, but add px for ints */
            $height .= is_numeric($height) ? 'px' : '';
            $width .= is_numeric($width) ? 'px' : '';

            $content = '<div id="leaflet-wordpress-image-' . $leaflet_map_count . '" class="leaflet-wordpress-map" style="height:' . $height . '; width:' . $width . ';"></div>';

            $content .= "<script>
            WPLeafletMapPlugin.add(function () {
                var map,
                    image_src = '$source',
                    img = new Image(),
                    zoom = $zoom;

                img.onload = function() {
                    var center_h = img.height / (zoom * 4),
                        center_w = img.width / (zoom * 4);

                    map.setView([center_h, center_w], zoom);

                    L.imageOverlay( image_src, [[ center_h * 2, 0 ], [ 0, center_w * 2]] ).addTo( map );

                    img.is_loaded = true;
                };
                img.src = image_src;

                map = L.map('leaflet-wordpress-image-{$leaflet_map_count}', {
                    maxZoom: 10,
                    minZoom: 1,
                    crs: L.CRS.Simple,
                    zoomControl: {$zoomcontrol},
                    scrollWheelZoom: {$scrollwheel}
                }).setView([0, 0], zoom);

                // make it known that it is an image map
                map.is_image_map = true;

                WPLeafletMapPlugin.maps.push( map );
                WPLeafletMapPlugin.images.push( img );
            }); // end add
            </script>";

            return $content;
        }


        private function startsWith($haystack, $needle)
        {
            // search backwards starting from haystack length characters from the end
            return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
        }


        /**
         * @author Krzysztof Bednarczyk
         * @param $value
         * @return mixed|string
         */
        private function getValueFormShortCode($value){
            $value = str_replace("array(", "[", $value);
            $value = str_replace(")", "]", $value);

            $val = json_decode(trim($value));

            return $val == null ? $value : $val;
        }


        /**
         * @author Krzysztof Bednarczyk
         * @param $prefix
         * @param mixed[] $values
         */
        public function getValuesFromPrefix($prefix, array $values){
            $result  = [];

            foreach($values as $name => $value){
                if(!$this->startsWith($name, $prefix."-")){
                    continue;
                }

                $optionName = substr($name, strlen($prefix)+1);
                $optionName = str_replace(' ', '', ucwords(str_replace('-', ' ', $optionName)));
                $result[lcfirst($optionName)] = $this->getValueFormShortCode($value);

            }

            return $result;
        }


        public function marker_shortcode($atts, $content = null)
        {

            /* add to previous map */
            if (!$this::$leaflet_map_count) {
                return '';
            }

            $leaflet_map_count = $this::$leaflet_map_count;

            if (!empty($atts)) extract($atts);

            $draggable = empty($draggable) ? 'false' : $draggable;
            $visible = empty($visible) ? false : ($visible == 'true');

            if (!empty($address)) {
                $location = $this::geocoder($address);
                $lat = $location->{'lat'};
                $lng = $location->{'lng'};
            }

            $marker_script = "<script>
            WPLeafletMapPlugin.add(function () {
                var marker,
                    markerOptions,
                    icon,
                    map_count = {$leaflet_map_count},
                    previous_map = WPLeafletMapPlugin.maps[ map_count - 1 ],
                    is_image = previous_map.is_image_map,
                    image_len = WPLeafletMapPlugin.images.length,
                    previous_image = WPLeafletMapPlugin.images[ image_len - 1 ],
                    previous_image_onload;
                ";

            /* add to user contributed lat lng */
            $lat = empty($lat) ? (empty($y) ? '0' : $y) : $lat;
            $lng = empty($lng) ? (empty($x) ? '0' : $x) : $lng;


            //simple [leaflet-marker option-icon-url="//cdn.google.pl/icon.png"]
            $options = array_merge(array(
                "draggable" => !!json_decode($draggable)
            ), $this->getValuesFromPrefix("option", $atts ?: []));

            $icon = $this->getValuesFromPrefix("ico", $atts ?: []);

            if(!empty($icon)){
                $marker_script .= "
                    icon = L.icon(".json_encode($icon).");
                ";
            }


            $marker_script .= "
                markerOptions =  ".json_encode($options).";
                icon && (markerOptions.icon = icon);

	            marker = L.marker([{$lat}, {$lng}], markerOptions);";


            if (empty($lat) && empty($lng)) {
                /* update lat lng to previous map's center */
                $marker_script .= "
                    if ( is_image &&
                        !previous_image.is_loaded) {
                        previous_image_onload = previous_image.onload;
                        previous_image.onload = function () {
                            previous_image_onload();
                            marker.setLatLng( previous_map.getCenter() );
                        };
                    } else {
                        marker.setLatLng( previous_map.getCenter() );
                    }
                    ";
            }

            if($options['draggable']){
                $marker_script .= "
                        marker.on('dragend', function () {
                            var latlng = this.getLatLng(),
                                lat = latlng.lat,
                                lng = latlng.lng;
                            if (is_image) {
                                console.log('[leaflet-marker y=' + lat + ' x=' + lng + ']');
                            } else {
                                console.log('[leaflet-marker lat=' + lat + ' lng=' + lng + ']');
                            }
                        });
                    ";
            }

            $marker_script .= "
                marker.addTo( previous_map );
            ";

            $message = empty($message) ? (empty($content) ? '' : $content) : $message;

            if (!empty($message)) {

                $message = str_replace("\n", '', $message);

                $marker_script .= "marker.bindPopup('$message')";

                if ($visible) {

                    $marker_script .= ".openPopup()";

                }

                $marker_script .= ";
                ";

            }

            $marker_script .= "
                    WPLeafletMapPlugin.markers.push( marker );
            }); // end add function
            </script>";

            return $marker_script;
        }

        public function line_shortcode($atts, $content = null)
        {
            /* add to previous map */
            if (!$this::$leaflet_map_count) {
                return '';
            }
            $leaflet_map_count = $this::$leaflet_map_count;

            if (!empty($atts)) extract($atts);

            $color = empty($color) ? "black" : $color;
            $fitline = empty($fitline) ? 0 : $fitline;

            $locations = Array();

            if (!empty($addresses)) {
                $addresses = preg_split('/\s?[;|\/]\s?/', $addresses);
                foreach ($addresses as $address) {
                    if (trim($address)) {
                        $geocoded = $this::geocoder($address);
                        $locations[] = Array($geocoded->{'lat'}, $geocoded->{'lng'});
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

            $marker_script = "<script>
            WPLeafletMapPlugin.add(function () {
                var marker,
                    previous_map = WPLeafletMapPlugin.maps[ {$leaflet_map_count} - 1 ],
                    line = L.polyline($location_json, { color : '$color'}),
                    fitline = $fitline;
                line.addTo( previous_map );

                if (fitline) {
                    // zoom the map to the polyline
                    previous_map.fitBounds( line.getBounds() );
                }

                WPLeafletMapPlugin.lines.push( line );

            });
            </script>";

            return $marker_script;
        }

    } /* end class */

    register_activation_hook(__FILE__, array('Leaflet_Map_Plugin', 'activate'));
    register_uninstall_hook(__FILE__, array('Leaflet_Map_Plugin', 'uninstall'));

    $leaflet_map_plugin = new Leaflet_Map_Plugin();
}