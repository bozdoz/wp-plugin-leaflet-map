<?php
/*
Leaflet Map Class
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Leaflet_Map {

    /**
    * Leaflet version
    * @var string major minor patch version
    */
    public $leaflet_version = '1.2.0';

    /**
    * Number of maps on page; used for unique map ids
    * @var int $map_count
    */
    public $map_count = 0;

    /**
    * Files to include upon init
    * @var array $shortcodes
    */
    private $shortcodes = array(
        'leaflet-geojson' => array(
            'file' => 'class.geojson-shortcode.php',
            'class' => 'Leaflet_Geojson_Shortcode'
        ),
        'leaflet-image' => array(
            'file' => 'class.image-shortcode.php',
            'class' => 'Leaflet_Image_Shortcode'
        ),
        'leaflet-kml' => array(
            'file' => 'class.kml-shortcode.php',
            'class' => 'Leaflet_Kml_Shortcode'
        ),
        'leaflet-line' => array(
            'file' => 'class.line-shortcode.php',
            'class' => 'Leaflet_Line_Shortcode'
        ),
        'leaflet-map' => array(
            'file' => 'class.map-shortcode.php',
            'class' => 'Leaflet_Map_Shortcode'
        ),
        'leaflet-marker' => array(
            'file' => 'class.marker-shortcode.php',
            'class' => 'Leaflet_Marker_Shortcode'
        )
    );

    /**
     * @var Leaflet_Map
     **/
    private static $instance = null;

    /**
     * Singleton
     * @static
     */
    public static function init() {
        if ( !self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Leaflet_Map Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
        $this->add_shortcodes();

        // loaded
        do_action('leaflet-map-loaded');
    }

    /**
    *
    * include classes
    *
    */
    private function includes() {
        // Leaflet_Map_Plugin_Settings
        include_once(LEAFLET_MAP__PLUGIN_DIR . 'class.plugin-settings.php');
    }

    /**
    *
    * add actions and filters
    *
    */
    private function init_hooks() {
        
        // get these details above
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_and_register'));

        /* add settings to plugin page */
        add_filter('plugin_action_links_' . plugin_basename(LEAFLET_MAP__PLUGIN_FILE), array($this, 'plugin_action_links'));
        
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_and_register') );

        /* 
        allows maps on excerpts 
        todo? should be optional somehow (admin setting?)
        */
        add_filter('the_excerpt', 'do_shortcode');
    }

    /**
    *
    * includes and adds shortcodes
    *
    */
    private function add_shortcodes () {
        // shortcodes
        $shortcode_dir = LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/';
        
        foreach ($this->shortcodes as $shortcode => $details) {
            include_once($shortcode_dir . $details['file']);
            add_shortcode($shortcode, array($details['class'], 'shortcode'));
        }
    }

    /**
    * Triggered when user uninstalls/removes plugin
    */

    public static function uninstall () {            
        // remove settings in db
        // think it needs to be included again (because __construct 
        // won't need to execute)
        include_once(LEAFLET_MAP__PLUGIN_DIR . 'class.plugin-settings.php');
        $settings = Leaflet_Map_Plugin_Settings::init();
        $settings->reset();

        // remove geocoder locations in db
        include_once(LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php');
        Leaflet_Geocoder::remove_caches();
    }

    /**
    * Enqueue and register styles and scripts (called in __construct)
    *
    */

    public function enqueue_and_register () {
        /* defaults from db */
        $settings = Leaflet_Map_Plugin_Settings::init();

        $js_url = $settings->get('js_url');
        $css_url = $settings->get('css_url');

        wp_register_style('leaflet_stylesheet', $css_url, Array(), $this->leaflet_version, false);
        wp_register_script('leaflet_js', $js_url, Array(), $this->leaflet_version, true);

        // new required MapQuest javascript file
        $tiling_service = $settings->get('default_tiling_service');

        if ($tiling_service == 'mapquest') {
            $mapquest_js_url = 'https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key=%s';
            $mq_appkey = $settings->get('mapquest_appkey');
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

    /**
    * Admin init registers styles
    *
    * todo: candidate for separate class
    *
    */
    
    public function admin_init () {
        wp_register_style('leaflet_admin_stylesheet', plugins_url('style.css', __FILE__));
    }

    /**
    * Main settings page includes form inputs
    *
    */

    public function settings_page () {
        wp_enqueue_style( 'leaflet_admin_stylesheet' );

        $settings = Leaflet_Map_Plugin_Settings::init();
        $plugin_data = get_plugin_data(LEAFLET_MAP__PLUGIN_FILE);
        include 'templates/settings.php';
    }

    /**
    * Shortcode page shows example shortcodes and an interactive generator
    *
    */
    public function shortcode_page () {
        wp_enqueue_style( 'leaflet_admin_stylesheet' );
        wp_enqueue_script('custom_plugin_js', plugins_url('scripts/get-shortcode.js', __FILE__), Array('leaflet_js'), false);

        include 'templates/shortcode-helper.php';
    }

    /**
    * Add admin menu page when user in admin area
    *
    */

    public function admin_menu () {
        if (current_user_can('manage_options')) {
            $main_link = 'leaflet-map';
        } else {
            $main_link = 'leaflet-get-shortcode';
        }

        add_menu_page("Leaflet Map", "Leaflet Map", 'manage_options', $main_link, array($this, "settings_page"), plugins_url('images/leaf.png', __FILE__));
        add_submenu_page("leaflet-map", "Default Values", "Default Values", 'manage_options', "leaflet-map", array($this, "settings_page"));
        add_submenu_page("leaflet-map", "Shortcode Helper", "Shortcode Helper", 'edit_posts', "leaflet-get-shortcode", array($this, "shortcode_page"));
    }

    /**
    * Add settings link to the plugin on Installed Plugins page
    *
    */
    public function plugin_action_links ( $links ) {
        $links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=leaflet-map') ) .'">Settings</a>';
        return $links;
    }

    /**
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

    /**
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

    /**
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

        return $this->json_sanitize($style, $args);
    }

    /**
    * Add Popups to Shapes
    *
    * used by leaflet-marker and leaflet-line
    *
    * @param array $atts        user-input array
    * @param string $content    text to display
    * @param string $shape      JavaScript variable for shape
    * @return null
    */

    public function add_popup_to_shape ($atts, $content, $shape) {
        if (!empty($atts)) extract($atts);

        $message = empty($message) ? (empty($content) ? '' : $content) : $message;
        $message = str_replace(array("\r\n", "\n", "\r"), '<br>', $message);
        $message = htmlspecialchars($message);
        $visible = empty($visible) ? false : ($visible == 'true');

        if (!empty($message)) {
            /* 
            $message = str_replace("\n", '', $message);
            */

            echo "{$shape}.bindPopup(WPLeafletMapPlugin.unescape('{$message}'))";

            if ($visible) {
                echo ".openPopup()";
            }

            echo ";";
        }
    }
}