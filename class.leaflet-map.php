<?php
/**
 * Leaflet Map Class File
 * 
 * @category Admin
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Leaflet Map Class
 */
class Leaflet_Map
{

    /**
     * Leaflet version
     * 
     * @var string major minor patch version
     */
    public static $leaflet_version = '1.9.3';

    /**
     * Files to include upon init
     * 
     * @var array $_shortcodes
     */
    private $_shortcodes = array(
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
        'leaflet-gpx' => array(
            'file' => 'class.gpx-shortcode.php',
            'class' => 'Leaflet_Gpx_Shortcode'
        ),
        'leaflet-line' => array(
            'file' => 'class.line-shortcode.php',
            'class' => 'Leaflet_Line_Shortcode'
        ),
        'leaflet-polygon' => array(
            'file' => 'class.polygon-shortcode.php',
            'class' => 'Leaflet_Polygon_Shortcode'
        ),
        'leaflet-circle' => array(
            'file' => 'class.circle-shortcode.php',
            'class' => 'Leaflet_Circle_Shortcode'
        ),
        'leaflet-map' => array(
            'file' => 'class.map-shortcode.php',
            'class' => 'Leaflet_Map_Shortcode'
        ),
        'leaflet-marker' => array(
            'file' => 'class.marker-shortcode.php',
            'class' => 'Leaflet_Marker_Shortcode'
        ),
        'leaflet-scale' => array(
            'file' => 'class.scale-shortcode.php',
            'class' => 'Leaflet_Scale_Shortcode'
        ),
        'leaflet-image-overlay' => array(
            'file' => 'class.image-overlay-shortcode.php',
            'class' => 'Leaflet_Image_Overlay_Shortcode'
        ),
        'leaflet-video-overlay' => array(
            'file' => 'class.video-overlay-shortcode.php',
            'class' => 'Leaflet_Video_Overlay_Shortcode'
        ),
    );

    /**
     * Singleton Instance of Leaflet Map
     * 
     * @var Leaflet_Map
     **/
    private static $instance = null;

    /**
     * Singleton init Function
     * 
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
        $this->init_hooks();
        $this->add_shortcodes();

        // loaded
        do_action('leaflet_map_loaded');
    }

    /**
     * Add actions and filters
     */
    private function init_hooks()
    {

        // Leaflet_Map_Plugin_Settings
        include_once LEAFLET_MAP__PLUGIN_DIR . 'class.plugin-settings.php';

        // Leaflet_Map_Admin
        include_once LEAFLET_MAP__PLUGIN_DIR . 'class.admin.php';
        
        // init admin
        Leaflet_Map_Admin::init();

        add_action( 'plugins_loaded', array('Leaflet_Map', 'load_text_domain' ));
        
        add_action( 'wp_enqueue_scripts', array('Leaflet_Map', 'enqueue_and_register') );

        $settings = self::settings();

        if ($settings->get('shortcode_in_excerpt')) {
            // allows maps in excerpts
            add_filter('the_excerpt', 'do_shortcode');
        }
    }

    /**
     * Includes and adds shortcodes
     */
    private function add_shortcodes()
    {
        // shortcodes
        $shortcode_dir = LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/';
        
        foreach ($this->_shortcodes as $shortcode => $details) {
            include_once $shortcode_dir . $details['file'];
            add_shortcode($shortcode, array($details['class'], 'shortcode'));
        }
    }

    /**
     * Triggered when user uninstalls/removes plugin
     */
    public static function uninstall()
    {
        // remove settings in db
        // it needs to be included again because __construct 
        // won't need to execute
        $settings = self::settings();
        $settings->reset();

        // remove geocoder locations in db
        include_once LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php';
        Leaflet_Geocoder::remove_caches();
    }

    /**
     * Loads Translations
     */
    public static function load_text_domain()
    {
        load_plugin_textdomain( 'leaflet-map', false, dirname( plugin_basename( LEAFLET_MAP__PLUGIN_FILE ) ) . '/languages/' );
    }

    /**
     * Enqueue and register styles and scripts (called in __construct)
     */
    public static function enqueue_and_register()
    {
        /* defaults from db */
        $settings = self::settings();

        $js_url = $settings->get('js_url');
        $css_url = $settings->get('css_url');

        wp_register_style('leaflet_stylesheet', $css_url, Array(), null, false);
        wp_register_script('leaflet_js', $js_url, Array(), null, true);

        // new required MapQuest javascript file
        $tiling_service = $settings->get('default_tiling_service');

        if ($tiling_service == 'mapquest') {
            $mapquest_js_url = 'https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key=%s';
            $mq_appkey = $settings->get('mapquest_appkey');
            $mapquest_js_url = sprintf($mapquest_js_url, $mq_appkey);

            wp_register_script('leaflet_mapquest_plugin', $mapquest_js_url, Array('leaflet_js'), '2.0', true);
        }
        
        // optional ajax geojson plugin
        wp_register_script('tmcw_togeojson', $settings->get('togeojson_url'), Array('jquery'), LEAFLET_MAP__PLUGIN_VERSION, false);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $minified = '';
        } else {
            $minified = '.min';
        }

        wp_register_script('leaflet_ajax_geojson_js', plugins_url(sprintf('scripts/leaflet-ajax-geojson%s.js', $minified), __FILE__), Array('tmcw_togeojson', 'leaflet_js'), LEAFLET_MAP__PLUGIN_VERSION, false);

        wp_register_script('leaflet_svg_icon_js', plugins_url(sprintf('scripts/leaflet-svg-icon%s.js', $minified), __FILE__), Array('leaflet_js'), LEAFLET_MAP__PLUGIN_VERSION, false);
        
        /* run a construct function in the document head for subsequent functions to use (it is lightweight) */
        wp_register_script('wp_leaflet_map', plugins_url(sprintf('scripts/construct-leaflet-map%s.js', $minified), __FILE__), Array('leaflet_js'), LEAFLET_MAP__PLUGIN_VERSION, false);
    }

    /**
     * Filter for removing nulls from array
     *
     * @param array $arr
     * 
     * @return array with nulls removed
     */
    public function filter_null($arr)
    {
        if (!function_exists('remove_null')) {
            function remove_null ($var) {
                return $var !== null;
            }
        }

        return array_filter($arr, 'remove_null');
    }

    /**
     * Filter for removing empty strings from array
     *
     * @param array $arr
     * 
     * @return array with empty strings removed
     */
    public function filter_empty_string($arr)
    {
        if (!function_exists('remove_empty_string')) {
            function remove_empty_string ($var) {
                return $var !== "";
            }
        }

        return array_filter($arr, 'remove_empty_string');
    }

    /**
     * Sanitize any given validations, but concatenate with the remaining keys from $arr
     */
    public function sanitize_inclusive($arr, $validations) {
        return array_merge(
            $arr,
            $this->sanitize_exclusive($arr, $validations)
        );
    }

    /**
     * Sanitize and return ONLY given validations
     */
    public function sanitize_exclusive($arr, $validations) {
        // remove nulls
        $arr = $this->filter_null($arr);

        // sanitize output
        $args = array_intersect_key($validations, $arr);
        return filter_var_array($arr, $args);
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
    public function json_sanitize($arr, $args)
    {
        $arr = $this->sanitize_exclusive($arr, $args);

        $output = json_encode($arr);

        // always return object; not array
        if ($output === '[]') {
            $output = '{}';
        }

        return $output;
    }

    /**
     * Get Style JSON for map shapes/geojson (svg or canvas)
     *
     * Takes atts for creating shapes on the map
     *
     * @param array $atts    user-input array
     * 
     * @return array corrected for JavaScript
     */
    public function get_style_json($atts)
    {
        if ($atts) {
            extract($atts, EXTR_SKIP);
        }

        // from http://leafletjs.com/reference-1.0.3.html#path
        $style = array(
            'stroke' => isset($stroke) ? $stroke : null,
            'color' => isset($color) ? $color : null,
            'weight' => isset($weight) ? $weight : null,
            'opacity' => isset($opacity) ? $opacity : null,
            'lineCap' => isset($linecap) ? $linecap : null,
            'lineJoin' => isset($linejoin) ? $linejoin : null,
            'dashArray' => isset($dasharray) ? $dasharray : null,
            'dashOffset' => isset($dashoffset) ? $dashoffset : null,
            'fill' => isset($fill) ? $fill : null,
            'fillColor' => isset($fillcolor) ? $fillcolor : null,
            'fillOpacity' => isset($fillopacity) ? $fillopacity : null,
            'fillRule' => isset($fillrule) ? $fillrule : null,
            'className' => isset($classname) ? $classname : null,
            'radius' => isset($radius) ? $radius : null
            );

        $args = array(
            'stroke' => FILTER_VALIDATE_BOOLEAN,
            'color' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'weight' => FILTER_VALIDATE_FLOAT,
            'opacity' => FILTER_VALIDATE_FLOAT,
            'lineCap' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'lineJoin' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'dashArray' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'dashOffset' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'fill' => FILTER_VALIDATE_BOOLEAN,
            'fillColor' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'fillOpacity' => FILTER_VALIDATE_FLOAT,
            'fillRule' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'className' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'radius' => FILTER_VALIDATE_FLOAT
            );

        return $this->json_sanitize($style, $args);
    }

    /**
     * Add Popups to Shapes
     *
     * used by leaflet-marker, leaflet-line and leaflet-circle
     *
     * @param array  $atts    user-input array
     * @param string $content text to display
     * @param string $shape   JavaScript variable for shape
     *
     * @return null
     */
    public function add_popup_to_shape($atts, $content, $shape)
    {
        if (!empty($atts)) {
            // don't overwrite existing variables
            extract($atts, EXTR_SKIP);
        }

        $message = empty($message) ? 
            (empty($content) ? '' : $content) : $message;

        if (empty($message)) {
            return;
        }
        
        // save variable for filter
        $original = $message;
        
        // execute shortcodes if present:
        // e.g. [leaflet-marker][some-shortcode][/leaflet-marker]
        $message = do_shortcode($message);

        // save variable for filter
        $shortcoded = $message;

        $message = str_replace(array("\r\n", "\n", "\r"), '<br>', $message);
        $message = addslashes($message);
        $message = htmlspecialchars($message);
        $message = "window.WPLeafletMapPlugin.unescape('{$message}')";

        // use with: add_filter('leaflet_map_popup_message', 'example_callback', 10, 3);
        // function takes default message, message after do_shortcode, and original/raw
        $message = apply_filters('leaflet_map_popup_message', $message, $shortcoded, $original);

        echo "{$shape}.bindPopup({$message})";
        
        $visible = empty($visible) 
            ? false 
            : filter_var($visible, FILTER_VALIDATE_BOOLEAN);
        
        if ($visible) {
            echo ".openPopup()";
        }
        echo ";";
    }

    /**
     * Get settings from Leaflet_Map_Plugin_Settings
     * @return Leaflet_Map_Plugin_Settings
     */
    public static function settings () {
        include_once LEAFLET_MAP__PLUGIN_DIR . 'class.plugin-settings.php';
        return Leaflet_Map_Plugin_Settings::init();
    }

    /**
     * Parses liquid tags from a string
     * 
     * @param string $str
     * 
     * @return array|null
     */
    public function liquid ($str) {
        if (!is_string($str)) {
            return null;
        }
        $templateRegex = "/\{ *(.*?) *\}/";
        preg_match_all($templateRegex, $str, $matches);
               
        if (!$matches[1]) {
            return null;
        }
        
        $str = $matches[1][0];

        $tags = explode(' | ', $str);

        $original = array_shift($tags);

        if (!$tags) {
            return null;
        }

        $output = array();

        foreach ($tags as $tag) {
            $tagParts = explode(': ', $tag);
            $tagName = array_shift($tagParts);
            $tagValue = implode(': ', $tagParts) || true;

            $output[$tagName] = $tagValue;
        }

        // preserve the original
        $output['original'] = $original;

        return $output;
    }

    /**
     * Renders a json-like string, removing quotes for values
     * 
     * allows JavaScript variables to be added directly 
     * 
     * @return string
     */
    public function rawDict ($arr) {
        $obj = '{';
        
        foreach ($arr as $key=>$val) {
            $obj .= "\"$key\": $val,";
        }

        $obj .= '}';

        return $obj;
    }

    /**
     * Filter all floats to remove commas, force decimals, and validate float
     * see: https://wordpress.org/support/topic/all-maps-are-gone/page/3/#post-14625548
     */
    public function filter_float ($flt) {
        // make sure the value actually is a float
        $out = filter_var($flt, FILTER_VALIDATE_FLOAT);
        
        // some locales seem to force commas
        $out = str_replace(',', '.', $out);
        
        return $out;
    }

    /**
     * Bounds are given as "50, -114; 52, -112"
     * Converted to 2d-array: [[50, -114], [52, -112]]
     */
    public function convert_bounds_str_to_arr ($bounds) {
        if (isset($bounds)) {
            try {
                // explode by semi-colons and commas
                $arr = preg_split("[;|,]", $bounds);

                return array(
                    array(
                        $this->filter_float($arr[0]), 
                        $this->filter_float($arr[1])
                    ),
                    array(
                        $this->filter_float($arr[2]), 
                        $this->filter_float($arr[3])
                    )
                );
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }
}
