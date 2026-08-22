<?php
/**
 * Used to generate an admin for Leaflet Map
 *
 * PHP Version 5.5
 * 
 * @category Admin
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Leaflet_Map_Admin class
 */
class Leaflet_Map_Admin
{
    /**
     * Singleton Instance
     * 
     * @var Leaflet_Map_Admin $_instance
     */
    private static $_instance = null;

    /**
     * Singleton
     * 
     * @static
     * 
     * @return Leaflet_Map_Admin
     */
    public static function init()
    {
        if (!self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Instantiate the class
     */
    private function __construct()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array('Leaflet_Map', 'enqueue_and_register'));
        add_action('wp_ajax_leaflet_map_reset_setting', array($this, 'ajax_reset_setting'));

        /* add settings to plugin page */
        add_filter('plugin_action_links_' . plugin_basename(LEAFLET_MAP__PLUGIN_FILE), array($this, 'plugin_action_links'));
    }

    /**
     * Admin init registers styles
     */
    public function admin_init() 
    {
        wp_register_style('leaflet_admin_stylesheet', plugins_url('style.css', LEAFLET_MAP__PLUGIN_FILE));
    }

    /**
     * Add admin menu page when user in admin area
     */
    public function admin_menu()
    {
        $leaf = 'data:image/svg+xml;base64,PHN2ZyBhcmlhLWhpZGRlbj0idHJ1ZSIgZm9jdXNhYmxlPSJmYWxzZSIgZGF0YS1wcmVmaXg9ImZhcyIgZGF0YS1pY29uPSJsZWFmIiBjbGFzcz0ic3ZnLWlubGluZS0tZmEgZmEtbGVhZiBmYS13LTE4IiByb2xlPSJpbWciIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDU3NiA1MTIiPjxwYXRoIGZpbGw9ImN1cnJlbnRDb2xvciIgZD0iTTU0Ni4yIDkuN2MtNS42LTEyLjUtMjEuNi0xMy0yOC4zLTEuMkM0ODYuOSA2Mi40IDQzMS40IDk2IDM2OCA5NmgtODBDMTgyIDk2IDk2IDE4MiA5NiAyODhjMCA3IC44IDEzLjcgMS41IDIwLjVDMTYxLjMgMjYyLjggMjUzLjQgMjI0IDM4NCAyMjRjOC44IDAgMTYgNy4yIDE2IDE2cy03LjIgMTYtMTYgMTZDMTMyLjYgMjU2IDI2IDQxMC4xIDIuNCA0NjhjLTYuNiAxNi4zIDEuMiAzNC45IDE3LjUgNDEuNiAxNi40IDYuOCAzNS0xLjEgNDEuOC0xNy4zIDEuNS0zLjYgMjAuOS00Ny45IDcxLjktOTAuNiAzMi40IDQzLjkgOTQgODUuOCAxNzQuOSA3Ny4yQzQ2NS41IDQ2Ny41IDU3NiAzMjYuNyA1NzYgMTU0LjNjMC01MC4yLTEwLjgtMTAyLjItMjkuOC0xNDQuNnoiLz48L3N2Zz4=';

        $admin = "manage_options";
        $author = "edit_posts";

        if (current_user_can($admin)) {
            $main_link = 'leaflet-map';
            $main_page = array($this, "settings_page");
        } else {
            $main_link = 'leaflet-shortcode-helper';
            $main_page = array($this, "shortcode_page");
        }

        add_menu_page("Leaflet Map", "Leaflet Map", $author, $main_link, $main_page, $leaf);
        add_submenu_page("leaflet-map", __('Settings', 'leaflet-map'), __('Settings', 'leaflet-map'), $admin, "leaflet-map", array($this, "settings_page"));
        add_submenu_page("leaflet-map", __('Shortcode helper', 'leaflet-map'), __('Shortcode helper', 'leaflet-map'), $author, "leaflet-shortcode-helper", array($this, "shortcode_page"));
    }

    /**
     * AJAX handler for resetting a single setting option
     */
    public function ajax_reset_setting()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'leaflet-map')), 403);
        }

        check_ajax_referer('leaflet-map-reset-setting', 'security');

        if (empty($_POST['setting'])) {
            wp_send_json_error(array('message' => __('Missing setting name.', 'leaflet-map')), 400);
        }

        $name = sanitize_text_field($_POST['setting']);
        $settings = Leaflet_Map_Plugin_Settings::init();

        if (!isset($settings->options[$name])) {
            wp_send_json_error(array('message' => __('Unknown setting.', 'leaflet-map')), 404);
        }

        $option = $settings->options[$name];
        if (property_exists($option, 'noreset') && $option->noreset == true) {
            wp_send_json_error(array('message' => __('This option cannot be reset individually.', 'leaflet-map')), 400);
        }

        $settings->delete($name);
        wp_send_json_success(array('default' => $settings->get($name)));
    }

    /**
     * Main settings page includes form inputs
     */
    public function settings_page()
    {
        wp_enqueue_style('leaflet_admin_stylesheet');

        $settings = Leaflet_Map_Plugin_Settings::init();
        $plugin_data = get_plugin_data(LEAFLET_MAP__PLUGIN_FILE);
        include 'templates/settings.php';
    }

    /**
     * Shortcode page shows example shortcodes and an interactive generator
     */
    public function shortcode_page()
    {
        wp_enqueue_style('leaflet_admin_stylesheet');
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $minified = '';
        } else {
            $minified = '.min';
        }

        $dependencies = Array('leaflet_js');
        if (Leaflet_Map::uses_esm() && function_exists('wp_enqueue_script_module')) {
            wp_enqueue_script_module('leaflet_esm_proxy');
            $dependencies = Array();
        }

        wp_enqueue_script('custom_plugin_js', plugins_url(sprintf('scripts/shortcode-helper%s.js', $minified), LEAFLET_MAP__PLUGIN_FILE), $dependencies, false);

        include 'templates/shortcode-helper.php';
    }

    /**
     * Add settings link to the plugin on Installed Plugins page
     * 
     * @return array
     */
    public function plugin_action_links($links)
    {
        $links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=leaflet-map') ) .'">Settings</a>';
        return $links;
    }
}
