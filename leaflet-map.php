<?php
    /*
    Plugin Name: Leaflet Map
    Plugin URI: http://twitter.com/bozdoz/
    Description: A plugin for creating a Leaflet JS map with a shortcode.
    Version: 1.7
    Author: bozdoz
    Author URI: http://twitter.com/bozdoz/
    License: GPL2
    */

if (!class_exists('Leaflet_Map_Plugin')) {
    
    class Leaflet_Map_Plugin {

        public static $defaults = array (
            'text' => array(
                'leaflet_map_tile_url' => 'http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpg',
                'leaflet_map_tile_url_subdomains' => '1234',
                'leaflet_js_version' => '0.7.3',
                'leaflet_default_zoom' => '16',
                'leaflet_default_height' => '250',
                'leaflet_default_width' => '100%',
                ),
            'checks' => array(
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
                'leaflet_js_version' => '0.7.3 is newest as of this plugin\'s conception',
                'leaflet_default_zoom' => 'Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map zoom="5"]</code>',
                'leaflet_default_height' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>',
                'leaflet_default_width' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>',
                'leaflet_show_attribution' => 'The default URL requires attribution by its terms of use.  If you want to change the URL, you may remove the attribution.  Also, you can set this per map in the shortcode (1 for enabled and 0 for disabled): <br/> <code>[leaflet-map show_attr="1"]</code>',
                'leaflet_show_zoom_controls' => 'The zoom buttons can be large and annoying.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map zoomcontrol="0"]</code>',
                'leaflet_scroll_wheel_zoom' => 'Disable zoom with mouse scroll wheel.  Sometimes someone wants to scroll down the page, and not zoom the map.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map scrollwheel="0"]</code>',
            );

        public function __construct() {
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'admin_menu'));

            add_shortcode('leaflet-map', array(&$this, 'map_shortcode'));
            add_shortcode('leaflet-marker', array(&$this, 'marker_shortcode'));
            add_shortcode('leaflet-image', array(&$this, 'image_shortcode'));

            add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_and_register') );
        }

        public static function activate () {
            /* set default values to db */
            foreach(self::$defaults as $arrs) {
                foreach($arrs as $k=>$v) {
                    add_option($k, $v);
                }
            }
        }
        
        public static function uninstall () {
            
            /* remove geocoded locations */
            $locations = get_option('leaflet_geocoded_locations', array());

            foreach ($locations as $address => $latlng) {
                delete_option('leaflet_' + $address);
            }

            /* remove values from db */
            foreach (self::$defaults as $arrs) {
                foreach($arrs as $k=>$v) {
                    delete_option($k);
                }
            }
        }

        public function enqueue_and_register () {
            $defaults = $this::$defaults['text'];

            /* defaults from db */
            $version = get_option('leaflet_js_version', $defaults['leaflet_js_version']);

            // add style to every page
            wp_enqueue_style('leaflet_stylesheet', 'http://cdn.leafletjs.com/leaflet-'.$version.'/leaflet.css', Array(), $version, false);

            wp_register_script('leaflet_js', 'http://cdn.leafletjs.com/leaflet-'.$version.'/leaflet.js', Array(), $version, true);
            
            /* run an init function because other wordpress plugins don't play well with their window.onload functions */
            wp_register_script('leaflet_map_init', plugins_url('scripts/init-leaflet-map.js', __FILE__), Array('leaflet_js'), '1.0', true);

            /* run a construct function in the document head for the init function to use */
            wp_enqueue_script('leaflet_map_construct', plugins_url('scripts/construct-leaflet-map.js', __FILE__), Array(), '1.0', false);

        }
        
        public function admin_init () {
            wp_register_style('leaflet_admin_stylesheet', plugins_url('style.css', __FILE__));
        }

        public function admin_menu () {
            add_menu_page("Leaflet Map", "Leaflet Map", 'manage_options', "leaflet-map", array(&$this, "settings_page"), plugins_url('images/leaf.png', __FILE__), 100);
            add_submenu_page("leaflet-map", "Default Values", "Default Values", 'manage_options', "leaflet-map", array(&$this, "settings_page"));
            add_submenu_page("leaflet-map", "Shortcodes", "Shortcodes", 'manage_options', "leaflet-get-shortcode", array(&$this, "shortcode_page"));
        }

        public function settings_page () {

            wp_enqueue_style( 'leaflet_admin_stylesheet' );

            include 'templates/admin.php';
        }

        public function shortcode_page () {

            wp_enqueue_style( 'leaflet_admin_stylesheet' );
            wp_enqueue_script('custom_plugin_js', plugins_url('scripts/get-shortcode.js', __FILE__), false);

            include 'templates/find-on-map.php';
        }

        public function google_geocode ( $address ) {
            
            $address = urlencode($address);
            $cached_address = 'leaflet_' . $address;

            /* retrieve cached geocoded location */
            if (get_option($cached_address)) {
                return get_option($cached_address);
            }

            /* try geocoding */
            $google_geocode = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
            $geocode_url = $google_geocode . $address;
            $json = file_get_contents($geocode_url);
            $json = json_decode($json);

            /* found location */
            if ($json->{'status'} == 'OK') {
                
                $location = $json->{'results'}[0]->{'geometry'}->{'location'};

                /* add location */
                add_option($cached_address, $location);

                /* add option key to locations for clean up purposes */
                $locations = get_option('leaflet_geocoded_locations', array());
                array_push($locations, $cached_address);
                update_option('leaflet_geocoded_locations', $locations);
                
                return $location;
            }

            /* else */
            return (Object) array('lat' => 0, 'lng' => 0);
        }

        /* count map shortcodes to allow for multiple */
        public static $leaflet_map_count;

        public function map_shortcode ( $atts ) {
            
            if (!$this::$leaflet_map_count) {
            	$this::$leaflet_map_count = 0;
            }
            $this::$leaflet_map_count++;

            $leaflet_map_count = $this::$leaflet_map_count;

            $defaults = array_merge($this::$defaults['text'], $this::$defaults['checks']);

            /* defaults from db */
            $default_zoom = get_option('leaflet_default_zoom', $defaults['leaflet_default_zoom']);
            $default_zoom_control = get_option('leaflet_show_zoom_controls', $defaults['leaflet_show_zoom_controls']);
            $default_height = get_option('leaflet_default_height', $defaults['leaflet_default_height']);
            $default_width = get_option('leaflet_default_width', $defaults['leaflet_default_width']);
            $default_show_attr = get_option('leaflet_show_attribution', $defaults['leaflet_show_attribution']);
            $default_tileurl = get_option('leaflet_map_tile_url', $defaults['leaflet_map_tile_url']);
            $default_subdomains = get_option('leaflet_map_tile_url_subdomains', $defaults['leaflet_map_tile_url_subdomains']);
            $default_scrollwheel = get_option('leaflet_scroll_wheel_zoom', $defaults['leaflet_scroll_wheel_zoom']);

            /* leaflet script */
            wp_enqueue_script('leaflet_js');
            wp_enqueue_script('leaflet_map_init');

            if ($atts) {
                extract($atts);
            }

            /* only really necessary $atts are the location variables */
            if (!empty($address)) {
                /* try geocoding */
                $location = $this::google_geocode($address);
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
            
            /* allow percent, but add px for ints */
            $height .= is_numeric($height) ? 'px' : '';
            $width .= is_numeric($width) ? 'px' : '';   
            
            /* should be iterated for multiple maps */
            $content = '<div id="leaflet-wordpress-map-'.$leaflet_map_count.'" class="leaflet-wordpress-map" style="height:'.$height.'; width:'.$width.';"></div>';

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
                
                if ($show_attr) {
                    /* add attribution to MapQuest and OSM */
                    $content .= '
                        map.attributionControl.addAttribution("Tiles Courtesy of <a href=\"http://www.mapquest.com/\" target=\"_blank\">MapQuest</a> <img src=\"http://developer.mapquest.com/content/osm/mq_logo.png\" />");

                        map.attributionControl.addAttribution("© <a href=\"http://www.openstreetmap.org/\">OpenStreetMap</a> contributors");';
                }

                $content .= '
                WPLeafletMapPlugin.maps.push(map);
            }); // end add
            </script>
            ';

            return $content;
        }

        public function image_shortcode ( $atts ) {
            
            /* get map count for unique id */
            if (!$this::$leaflet_map_count) {
                $this::$leaflet_map_count = 0;
            }
            $this::$leaflet_map_count++;

            $leaflet_map_count = $this::$leaflet_map_count;

            $defaults = array_merge($this::$defaults['text'], $this::$defaults['checks']);

            /* defaults from db */
            $default_zoom_control = get_option('leaflet_show_zoom_controls', $defaults['leaflet_show_zoom_controls']);
            $default_height = get_option('leaflet_default_height', $defaults['leaflet_default_height']);
            $default_width = get_option('leaflet_default_width', $defaults['leaflet_default_width']);
            $default_scrollwheel = get_option('leaflet_scroll_wheel_zoom', $defaults['leaflet_scroll_wheel_zoom']);

            /* leaflet script */
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

        public function marker_shortcode ( $atts ) {

            /* add to previous map */
            if (!$this::$leaflet_map_count) {
            	return '';
            }

            $leaflet_map_count = $this::$leaflet_map_count;
            
            if (!empty($atts)) extract($atts);

            $draggable = empty($draggable) ? 'false' : $draggable;
            $visible = empty($visible) ? false : ($visible == 'true');

            if (!empty($address)) {
                $location = $this::google_geocode($address);
                $lat = $location->{'lat'};
                $lng = $location->{'lng'};
            }

            $content = "<script>
            WPLeafletMapPlugin.add(function () {
                var marker,
                    map_count = {$leaflet_map_count},
                    draggable = {$draggable},
                    previous_map = WPLeafletMapPlugin.maps[ map_count - 1 ],
                    is_image = previous_map.is_image_map,
                    image_len = WPLeafletMapPlugin.images.length,
                    previous_image = WPLeafletMapPlugin.images[ image_len - 1 ],
                    previous_image_onload;
                ";

            	/* add to user contributed lat lng */
                $lat = empty($lat) ? ( empty($y) ? '0' : $y ) : $lat;
                $lng = empty($lng) ? ( empty($x) ? '0' : $x ) : $lng;

	            $content .= "
	            marker = L.marker([{$lat}, {$lng}], { draggable : draggable });";


                if (empty($lat) && empty($lng)) {
                    /* update lat lng to previous map's center */
                    $content .= "
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

            $content .= "
            if (draggable) {
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
            }

            marker.addTo( previous_map );
            ";
            
            if (!empty($message)) {

                $content .= "marker.bindPopup('$message')";

                if ($visible) {

                    $content .= ".openPopup()";                    

                }

                $content .= ";
                ";

            }

            $content .= "
                    WPLeafletMapPlugin.markers.push( marker );
            }); // end add function
            </script>";

            return $content;
        }

    } /* end class */

    register_activation_hook( __FILE__, array('Leaflet_Map_Plugin', 'activate'));
    register_uninstall_hook( __FILE__, array('Leaflet_Map_Plugin', 'uninstall') );

    $leaflet_map_plugin = new Leaflet_Map_Plugin();
}
?>
