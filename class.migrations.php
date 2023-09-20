<?php
/**
 * Leaflet Map Migrations
 * @since 3.4.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define('LEAFLET_MAP__MIGRATION_DIR', LEAFLET_MAP__PLUGIN_DIR . 'migrations/');

class Leaflet_Map_Migrations {
    /**
     * only call once
     **/
    private static $called = false;

    /**
     * We assume if this is called, our db version diverges from plugin version
     * migrations here are run in ascending order, always compared via '<' against db version
     */
    public static function run_once($db_version) {
        if ( self::$called ) {
            return;
        }

        self::$called = true;

        if (version_compare($db_version, '3.4.0', '<')) {
            include_once LEAFLET_MAP__MIGRATION_DIR . '001-v3.4.0-geocoder-transients.php';
            
            migration001();

            // update version, if for some reason something fails afterwards, and we want to prevent this migration
            update_option(LEAFLET_MAP__DB_VERSION_KEY, '3.4.0');
        }
    }
}
