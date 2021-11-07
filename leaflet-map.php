<?php

/**
 * Plugin Name: Leaflet Map
 * Plugin URI: https://wordpress.org/plugins/leaflet-map/
 * Description: A plugin for creating a Leaflet JS map with a shortcode. Boasts two free map tile services and three free geocoders.
 * Author: bozdoz
 * Author URI: https://bozdoz.com/
 * Text Domain: leaflet-map
 * Domain Path: /languages/
 * Version: 3.0.4
 * License: GPL2
 * Leaflet Map is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *  
 * Leaflet Map is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License
 * along with Leaflet Map. If not, see  https://github.com/bozdoz/wp-plugin-leaflet-map/blob/master/LICENSE.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit; 
}

define('LEAFLET_MAP__PLUGIN_VERSION', '3.0.4');
define('LEAFLET_MAP__PLUGIN_FILE', __FILE__);
define('LEAFLET_MAP__PLUGIN_DIR', plugin_dir_path(__FILE__));

// import main class
require_once LEAFLET_MAP__PLUGIN_DIR . 'class.leaflet-map.php';

// uninstall hook
register_uninstall_hook(__FILE__, array('Leaflet_Map', 'uninstall'));

add_action('init', array('Leaflet_Map', 'init'));