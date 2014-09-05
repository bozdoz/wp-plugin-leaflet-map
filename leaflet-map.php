<?php
    /*
    Plugin Name: Leaflet Map
    Plugin URI: http://twitter.com/bozdoz/
    Description: A plugin for creating a Leaflet JS map with a shortcode.
    Version: 1.2
    Author: Benjamin J DeLong
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

        /* count map shortcodes to allow for multiple */
        public static $leaflet_map_count;

        /* leave marker variables global for possibly manipulation */
        public static $leaflet_marker_count;

        public function __construct() {
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'admin_menu'));
            add_shortcode('leaflet-map', array(&$this, 'map_shortcode'));
            add_shortcode('leaflet-marker', array(&$this, 'marker_shortcode'));
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
            /* remove values from db */
            foreach(self::$defaults as $arrs) {
            	foreach($arrs as $k=>$v) {
            		delete_option($k);
            	}
            }
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
            /* try geocoding */
            $google_geocode = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
            $geocode_url = $google_geocode . urlencode($address);
            $json = file_get_contents($geocode_url);
            $json = json_decode($json);

            if ($json->{'status'} == 'OK') {
                return $json->{'results'}[0]->{'geometry'}->{'location'};
            }
            return array('lat' => 0, 'lng' => 0);
        }

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
            $version = get_option('leaflet_js_version', $defaults['leaflet_js_version']);

            /* leaflet style and script */
            wp_enqueue_style('leaflet_stylesheet', 'http://cdn.leafletjs.com/leaflet-'.$version.'/leaflet.css', false);
            wp_enqueue_script('leaflet_js', 'http://cdn.leafletjs.com/leaflet-'.$version.'/leaflet.js', false);

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
            var map_{$leaflet_map_count};
            jQuery(function () {
                var baseURL = '{$tileurl}';
               
                var base = L.tileLayer(baseURL, { 
                   subdomains: '{$subdomains}'
                   });
                map_{$leaflet_map_count} = L.map('leaflet-wordpress-map-{$leaflet_map_count}', 
                	{
                		layers: [base],
                		zoomControl: {$zoomcontrol},
                		scrollWheelZoom: {$scrollwheel}
                	}).setView([{$lat}, {$lng}], {$zoom});";
			
			if ($show_attr) {
                /* add attribution to MapQuest and OSM */
                $content .= 'map_'.$leaflet_map_count.'.attributionControl.addAttribution("Tiles Courtesy of <a href=\"http://www.mapquest.com/\" target=\"_blank\">MapQuest</a> <img src=\"http://developer.mapquest.com/content/osm/mq_logo.png\" />");';
                $content .= 'map_'.$leaflet_map_count.'.attributionControl.addAttribution("© <a href=\"http://www.openstreetmap.org/\">OpenStreetMap</a> contributors");';
            }

            $content .= '
        	});
            </script>
            ';

            return $content;
        }

        public function marker_shortcode ( $atts ) {

            /* add to previous map */
            if (!$this::$leaflet_map_count) {
            	return '';
            }

            /* increment marker count */
            if (!$this::$leaflet_marker_count) {
            	$this::$leaflet_marker_count = 0;
            }
            $this::$leaflet_marker_count++;

            $leaflet_map_count = $this::$leaflet_map_count;
            $leaflet_marker_count = $this::$leaflet_marker_count;

            $content = "<script>
            var marker_{$leaflet_marker_count};
            jQuery(function () {";

            if (!empty($atts)) extract($atts);

            $draggable = empty($draggable) ? 'false' : $draggable;
            $visible = ($visible == 'true');

            if (!empty($address)) {
                $location = $this::google_geocode($address);
                $lat = $location->{'lat'};
                $lng = $location->{'lng'};
            }

            if (empty($lat) && empty($lng)) {
            	/* add to previous map's center */
            	$content .= "
	            marker_{$leaflet_marker_count} = L.marker(map_{$leaflet_map_count}.getCenter()";
            } else {
            	/* add to user contributed lat lng */
	            $lat = empty($lat) ? '44.67' : $lat;
	            $lng = empty($lng) ? '-63.61' : $lng;
	            $content .= "
	            marker_{$leaflet_marker_count} = L.marker([{$lat}, {$lng}]";
            }

            $content .= ", { draggable : {$draggable} });
            ";
            

            $content .= "
            marker_{$leaflet_marker_count}.addTo(map_{$leaflet_map_count});
            ";
            
            if (!empty($message)) {

                $content .= "marker_{$leaflet_marker_count}.bindPopup('$message')";

                if ($visible) {

                    $content .= ".openPopup()";                    

                }

                $content .= ";
                ";

            }

            $content .= "
            });
            </script>";

            return $content;
        }

    } /* end class */

    register_activation_hook( __FILE__, array('Leaflet_Map_Plugin', 'activate'));
    register_uninstall_hook( __FILE__, array('Leaflet_Map_Plugin', 'uninstall') );

    $leaflet_map_plugin = new Leaflet_Map_Plugin();
}
?>
