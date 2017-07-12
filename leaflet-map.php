<?php
    /*
    Plugin Name: Leaflet Map
    Plugin URI: https://wordpress.org/plugins/leaflet-map/
    Description: A plugin for creating a Leaflet JS map with a shortcode. Boasts two free map tile services and three free geocoders.
    Author: bozdoz
    Author URI: https://twitter.com/bozdoz/
    Version: 2.7.8
    License: GPL2

    Leaflet Map is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    any later version.
     
    Leaflet Map is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
     
    You should have received a copy of the GNU General Public License
    along with Leaflet Map. If not, see https://github.com/bozdoz/wp-plugin-leaflet-map/blob/master/LICENSE.
    */

if (!class_exists('Leaflet_Map_Plugin')) {
    
    class Leaflet_Map_Plugin {

        /*
        * Number of maps on page
        * @var int $leaflet_map_count
        */
        public static $leaflet_map_count;

        /*
        * Default values and admin form information
        * @var array $defaults
        */
        public static $defaults = array(
            'leaflet_default_lat' => array(
                'default'=>'44.67',
                'type' => 'text',
                'helptext' => 'Default latitude for maps or adjust for each map like so: <br /> <code>[leaflet-map lng="44.67"]</code>'
            ),
            'leaflet_default_lng' => array(
                'default'=>'-63.61',
                'type' => 'text',
                'helptext' => 'Default longitude for maps or adjust for each map like so: <br /> <code>[leaflet-map lng="-63.61"]</code>'
            ),
            'leaflet_default_zoom' => array(
                'default'=>'12',
                'type' => 'text',
                'helptext' => 'Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map zoom="5"]</code>'
            ),
            'leaflet_default_height' => array(
                'default'=>'250',
                'type' => 'text',
                'helptext' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>'
            ),
            'leaflet_default_width' => array(
                'default'=>'100%',
                'type' => 'text',
                'helptext' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map width="100%"]</code>'
            ),
            'leaflet_show_zoom_controls' => array(
                'default' => '0',
                'type' => 'checkbox',
                'helptext' => 'The zoom buttons can be large and annoying.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map zoomcontrol="0"]</code>'
            ),
            'leaflet_scroll_wheel_zoom' => array(
                'default' => '0',
                'type' => 'checkbox',
                'helptext' => 'Disable zoom with mouse scroll wheel.  Sometimes someone wants to scroll down the page, and not zoom the map.  Enable or disable per map in shortcode: <br/> <code>[leaflet-map scrollwheel="0"]</code>'
            ),
            'leaflet_double_click_zoom' => array(
                'default' => '0',
                'type' => 'checkbox',
                'helptext' => 'If enabled, your maps will zoom with a double click.  By default it is disabled: If we\'re going to remove zoom controls and have scroll wheel zoom off by default, we might as well stick to our guns and not zoom the map.  Enable or disable per map in shortcode: <br/> <code>[leaflet-map doubleClickZoom=false]</code>'
            ),
            'leaflet_default_min_zoom' => array(
                'default' => '0',
                'type' => 'text',
                'helptext' => 'Restrict the viewer from zooming in past the minimum zoom.  Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map min_zoom="1"]</code>'
            ),
            'leaflet_default_max_zoom' => array(
                'default' => '20',
                'type' => 'text',
                'helptext' => 'Restrict the viewer from zooming out past the maximum zoom.  Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map max_zoom="10"]</code>'
            ),
            'leaflet_default_tiling_service' => array(
                'default' => 'other',
                'type' => 'select',
                'options' => array(
                    'other' => 'I will provide my own map tile URL',
                    'mapquest' => 'MapQuest (I have an app key)',
                ),
                'helptext' => 'Choose a tiling service or provide your own.'
            ),
            'leaflet_mapquest_appkey' => array(
                'default' => 'supply-an-app-key-if-you-choose-mapquest',
                'type' => 'text',
                'noreset' => true,
                'helptext' => 'If you choose MapQuest, you must provide an app key. <a href="https://developer.mapquest.com/plan_purchase/steps/business_edition/business_edition_free/register" target="_blank">Sign up</a>, then <a href="https://developer.mapquest.com/user/me/apps" target="_blank">Create a new app</a> then supply the "Consumer Key" here.'
            ),
            'leaflet_map_tile_url' => array(
                'default'=>'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                'type' => 'text',
                'helptext' => 'See more tile servers here: <a href="http://wiki.openstreetmap.org/wiki/Tile_servers" target="_blank">here</a>.  Please note(!): free tiles from MapQuest have been discontinued without use of an app key (free accounts available) (see <a href="http://devblog.mapquest.com/2016/06/15/modernization-of-mapquest-results-in-changes-to-open-tile-access/" target="_blank">blog post</a>). Can be set per map with the shortcode <br/> <code>[leaflet-map tileurl=http://{s}.tile.stamen.com/watercolor/{z}/{x}/{y}.jpg subdomains=abcd]</code>'
            ),
            'leaflet_map_tile_url_subdomains' => array(
                'default'=>'abc',
                'type' => 'text',
                'helptext' => 'Some maps get tiles from multiple servers with subdomains such as a,b,c,d or 1,2,3,4; can be set per map with the shortcode <br/> <code>[leaflet-map subdomains="1234"]</code>',
            ),
            'leaflet_js_url' => array(
                'default'=>'https://unpkg.com/leaflet@1.1.0/dist/leaflet.js',
                'type' => 'text',
                'helptext' => 'If you host your own Leaflet files, specify the URL here.'
            ),
            'leaflet_css_url' => array(
                'default'=>'https://unpkg.com/leaflet@1.1.0/dist/leaflet.css',
                'type' => 'text',
                'helptext' => 'Save as above.'
            ),
            'leaflet_default_attribution' => array(
                'default' => "<a href=\"http://leafletjs.com\" title=\"A JS library for interactive maps\">Leaflet</a>; \r\n© <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors",
                'type' => 'textarea',
                'helptext' => 'Attribution to a custom tile url.  Use semi-colons (;) to separate multiple.'
            ),
            'leaflet_geocoder' => array(
                'default' => 'google',
                'type' => 'select',
                'options' => array(
                    'google' => 'Google Maps',
                    'osm' => 'OpenStreetMap Nominatim',
                    'dawa' => 'Danmarks Adressers'
                ),
                'helptext' => 'Select the Geocoding provider to use to retrieve addresses defined in shortcode.'
            ),
            // not in admin
            'leaflet_geocoded_locations' => array()
        );

        /*
        *
        * Initialize plugin
        *
        */

        public function __construct() {
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'admin_menu'));
            add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_and_register') );
            add_action( 'admin_enqueue_scripts', array(&$this, 'enqueue_and_register') );

            add_shortcode('leaflet-map', array(&$this, 'map_shortcode'));
            add_shortcode('leaflet-marker', array(&$this, 'marker_shortcode'));
            add_shortcode('leaflet-line', array(&$this, 'line_shortcode'));
            add_shortcode('leaflet-image', array(&$this, 'image_shortcode'));
            add_shortcode('leaflet-geojson', array(&$this, 'geojson_shortcode'));
            add_shortcode('leaflet-kml', array(&$this, 'kml_shortcode'));

            /* allow maps on excerpts */
            /* should be optional somehow (admin setting?) */
            add_filter('the_excerpt', 'do_shortcode');

            /* add settings to plugin page */
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'plugin_action_links'));
        }

        /*
        *
        * Triggered when user activates plugin
        *
        */

        public static function activate () {
            /* set default values to db */
            foreach(self::$defaults as $name=>$atts) {
                $value = isset($atts['default']) ? $atts['default'] : $atts;

                add_option($name, $value);
            }
        }
        
        /*
        *
        * Triggered when user uninstalls/removes plugin
        *
        */

        public static function uninstall () {            
            /* remove geocoded locations */
            $locations = get_option('leaflet_geocoded_locations', array());

            foreach ($locations as $address => $latlng) {
                delete_option('leaflet_' + $address);
            }

            /* remove values from db */
            foreach(self::$defaults as $name=>$atts) {
                delete_option( $name );
            }
        }

        /*
        *
        * Enqueue and register styles and scripts (called in __construct)
        *
        */

        public function enqueue_and_register () {
            /* backwards compatible : leaflet_js_version */
            $version = get_option('leaflet_js_version', '');

            /* defaults from db */
            $defaults = self::$defaults;
            $js_url = get_option('leaflet_js_url', $defaults['leaflet_js_url']['default']);
            $css_url = get_option('leaflet_css_url', $defaults['leaflet_css_url']['default']);

            $js_url = sprintf($js_url, $version);
            $css_url = sprintf($css_url, $version);

            wp_register_style('leaflet_stylesheet', $css_url, Array(), $version, false);
            wp_register_script('leaflet_js', $js_url, Array(), $version, true);

            // new required MapQuest javascript file
            $tiling_service = get_option('leaflet_default_tiling_service','');

            if ($tiling_service == 'mapquest') {
                $mapquest_js_url = 'https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key=%s';
                $mq_appkey = get_option('leaflet_mapquest_appkey','');
                $mapquest_js_url = sprintf($mapquest_js_url, $mq_appkey);

                wp_register_script('leaflet_mapquest_plugin', $mapquest_js_url, Array('leaflet_js'), '2.0', true);
            }
            
            // optional ajax geojson plugin
            wp_register_script('leaflet_ajax_geojson_js', plugins_url('scripts/leaflet-ajax-geojson.js', __FILE__), Array('leaflet_js',), '1.0', false);

            wp_register_script('tmcw_togeojson', 'https://cdn.rawgit.com/mapbox/togeojson/master/togeojson.js', Array('jquery'), '1.0', false);

            wp_register_script('leaflet_ajax_kml_js', plugins_url('scripts/leaflet-ajax-kml.js', __FILE__), Array('tmcw_togeojson', 'leaflet_js', 'leaflet_ajax_geojson_js'), '1.0', false);

            /* run a construct function in the document head for subsequent functions to use (it is lightweight) */
            wp_enqueue_script('leaflet_map_construct', plugins_url('scripts/construct-leaflet-map.js', __FILE__), Array(), '1.0', false);
        }

        /*
        *
        * Admin init registers styles
        *
        * todo: candidate for separate class
        *
        */
        
        public function admin_init () {
            wp_register_style('leaflet_admin_stylesheet', plugins_url('style.css', __FILE__));
        }

        /*
        *
        * Main settings page includes form inputs
        *
        */

        public function settings_page () {
            wp_enqueue_style( 'leaflet_admin_stylesheet' );
            include 'templates/admin.php';
        }

        /*
        *
        * Shortcode page shows example shortcodes and an interactive generator
        *
        */
        public function shortcode_page () {
            wp_enqueue_style( 'leaflet_admin_stylesheet' );
            wp_enqueue_script('custom_plugin_js', plugins_url('scripts/get-shortcode.js', __FILE__), Array('leaflet_js'), false);

            include 'templates/shortcode-helper.php';
        }

        /*
        *
        * Add admin menu page when user in admin area
        *
        */

        public function admin_menu () {
            if (current_user_can('manage_options')) {
                $main_link = 'leaflet-map';
            } else {
                $main_link = 'leaflet-get-shortcode';
            }

            add_menu_page("Leaflet Map", "Leaflet Map", 'manage_options', $main_link, array(&$this, "settings_page"), plugins_url('images/leaf.png', __FILE__));
            add_submenu_page("leaflet-map", "Default Values", "Default Values", 'manage_options', "leaflet-map", array(&$this, "settings_page"));
            add_submenu_page("leaflet-map", "Shortcode Helper", "Shortcode Helper", 'edit_posts', "leaflet-get-shortcode", array(&$this, "shortcode_page"));
        }

        /*
        *
        * Add settings link to the plugin on Installed Plugins page
        *
        */
        public function plugin_action_links ( $links ) {
            $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=leaflet-map') ) .'">Settings</a>';
            return $links;
        }

        /*
        *
        * Geocoder
        *
        * calls the specific geocoder function (chosen in admin or default: google_geocode)
        *
        * todo: candidate for separate class
        *
        * @param string $address    the requested address to look up
        * @return object or null latitude and longitude
        */

        public function geocoder ( $address ) {

            $address = urlencode( $address );

            $geocoder = get_option('leaflet_geocoder', self::$defaults['leaflet_geocoder']['default']);

            $cached_address = 'leaflet_' . $geocoder . '_' . $address;

            /* retrieve cached geocoded location */
            $found_cache = get_option( $cached_address );

            if ( $found_cache ) {
                return $found_cache;
            }

            $geocoding_method = $geocoder . '_geocode';

            try {
                $location = (Object) self::$geocoding_method( $address );
                /* add location */
                add_option($cached_address, $location);

                /* add option key to locations for clean up purposes */
                $locations = get_option('leaflet_geocoded_locations', array());
                array_push($locations, $cached_address);
                update_option('leaflet_geocoded_locations', $locations);

                return $location;
            } catch (Exception $e) {
                return null;
            }
        }

        /*
        *
        * Used by geocoders to make requests via curl or file_get_contents
        *
        * includes a try/catch
        *
        * @param string $url    the urlencoded request url
        * @return varies object from API or null (failed)
        */

        public function get_url( $url ) {
            if (in_array('curl', get_loaded_extensions())) {
                /* try curl */
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

                $data = curl_exec($ch);
                curl_close($ch);

                return $data;
            } else if (ini_get('allow_url_fopen')) {
                /* try file get contents */
                return file_get_contents( $url );
            }

            $error_msg = 'Could not get url: ' . $url;
            throw new Exception( $error_msg );
        }

        /*
        *
        * Google geocoder (https://developers.google.com/maps/documentation/geocoding/start)
        *
        * @param string $address    the urlencoded address to look up
        * @return varies object from API or null (failed)
        */

        public function google_geocode ( $address ) {
            /* Google */
            
            $geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
            $geocode_url .= $address;
            $json = self::get_url($geocode_url);

            if ($json) {
                $json = json_decode($json);
                /* found location */
                if ($json->{'status'} == 'OK') {
                    
                    $location = $json->{'results'}[0]->{'geometry'}->{'location'};

                    return (Object) $location;
                }
            }

            return null;
        }

        /*
        *
        * OpenStreetMap geocoder Nominatim (https://nominatim.openstreetmap.org/)
        *
        * @param string $address    the urlencoded address to look up
        * @return varies object from API or null (failed)
        */

        public function osm_geocode ( $address ) {
            /* OpenStreetMap Nominatim */

            $osm_geocode = 'https://nominatim.openstreetmap.org/?format=json&limit=1&q=';
            $geocode_url = $osm_geocode . $address;
            $json = self::get_url($geocode_url);
            $json = json_decode($json);

            if (is_array($json) && 
                is_object($json[0]) &&
                $json[0]->{'lat'}) {
                // found location
                return (Object) array(
                    'lat' => $json[0]->{'lat'},
                    'lng' => $json[0]->{'lon'},
                );
            } else {
                // not found
                return (Object) array('lat' => 0, 'lng' => 0);
            }
        }

        /*
        *
        * DAWA geocoder (https://dawa.aws.dk)
        *
        * @param string $address    the urlencoded address to look up
        * @return varies object from API or null (failed)
        */

        public function dawa_geocode ( $address ) {
            /* Danish Addresses Web Application */

            $dawa_geocode = 'https://dawa.aws.dk/adresser?format=json&q=';
            $geocode_url = $dawa_geocode . $address;
            $json = self::get_url($geocode_url);
            $json = json_decode($json);

            if (is_array($json) && 
                is_object($json[0]) &&
                $json[0]->{'status'} == 1) {
                /* found location */
                $location = (Object) array(
                    'lat'=>$json[0]->{'adgangsadresse'}->{'adgangspunkt'}->{'koordinater'}[1],
                    'lng'=>$json[0]->{'adgangsadresse'}->{'adgangspunkt'}->{'koordinater'}[0]
                    );

                return $location;
            } 

            return (Object) array('lat' => 0, 'lng' => 0);
        }

        /*
        *
        * Filter for removing nulls from array
        *
        * @param array $arr
        * @return array with nulls removed
        */

        public function filter_null ($arr) {
            if (!function_exists('remove_null')) {
                function remove_null ($var) {
                    return $var !== null;
                }
            }

            return array_filter($arr, 'remove_null');
        }

        /*
        *
        * Map Shortcode
        *
        * Displays map with [leaflet-map ...atts] 
        *
        * JavaScript equivalent : L.map("id");
        *
        * @param array $atts
        * @return string $content produced by adding atts to JavaScript
        */

        public function map_shortcode ( $atts ) {
            
            if (!self::$leaflet_map_count) {
            	self::$leaflet_map_count = 0;
            }
            self::$leaflet_map_count++;

            $leaflet_map_count = self::$leaflet_map_count;

            /* defaults from db */
            $defaults = self::$defaults;
            $default_zoom = get_option('leaflet_default_zoom', $defaults['leaflet_default_zoom']['default']);
            $default_lat = get_option('leaflet_default_lat', $defaults['leaflet_default_lat']['default']);
            $default_lng = get_option('leaflet_default_lng', $defaults['leaflet_default_lng']['default']);
            $default_zoom_control = get_option('leaflet_show_zoom_controls', $defaults['leaflet_show_zoom_controls']['default']);
            $default_height = get_option('leaflet_default_height', $defaults['leaflet_default_height']['default']);
            $default_width = get_option('leaflet_default_width', $defaults['leaflet_default_width']['default']);
            $default_scrollwheel = get_option('leaflet_scroll_wheel_zoom', $defaults['leaflet_scroll_wheel_zoom']['default']);
            $default_doubleclickzoom = get_option('leaflet_double_click_zoom', $defaults['leaflet_double_click_zoom']['default']);
            $default_attribution = get_option('leaflet_default_attribution', $defaults['leaflet_default_attribution']['default']);
            $default_min_zoom = get_option('leaflet_default_min_zoom', $defaults['leaflet_default_min_zoom']['default']);
            $default_max_zoom = get_option('leaflet_default_max_zoom', $defaults['leaflet_default_max_zoom']['default']);

            if ($atts) {
                extract($atts);
            }

            /* only really necessary $atts are the location variables */
            if (!empty($address)) {
                /* try geocoding */
                $location = self::geocoder( $address );
                $lat = $location->{'lat'};
                $lng = $location->{'lng'};
            }

            /* 
                check more user defined $atts against defaults 
            */
            $lat = empty($lat) ? $default_lat : $lat;
            $lng = empty($lng) ? $default_lng : $lng;
            $zoomcontrol = empty($zoomcontrol) ? $default_zoom_control : $zoomcontrol;
            $zoom = empty($zoom) ? $default_zoom : $zoom;
            $min_zoom = empty($min_zoom) ? $default_min_zoom : $min_zoom;
            $max_zoom = empty($max_zoom) ? $default_max_zoom : $max_zoom;
            $scrollwheel = empty($scrollwheel) ? $default_scrollwheel : $scrollwheel;
            $doubleclickzoom = empty($doubleclickzoom) ? $default_doubleclickzoom : $doubleclickzoom;
            $height = empty($height) ? $default_height : $height;
            $width = empty($width) ? $default_width : $width;
            
            /* need to allow 0 or empty for removal of attribution */
            if (!$atts ||
                !array_key_exists('attribution', (array) $atts)) {
                $attribution = $default_attribution;
            }

            $tileurl = empty($tileurl) ? '' : $tileurl;
            $subdomains = empty($subdomains) ? '' : $subdomains;

            /* leaflet script */
            wp_enqueue_style('leaflet_stylesheet');
            wp_enqueue_script('leaflet_js');
            wp_enqueue_script('leaflet_map_init');

            if (wp_script_is('leaflet_mapquest_plugin', 'registered')) {
                // mapquest doesn't accept direct tile access as of July 11, 2016
                wp_enqueue_script('leaflet_mapquest_plugin');
            } else {
                $default_tileurl = get_option('leaflet_map_tile_url', $defaults['leaflet_map_tile_url']['default']);
                $default_subdomains = get_option('leaflet_map_tile_url_subdomains', $defaults['leaflet_map_tile_url_subdomains']['default']);
                $tileurl = empty($tileurl) ? $default_tileurl : $tileurl;
                $subdomains = empty($subdomains) ? $default_subdomains : $subdomains;
            }

            /* allow percent, but add px for ints */
            $height .= is_numeric($height) ? 'px' : '';
            $width .= is_numeric($width) ? 'px' : '';   


            /* allow a bunch of other options */
            // http://leafletjs.com/reference-1.0.3.html#map-closepopuponclick
            $more_options = array(
                'closePopupOnClick' => isset($closepopuponclick) ? $closepopuponclick : NULL,
                'trackResize' => isset($trackresize) ? $trackresize : NULL,
                'boxZoom' => isset($boxzoom) ? $boxzoom : NULL,
                'doubleClickZoom' => isset($doubleclickzoom) ? $doubleclickzoom : NULL,
                'dragging' => isset($dragging) ? $dragging : NULL,
                'keyboard' => isset($keyboard) ? $keyboard : NULL,
                );
            
            // filter out nulls
            $more_options = self::filter_null( $more_options );
            
            // change string booleans to booleans
            $more_options = filter_var_array($more_options, FILTER_VALIDATE_BOOLEAN);

            // wrap as JSON
            if ($more_options) {
                $more_options = json_encode( $more_options );
            } else {
                $more_options = '{}';
            }

            /* should be iterated for multiple maps */
            $content = '<div id="leaflet-wordpress-map-'.$leaflet_map_count.'" class="leaflet-wordpress-map" style="height:'.$height.'; width:'.$width.';"></div>';

            $content .= "<script>
            WPLeafletMapPlugin.add(function () {
                var baseUrl = '{$tileurl}',
                    base = (!baseUrl && window.MQ) ? MQ.mapLayer() : L.tileLayer(baseUrl, { 
                       subdomains: '{$subdomains}'
                    }),
                    options = L.Util.extend({}, {
                        maxZoom: {$max_zoom},
                        minZoom: {$min_zoom},
                        layers: [base],
                        zoomControl: {$zoomcontrol},
                        scrollWheelZoom: {$scrollwheel},
                        doubleClickZoom: {$doubleclickzoom},
                        attributionControl: false
                    }, {$more_options}),
                    map = L.map('leaflet-wordpress-map-{$leaflet_map_count}', options).setView([{$lat}, {$lng}], {$zoom});";
                
                if ($attribution) {
                    /* add any attributions, semi-colon-separated */
                    $attributions = explode(';', $attribution);

                    $content .= "var attControl = L.control.attribution({prefix:false}).addTo(map);";

                    foreach ($attributions as $a) {
                        $a = trim($a);
                        $content .= "attControl.addAttribution('{$a}');";
                    }
                }

                $content .= "
                WPLeafletMapPlugin.maps.push(map);
            }); // end add
            </script>";

            return $content;
        }

        /*
        *
        * Image Shortcode
        *
        * Displays map with [leaflet-image source="path/to/image.jpg"] 
        *
        * JavaScript equivalent : L.imageOverlay('path/to/image.jpg');
        *
        * @param array $atts
        * @return string $content produced by adding atts to JavaScript
        */

        public function image_shortcode ( $atts ) {
            
            /* get map count for unique id */
            if (!self::$leaflet_map_count) {
                self::$leaflet_map_count = 0;
            }
            self::$leaflet_map_count++;

            $leaflet_map_count = self::$leaflet_map_count;

            /* defaults from db */
            $defaults = self::$defaults;
            $default_zoom_control = get_option('leaflet_show_zoom_controls', $defaults['leaflet_show_zoom_controls']['default']);
            $default_height = get_option('leaflet_default_height', $defaults['leaflet_default_height']['default']);
            $default_width = get_option('leaflet_default_width', $defaults['leaflet_default_width']['default']);
            $default_scrollwheel = get_option('leaflet_scroll_wheel_zoom', $defaults['leaflet_scroll_wheel_zoom']['default']);

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
            
            $content = '<div id="leaflet-wordpress-image-'.$leaflet_map_count.'" class="leaflet-wordpress-map" style="height:'.$height.'; width:'.$width.';"></div>';

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

        /*
        *
        * Sanitize JSON
        *
        * Takes options for filtering/correcting inputs for use in JavaScript
        *
        * @param array $arr     user-input array
        * @param array $args    array with key-value definitions on how to convert values
        * @return array corrected for JavaScript
        */

        public function json_sanitize ($arr, $args) {
            // remove nulls
            $arr = self::filter_null( $arr );

            // sanitize output
            $args = array_intersect_key($args, $arr);
            $arr = filter_var_array($arr, $args);

            return json_encode( $arr );
        }

        /*
        *
        * Get Style JSON for map shapes/geojson (svg or canvas)
        *
        * Takes atts for creating shapes on the map
        *
        * @param array $atts    user-input array
        * @return array corrected for JavaScript
        */

        public function get_style_json ($atts) {
            if ($atts) {
                extract($atts);
            }

            // from http://leafletjs.com/reference-1.0.3.html#path
            $style = array(
                'stroke' => isset($stroke) ? $stroke : NULL,
                'color' => isset($color) ? $color : NULL,
                'weight' => isset($weight) ? $weight : NULL,
                'opacity' => isset($opacity) ? $opacity : NULL,
                'lineCap' => isset($linecap) ? $linecap : NULL,
                'lineJoin' => isset($linejoin) ? $linejoin : NULL,
                'dashArray' => isset($dasharray) ? $dasharray : NULL,
                'dashOffset' => isset($dashoffset) ? $dashoffset : NULL,
                'fill' => isset($fill) ? $fill : NULL,
                'fillColor' => isset($fillcolor) ? $fillcolor : NULL,
                'fillOpacity' => isset($fillopacity) ? $fillopacity : NULL,
                'fillRule' => isset($fillrule) ? $fillrule : NULL,
                'className' => isset($classname) ? $classname : NULL,
                );

            $args = array(
                'stroke' => FILTER_VALIDATE_BOOLEAN,
                'color' => FILTER_SANITIZE_STRING,
                'weight' => FILTER_VALIDATE_FLOAT,
                'opacity' => FILTER_VALIDATE_FLOAT,
                'lineCap' => FILTER_SANITIZE_STRING,
                'lineJoin' => FILTER_SANITIZE_STRING,
                'dashArray' => FILTER_SANITIZE_STRING,
                'dashOffset' => FILTER_SANITIZE_STRING,
                'fill' => FILTER_VALIDATE_BOOLEAN,
                'fillColor' => FILTER_SANITIZE_STRING,
                'fillOpacity' => FILTER_VALIDATE_FLOAT,
                'fillRule' => FILTER_SANITIZE_STRING,
                'className' => FILTER_SANITIZE_STRING
                );

            return self::json_sanitize($style, $args);
        }

        /*
        *
        * Get Shape
        *
        * Used for generating shapes from GeoJSON or KML/KMZ
        *
        * @param array $atts        user-input array
        * @param string $wp_script  script to enqueue (varies)
        * @param string $L_method   which private function to call
        * @param string $default    test/example URL (if src is not present in $atts)
        * @return string JavaScript
        */

        public function get_shape ( $atts, $wp_script, $L_method, $default = '' ) {
            wp_enqueue_script( $wp_script );

            if ($atts) {
                extract($atts);
            }

            /* only required field for geojson */
            $src = empty($src) ? $default : $src;

            $style_json = self::get_style_json( $atts );

            $fitbounds = empty($fitbounds) ? 0 : $fitbounds;

            $popup_text = empty($popup_text) ? '' : $popup_text;
            $popup_property = empty($popup_property) ? '' : $popup_property;

            $geojson_script = "<script>
                WPLeafletMapPlugin.add(function () {
                    var previous_map = WPLeafletMapPlugin.getCurrentMap(),
                        src = '{$src}',
                        default_style = {$style_json},
                        rewrite_keys = {
                            fill : 'fillColor',
                            'fill-opacity' : 'fillOpacity',
                            stroke : 'color',
                            'stroke-opacity' : 'opacity',
                            'stroke-width' : 'width',
                        },
                        layer = L.{$L_method}(src, {
                            style : layerStyle,
                            onEachFeature : onEachFeature
                        }),
                        fitbounds = {$fitbounds},
                        popup_text = '{$popup_text}',
                        popup_property = '{$popup_property}';
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
                            text = popup_text || props[ popup_property ];
                        if (text) {
                            layer.bindPopup( text );
                        }
                    }          
                });
                </script>";

            return $geojson_script;
        }

        /*
        *
        * GeoJSON Shortcode
        *
        * Uses get_shape above
        *
        * @param array $atts        user-input array
        * @return string JavaScript
        */
        public function geojson_shortcode ( $atts ) {

            return self::get_shape( $atts, 'leaflet_ajax_geojson_js', 'ajaxGeoJson', 'https://rawgit.com/bozdoz/567817310f102d169510d94306e4f464/raw/2fdb48dafafd4c8304ff051f49d9de03afb1718b/map.geojson');
            
        }

        /*
        *
        * KML/KMZ Shortcode
        *
        * Uses get_shape above
        *
        * @param array $atts        user-input array
        * @return string JavaScript
        */

        public function kml_shortcode ( $atts ) {
            
            return self::get_shape( $atts, 'leaflet_ajax_kml_js', 'ajaxKML', 'https://cdn.rawgit.com/mapbox/togeojson/master/test/data/polygon.kml');
            
        }

        /*
        *
        * Add Popups to Shapes
        *
        * @param array $atts        user-input array
        * @param string $content    text to display
        * @param string $shape      JavaScript variable for shape
        * @return string JavaScript
        */

        public function add_popup_to_shape ($atts, $content, $shape) {
            if (!empty($atts)) extract($atts);

            $message = empty($message) ? (empty($content) ? '' : $content) : $message;
            $visible = empty($visible) ? false : ($visible == 'true');

            $output = '';

            if (!empty($message)) {
                $message = str_replace("\n", '', $message);

                $output .= "$shape.bindPopup('$message')";

                if ($visible) {
                    $output .= ".openPopup()";
                }

                $output .= ";";
            }

            return $output;
        }

        /*
        *
        * Marker Shortcode
        *
        * @param array $atts        user-input array
        * @param string $content    user-input content (allows HTML)
        * @return string content for post/page
        */

        public function marker_shortcode ( $atts, $content = null ) {

            if (!empty($atts)) extract($atts);

            if (!empty($address)) {
                $location = self::geocoder( $address );
                $lat = $location->{'lat'};
                $lng = $location->{'lng'};
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

            $options = self::json_sanitize($options, $args);
            
            if ($options === '[]') {
                $options = '{}';
            }

            $marker_script = "<script>
            WPLeafletMapPlugin.add(function () {
                var marker_options = (function () {
                        var _options = {$options},
                            iconArrays = ['iconSize', 'iconAnchor', 'shadowSize', 'shadowAnchor'];
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
                    marker = L.marker([{$lat}, {$lng}], marker_options),
                    previous_map = WPLeafletMapPlugin.getCurrentMap(),
                    is_image = previous_map.is_image_map,
                    previous_map_onload;
                ";

                if (empty($lat) && empty($lng)) {
                    /* update lat lng to previous map's center */
                    $marker_script .= "
                    if ( is_image && 
                        !previous_map.is_loaded) {
                        previous_map_onload = previous_map.onload;
                        previous_map.onload = function () {
                            if (typeof(previous_map_onload) === 'function') {
                                previous_map_onload();
                            }
                            marker.setLatLng( previous_map.getCenter() );
                        };
                    } else {
                        marker.setLatLng( previous_map.getCenter() );
                    }
                    ";
                }

            $marker_script .= "
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

            marker.addTo( previous_map );
            ";
            
            $marker_script .= self::add_popup_to_shape($atts, $content, 'marker');

            $marker_script .= "
                    WPLeafletMapPlugin.markers.push( marker );
            }); // end add function
            </script>";

            return $marker_script;
        }

        /*
        *
        * Line Shortcode
        *
        * @param array $atts        user-input array
        * @param string $content    user-input content (allows HTML)
        * @return string content for post/page
        */

        public function line_shortcode ( $atts, $content = null ) {
            if (!empty($atts)) extract($atts);
            
            $style_json = self::get_style_json( $atts );

            $fitbounds = empty($fitbounds) ? 0 : $fitbounds;

            // backwards compatible `fitline`
            if (!empty($fitline)) {
                $fitbounds = $fitline;
            }

            $locations = Array();

            if (!empty($addresses)) {
                $addresses = preg_split('/\s?[;|\/]\s?/', $addresses);
                foreach ($addresses as $address) {
                    if (trim($address)) {
                        $geocoded = self::geocoder($address);
                        $locations[] = Array(floatval($geocoded->{'lat'}), floatval($geocoded->{'lng'}));
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

            $line_script = "<script>
            WPLeafletMapPlugin.add(function () {
                var previous_map = WPLeafletMapPlugin.getCurrentMap(),
                    line = L.polyline($location_json, {$style_json}),
                    fitbounds = $fitbounds;
                line.addTo( previous_map );
                if (fitbounds) {
                    // zoom the map to the polyline
                    previous_map.fitBounds( line.getBounds() );
                }";

            $line_script .= self::add_popup_to_shape($atts, $content, 'line');

            $line_script .= "
                WPLeafletMapPlugin.lines.push( line );
            });
            </script>";

            return $line_script;
        }
    }

    register_activation_hook( __FILE__, array('Leaflet_Map_Plugin', 'activate'));
    register_uninstall_hook( __FILE__, array('Leaflet_Map_Plugin', 'uninstall') );

    $leaflet_map_plugin = new Leaflet_Map_Plugin();
}
?>