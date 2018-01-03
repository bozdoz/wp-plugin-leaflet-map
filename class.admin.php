<?php
/**
* 
* Used to generate an admin for Leaflet Map
* 
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Leaflet_Map_Admin {
    /**
     * @var Leaflet_Map_Admin
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

	private function __construct () {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array('Leaflet_Map', 'enqueue_and_register'));

        /* add settings to plugin page */
        add_filter('plugin_action_links_' . plugin_basename(LEAFLET_MAP__PLUGIN_FILE), array($this, 'plugin_action_links'));
	}

    /**
    * Admin init registers styles
    *
    */
    
    public function admin_init () {
        wp_register_style('leaflet_admin_stylesheet', plugins_url('style.css', LEAFLET_MAP__PLUGIN_FILE));
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

        add_menu_page("Leaflet Map", "Leaflet Map", 'manage_options', $main_link, array($this, "settings_page"), plugins_url('images/leaf.png', LEAFLET_MAP__PLUGIN_FILE));
        add_submenu_page("leaflet-map", "Default Values", "Default Values", 'manage_options', "leaflet-map", array($this, "settings_page"));
        add_submenu_page("leaflet-map", "Shortcode Helper", "Shortcode Helper", 'edit_posts', "leaflet-get-shortcode", array($this, "shortcode_page"));
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
        wp_enqueue_script('custom_plugin_js', plugins_url('scripts/get-shortcode.min.js', LEAFLET_MAP__PLUGIN_FILE), Array('leaflet_js'), false);

        include 'templates/shortcode-helper.php';
    }

    /**
    * Add settings link to the plugin on Installed Plugins page
    *
    */
    public function plugin_action_links ( $links ) {
        $links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=leaflet-map') ) .'">Settings</a>';
        return $links;
    }
}