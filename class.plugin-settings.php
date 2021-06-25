<?php
/**
 * Class for getting and setting db/default values
 * 
 * @category Admin
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once LEAFLET_MAP__PLUGIN_DIR . 'class.plugin-option.php';

// TODO: add option to reset just a single field

/**
 * Used to get and set values
 * 
 * Features:
 * * Add prefixes to db options
 * * built-in admin settings page method
 */
class Leaflet_Map_Plugin_Settings
{
    /**
     * Prefix for options, for unique db entries
     * 
     * @var string $prefix
     */
    public $prefix = 'leaflet_';
    
    /**
     * Singleton instance
     * 
     * @var Leaflet_Map_Plugin_Settings
     **/
    private static $_instance = null;

    /**
     * Default values and admin form information
     * Needs to be created within __construct
     * in order to use a function such as __()
     * 
     * @var array $options
     */
    public $options = array();

    /**
     * Singleton
     * 
     * @static
     * 
     * @return Leaflet_Map_Plugin_Settings
     */
    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Instantiate the class
     */
    private function __construct() 
    {

        /* update leaflet version from main class */
        $leaflet_version = Leaflet_Map::$leaflet_version;

        $foreachmap = __('You can also change this for each map');

        /* 
        * initiate options using internationalization! 
        */
        $this->options = array(
            'default_lat' => array(
                'display_name'=>__('Default Latitude', 'leaflet-map'),
                'default'=>'44.67',
                'type' => 'number',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map lat="44.67"]</code>', 
                    __('Default latitude for maps.', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'default_lng' => array(
                'display_name'=>__('Default Longitude', 'leaflet-map'),
                'default'=>'-63.61',
                'type' => 'number',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map lng="-63.61"]</code>', 
                    __('Default longitude for maps.', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'default_zoom' => array(
                'display_name'=>__('Default Zoom', 'leaflet-map'),
                'default'=>'12',
                'type' => 'number',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map zoom="5"]</code>', 
                    __('Default zoom for maps.', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'default_height' => array(
                'display_name'=>__('Default Height', 'leaflet-map'),
                'default'=>'250',
                'type' => 'text',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map height="250"]</code>', 
                    __('Default height for maps. Values can include "px" but it is not necessary. Can also be "%". ', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'default_width' => array(
                'display_name'=>__('Default Width', 'leaflet-map'),
                'default'=>'100%',
                'type' => 'text',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map width="100%%"]</code>', 
                    __('Default width for maps. Values can include "px" but it is not necessary.  Can also be "%".', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'fit_markers' => array(
                'display_name'=>__('Fit Bounds', 'leaflet-map'),
                'default' => '0',
                'type' => 'checkbox',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map fitbounds]</code>', 
                    __('If enabled, all markers on each map will alter the view of the map; i.e. the map will fit to the bounds of all of the markers on the map.', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'show_zoom_controls' => array(
                'display_name'=>__('Show Zoom Controls', 'leaflet-map'),
                'default' => '0',
                'type' => 'checkbox',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map !zoomcontrol]</code>', 
                    __('The zoom buttons can be large and annoying.', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'scroll_wheel_zoom' => array(
                'display_name'=>__('Scroll Wheel Zoom', 'leaflet-map'),
                'default' => '0',
                'type' => 'checkbox',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map !scrollwheel]</code>', 
                    __('Disable zoom with mouse scroll wheel.  Sometimes someone wants to scroll down the page, and not zoom the map.', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'double_click_zoom' => array(
                'display_name'=>__('Double Click Zoom', 'leaflet-map'),
                'default' => '0',
                'type' => 'checkbox',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map !doubleClickZoom]</code>', 
                    __('If enabled, your maps will zoom with a double click.  By default it is disabled: If we\'re going to remove zoom controls and have scroll wheel zoom off by default, we might as well stick to our guns and not zoom the map.', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'default_min_zoom' => array(
                'display_name'=>__('Default Min Zoom', 'leaflet-map'),
                'default' => '0',
                'type' => 'number',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map min_zoom="1"]</code>', 
                    __('Restrict the viewer from zooming in past the minimum zoom.  Can set per map in shortcode or adjust for all maps here.', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'default_max_zoom' => array(
                'display_name'=>__('Default Max Zoom', 'leaflet-map'),
                'default' => '20',
                'type' => 'number',
                'helptext' => sprintf(
                    '%1$s %2%s <br /> <code>%3$s</code>', 
                    __('Restrict the viewer from zooming out past the maximum zoom.  Can set per map in shortcode or adjust for all maps here', 'leaflet-map'),
                    $foreachmap,
                    '[leaflet-map max_zoom="10"]'
                )
            ),
            'default_tiling_service' => array(
                'display_name'=>__('Default Tiling Service', 'leaflet-map'),
                'default' => 'other',
                'type' => 'select',
                'options' => array(
                    'other' => __('I will provide my own map tile URL', 'leaflet-map'),
                    'mapquest' => __('MapQuest (I have an API key)', 'leaflet-map'),
                ),
                'helptext' => __('Choose a tiling service or provide your own.', 'leaflet-map')
            ),
            'mapquest_appkey' => array(
                'display_name'=>__('MapQuest API Key (optional)', 'leaflet-map'),
                'default' => __('Supply an API key if you choose MapQuest', 'leaflet-map'),
                'type' => 'text',
                'noreset' => true,
                'helptext' => sprintf(
                    '%1$s <a href="https://developer.mapquest.com/plan_purchase/steps/business_edition/business_edition_free/register" target="_blank"> %2$s </a>, %3$s <a href="https://developer.mapquest.com/user/me/apps" target="_blank"> %4$s </a> %5$s',
                    __('If you choose MapQuest, you must provide an API key.', 'leaflet-map'),
                    __('Sign up', 'leaflet-map'),
                    __('then', 'leaflet-map'),
                    __('Create a new app', 'leaflet-map'),
                    __('then supply the "Consumer Key" here.', 'leaflet-map')
                )
            ),
            'map_tile_url' => array(
                'display_name'=>__('Map Tile URL', 'leaflet-map'),
                'default'=>'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                'type' => 'text',
                'helptext' => sprintf(
                    '%1$s: <a href="http://wiki.openstreetmap.org/wiki/Tile_servers" target="_blank"> %2$s </a>. %3$s: <a href="http://devblog.mapquest.com/2016/06/15/modernization-of-mapquest-results-in-changes-to-open-tile-access/" target="_blank"> %4$s </a>. %5$s <br/> <code>[leaflet-map tileurl=http://{s}.tile.stamen.com/watercolor/{z}/{x}/{y}.jpg subdomains=abcd]</code>',
                    __('See more tile servers', 'leaflet-map'),
                    __('here', 'leaflet-map'),
                    __('Please note: free tiles from MapQuest have been discontinued without use of an API key', 'leaflet-map'),
                    __('blog post', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'map_tile_url_subdomains' => array(
                'display_name'=>__('Map Tile URL Subdomains', 'leaflet-map'),
                'default'=>'abc',
                'type' => 'text',
                'helptext' => sprintf(
                    '%1$s %2$s <br/> <code>[leaflet-map subdomains="1234"]</code>',
                    __('Some maps get tiles from multiple servers with subdomains such as a,b,c,d or 1,2,3,4', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'detect_retina' => array(
                'display_name' => __('Detect Retina', 'leaflet-map'),
                'default' => '0',
                'type' => 'checkbox',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map detect-retina]</code>',
                    __('Fetch tiles at different zoom levels to appear smoother on retina displays.', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'tilesize' => array(
                'display_name' => __('Tile Size', 'leaflet-map'),
                'default' => null,
                'type' => 'text',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map tilesize=512]</code>',
                    __('Width and height of tiles (in pixels) in the grid. Default is 256', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'mapid' => array(
                'display_name' => __('Tile Id', 'leaflet-map'),
                'default' => null,
                'type' => 'text',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map mapid="mapbox/streets-v11"]</code>',
                    __('An id that is passed to L.tileLayer; useful for Mapbox', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'accesstoken' => array(
                'display_name' => __('Access Token', 'leaflet-map'),
                'default' => null,
                'type' => 'text',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map accesstoken="your.mapbox.access.token"]</code>',
                    __('An access token that is passed to L.tileLayer; useful for Mapbox tiles', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'zoomoffset' => array(
                'display_name' => __('Zoom Offset', 'leaflet-map'),
                'default' => null,
                'type' => 'number',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map zoomoffset="-1"]</code>',
                    __('The zoom number used in tile URLs will be offset with this value', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'tile_no_wrap' => array(
                'display_name' => __('No Wrap (tiles)', 'leaflet-map'),
                'default' => '0',
                'type' => 'checkbox',
                'helptext' => sprintf(
                    '%1$s %2$s <br /> <code>[leaflet-map nowrap]</code>',
                    __('Boolean for whether the layer is wrapped around the antimeridian', 'leaflet-map'),
                    $foreachmap
                )
            ),
            'js_url' => array(
                'display_name'=>__('JavaScript URL', 'leaflet-map'),
                'default' => sprintf('https://unpkg.com/leaflet@%s/dist/leaflet.js', $leaflet_version),
                'type' => 'text',
                'helptext' => __('If you host your own Leaflet files, then paste the URL here.', 'leaflet-map')
            ),
            'css_url' => array(
                'display_name'=>__('CSS URL', 'leaflet-map'),
                'default' => sprintf('https://unpkg.com/leaflet@%s/dist/leaflet.css', $leaflet_version),
                'type' => 'text',
                'helptext' => __('Same as above.', 'leaflet-map')
            ),
            'default_attribution' => array(
                'display_name' => __('Default Attribution', 'leaflet-map'),
                'default' => sprintf(
                    '<a href="http://leafletjs.com" title="%1$s">Leaflet</a>; © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> %2$s',
                    __("A JS library for interactive maps", 'leaflet-map'),
                    __("contributors", 'leaflet-map')
                ),
                'type' => 'textarea',
                'helptext' => __('Attribution to a custom tile url.  Use semi-colons (;) to separate multiple.', 'leaflet-map')
            ),
            'show_scale' => array(
                'display_name' => __('Show Scale', 'leaflet-map'),
                'default' => '0',
                'type' => 'checkbox',
                'helptext' => __(
                    'Add a scale to each map. Can also be added via shortcode <br /> <code>[leaflet-scale]</code>', 
                    'leaflet-map'
                )
            ),
            'geocoder' => array(
                'display_name'=>__('Geocoder', 'leaflet-map'),
                'default' => 'osm',
                'type' => 'select',
                'options' => array(
                    'osm' => __('OpenStreetMap Nominatim', 'leaflet-map'),
                    'google' => __('Google Maps', 'leaflet-map'),
                    'dawa' => __('Denmark Addresses', 'leaflet-map')
                ),
                'helptext' => __('Select the Geocoding provider to use to retrieve addresses defined in shortcode.', 'leaflet-map')
            ),
            'google_appkey' => array(
                'display_name'=>__('Google API Key (optional)', 'leaflet-map'),
                'default' => __('Supply a Google API Key', 'leaflet-map'),
                'type' => 'text',
                'noreset' => true,
                'helptext' => sprintf(
                    '%1$s: <a href="https://cloud.google.com/maps-platform/?apis=places" target="_blank">%2$s</a>.  %3$s %4$s',
                    __('The Google Geocoder requires an API key with the Places product enabled', 'leaflet-map'),
                    __('here', 'leaflet-map'),
                    __('You must create a project and set up a billing account, then you will be given an API key.', 'leaflet-map'),
                    __('You are unlikely to ever be charged for geocoding.', 'leaflet-map')
                ),
            ),
            'togeojson_url' => array(
                'display_name'=>__('KML/GPX JavaScript Converter', 'leaflet-map'),
                'default' => 'https://unpkg.com/@mapbox/togeojson@0.16.0/togeojson.js',
                'type' => 'text',
                'helptext' => __('ToGeoJSON converts KML and GPX files to GeoJSON; if you plan to use [leaflet-kml] or [leaflet-gpx] then this library is loaded.  You can change the default if you need.', 'leaflet-map')
            ),
            'shortcode_in_excerpt' => array(
                'display_name' => __('Show maps in excerpts', 'leaflet-map'),
                'default' => '0',
                'type' => 'checkbox',
            ),
        );

        foreach ($this->options as $name => $details) {
            $this->options[ $name ] = new Leaflet_Map_Plugin_Option($details);
        }
    }

    /**
     * Wrapper for WordPress get_options (adds prefix to default options)
     *
     * @param string $key                
     * 
     * @return varies
     */
    public function get($key) 
    {
        $default = $this->options[ $key ]->default;
        $key = $this->prefix . $key;
        return get_option($key, $default);
    }

    /**
     * Wrapper for WordPress update_option (adds prefix to default options)
     *
     * @param string $key   Unique db key
     * @param varies $value Value to insert
     * 
     * @return Leaflet_Map_Plugin_Settings
     */
    public function set ($key, $value) {
        $key = $this->prefix . $key;
        update_option($key, $value);
        return $this;
    }

    /**
     * Wrapper for WordPress delete_option (adds prefix to default options)
     *
     * @param string $key Unique db key
     * 
     * @return boolean
     */
    public function delete($key) 
    {
        $key = $this->prefix . $key;
        return delete_option($key);
    }

    /**
     * Delete all options
     *
     * @return Leaflet_Map_Plugin_Settings
     */
    public function reset()
    {
        foreach ($this->options as $name => $option) {
            if (
                !property_exists($option, 'noreset') ||
                $option->noreset != true
            ) {
                $this->delete($name);
            }
        }
        return $this;
    }
}
