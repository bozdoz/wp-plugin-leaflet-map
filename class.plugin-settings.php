<?php
/**
* 
* Used to get and set values
* 
* Features:
* * Add prefixes to db options
* * built-in admin settings page method
* 
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

include_once(LEAFLET_MAP__PLUGIN_DIR . 'class.plugin-option.php');

class Leaflet_Map_Plugin_Settings {
	/**
    * Prefix for options, for unique db entries
    * @var string $prefix
    */
    public $prefix = 'leaflet_';
	
    /**
     * @var Leaflet_Map_Plugin_Settings
     **/
    private static $instance = null;

	/**
	* Default values and admin form information
	* @var array $options
	*/
	public $options = array(
        'default_lat' => array(
            'default'=>'44.67',
            'type' => 'text',
            'helptext' => 'Default latitude for maps or adjust for each map like so: <br /> <code>[leaflet-map lng="44.67"]</code>'
        ),
        'default_lng' => array(
            'default'=>'-63.61',
            'type' => 'text',
            'helptext' => 'Default longitude for maps or adjust for each map like so: <br /> <code>[leaflet-map lng="-63.61"]</code>'
        ),
        'default_zoom' => array(
            'default'=>'12',
            'type' => 'text',
            'helptext' => 'Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map zoom="5"]</code>'
        ),
        'default_height' => array(
            'default'=>'250',
            'type' => 'text',
            'helptext' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>'
        ),
        'default_width' => array(
            'default'=>'100%',
            'type' => 'text',
            'helptext' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map width="100%"]</code>'
        ),
        'fit_markers' => array(
            'default' => '0',
            'type' => 'checkbox',
            'helptext' => 'If enabled, all markers on each map will alter the view of the map; i.e. the map will fit to the bounds of all of the markers on the map.  You can also change this per map in the shortcode: e.g. <br /> <code>[leaflet-map fit_markers="1"]</code>'
        ),
        'show_zoom_controls' => array(
            'default' => '0',
            'type' => 'checkbox',
            'helptext' => 'The zoom buttons can be large and annoying.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map zoomcontrol="0"]</code>'
        ),
        'scroll_wheel_zoom' => array(
            'default' => '0',
            'type' => 'checkbox',
            'helptext' => 'Disable zoom with mouse scroll wheel.  Sometimes someone wants to scroll down the page, and not zoom the map.  Enable or disable per map in shortcode: <br/> <code>[leaflet-map scrollwheel="0"]</code>'
        ),
        'double_click_zoom' => array(
            'default' => '0',
            'type' => 'checkbox',
            'helptext' => 'If enabled, your maps will zoom with a double click.  By default it is disabled: If we\'re going to remove zoom controls and have scroll wheel zoom off by default, we might as well stick to our guns and not zoom the map.  Enable or disable per map in shortcode: <br/> <code>[leaflet-map doubleClickZoom=false]</code>'
        ),
        'default_min_zoom' => array(
            'default' => '0',
            'type' => 'text',
            'helptext' => 'Restrict the viewer from zooming in past the minimum zoom.  Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map min_zoom="1"]</code>'
        ),
        'default_max_zoom' => array(
            'default' => '20',
            'type' => 'text',
            'helptext' => 'Restrict the viewer from zooming out past the maximum zoom.  Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map max_zoom="10"]</code>'
        ),
        'default_tiling_service' => array(
            'default' => 'other',
            'type' => 'select',
            'options' => array(
                'other' => 'I will provide my own map tile URL',
                'mapquest' => 'MapQuest (I have an app key)',
            ),
            'helptext' => 'Choose a tiling service or provide your own.'
        ),
        'mapquest_appkey' => array(
            'default' => 'supply-an-app-key-if-you-choose-mapquest',
            'type' => 'text',
            'noreset' => true,
            'helptext' => 'If you choose MapQuest, you must provide an app key. <a href="https://developer.mapquest.com/plan_purchase/steps/business_edition/business_edition_free/register" target="_blank">Sign up</a>, then <a href="https://developer.mapquest.com/user/me/apps" target="_blank">Create a new app</a> then supply the "Consumer Key" here.'
        ),
        'map_tile_url' => array(
            'default'=>'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'type' => 'text',
            'helptext' => 'See more tile servers here: <a href="http://wiki.openstreetmap.org/wiki/Tile_servers" target="_blank">here</a>.  Please note(!): free tiles from MapQuest have been discontinued without use of an app key (free accounts available) (see <a href="http://devblog.mapquest.com/2016/06/15/modernization-of-mapquest-results-in-changes-to-open-tile-access/" target="_blank">blog post</a>). Can be set per map with the shortcode <br/> <code>[leaflet-map tileurl=http://{s}.tile.stamen.com/watercolor/{z}/{x}/{y}.jpg subdomains=abcd]</code>'
        ),
        'map_tile_url_subdomains' => array(
            'default'=>'abc',
            'type' => 'text',
            'helptext' => 'Some maps get tiles from multiple servers with subdomains such as a,b,c,d or 1,2,3,4; can be set per map with the shortcode <br/> <code>[leaflet-map subdomains="1234"]</code>',
        ),
        'js_url' => array(
            'default'=>'https://unpkg.com/leaflet@%s/dist/leaflet.js',
            'type' => 'text',
            'helptext' => 'If you host your own Leaflet files, specify the URL here.'
        ),
        'css_url' => array(
            'default'=>'https://unpkg.com/leaflet@%s/dist/leaflet.css',
            'type' => 'text',
            'helptext' => 'Save as above.'
        ),
        'default_attribution' => array(
            'default' => "<a href=\"http://leafletjs.com\" title=\"A JS library for interactive maps\">Leaflet</a>; \r\n© <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors",
            'type' => 'textarea',
            'helptext' => 'Attribution to a custom tile url.  Use semi-colons (;) to separate multiple.'
        ),
        'geocoder' => array(
            'default' => 'google',
            'type' => 'select',
            'options' => array(
                'google' => 'Google Maps',
                'osm' => 'OpenStreetMap Nominatim',
                'dawa' => 'Danmarks Adressers'
            ),
            'helptext' => 'Select the Geocoding provider to use to retrieve addresses defined in shortcode.'
        )
    );

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

	private function __construct () {

        /* update leaflet version from main class */
        $leaflet_version = Leaflet_Map::$leaflet_version;

        $this->options['js_url']['default'] = sprintf($this->options['js_url']['default'], $leaflet_version);
        $this->options['css_url']['default'] = sprintf($this->options['css_url']['default'], $leaflet_version);

		foreach ($this->options as $name => $details) {
			$this->options[ $name ] = new Leaflet_Map_Plugin_Option( $details );
		}
	}

	/*
	* wrapper for WordPress get_options (adds prefix to default options)
	*
	* @param string $key                
	* @param varies $default   default value if not found in db
	* @return varies
	*/

	public function get ($key) {
		$default = $this->options[ $key ]->default;
		$key = $this->prefix . $key;
		return get_option($key, $default);
	}

	/*
	* wrapper for WordPress update_option (adds prefix to default options)
	*
	* @param string $key
	* @param varies $value
	* @param varies $default   default value if not found in db
	* @return varies
	*/

	public function set ($key, $value) {
		$key = $this->prefix . $key;
		update_option($key, $value);
		return $this;
	}

	/*
	* wrapper for WordPress delete_option (adds prefix to default options)
	*
	* @param string $key
	* @param varies $default   default value if not found in db
	* @return varies
	*/

	public function delete ($key) {
		$key = $this->prefix . $key;
		return delete_option($key);
	}

	/*
	* wrapper for WordPress delete_option (adds prefix to default options)
	*
	* @param string $key
	* @param varies $default   default value if not found in db
	* @return varies
	*/

	public function reset () {
		foreach ($this->options as $name => $option) {
			$this->delete( $name );
		}
		return $this;
	}
}