<?php
/**
* Geocoder
*
* calls the specific geocoder function (chosen in admin or default: google_geocode)
*
*/

class Leaflet_Geocoder {
    /**
    * Geocoder should return this on error/not found
    * @var array $not_found
    */
    private $not_found = array('lat' => 0, 'lng' => 0);


    /** common identifier for cache keys using plugin slug */
    private const GEOCACHE_PREFIX = 'leaflet-map_';

    /**
    * Latitude
    * @var float $lat
    */
    public $lat = 0;
    /**
    * Longitude
    * @var float $lng
    */
    public $lng = 0;

    /**
    * new Geocoder from address
    *
    * handles url encoding and caching
    *
    * @param string $address the requested address to look up
    * @return NOTHING
    */
    public function __construct ($address) {
        $settings = Leaflet_Map_Plugin_Settings::init();
        // trim all quotes (even smart) from address
        $address = trim($address, '\'"”');
        $address = urlencode( $address );
        
        $geocoder = $settings->get('geocoder');

        $cached_address = $geocoder . '_' . $address;

        /* lookup cached geocoded location */
        $found_cache = $this->geocache_transient_get( $cached_address );

        if ( $found_cache ) {
            $location = $found_cache;
        } else {
            // try geocoding
            $geocoding_method = $geocoder . '_geocode';

            try {
                $location = (object) $this->$geocoding_method($address);

            } catch (Exception $e) {
                // failed
                $location = $this->not_found;
            }
        }

        if (isset($location->lat) && isset($location->lng)) {
            $this->lat = $location->lat;
            $this->lng = $location->lng;
            /*  we have a complete location record with lat/lng, cache it  */
            $this->geocache_transient_set($cached_address, $location, $this->geocache_get_ttl($geocoder));
        }
    }

    /**
    * Removes location caches if they ended up in the options table
    * borrowed from https://kellenmace.com/delete-transients-with-prefix-in-wordpress/
    * This function is only needed if manual cleanup is required;
    * all records that are added with geocache_transient_set()
    * have an expiry set and so will be removed by
    * Wordpress automatically ( cron event delete_expired_transients )
    * Using $wpdb is necessary because the WP API does not provide a way to
    * delete transients by prefix or to get a list of all non-autoloaded transients
    * @return void
    */
    public static function remove_caches()
    {

        // _transient_ is prefixed to all transient keys in the options table
        $prefix = '_transient_' . self::GEOCACHE_PREFIX;

        global $wpdb;

        $prefix = $wpdb->esc_like('_transient_' . self::GEOCACHE_PREFIX);
        $sql    = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
        $keys   = $wpdb->get_results($wpdb->prepare($sql, $prefix . '%'), ARRAY_A);

        if (is_wp_error($keys) || !is_array($keys)) {
            return ;
        }

        $transient_keys = array_map(function ($key) {
            // Remove '_transient_' from the option name.
            return ltrim($key['option_name'], '_transient_');
        }, $keys);

        foreach($transient_keys as $transient_name) {
            delete_transient($transient_name);
        }
        return;

    }

    /**
    * Used by geocoders to make requests via curl or file_get_contents
    *
    * includes a try/catch
    *
    * @param string $url    the urlencoded request url
    * @return varies object from API or null (failed)
    */
    private function get_url( $url ) {
        $referer = get_site_url();

        if (in_array('curl', get_loaded_extensions())) {
            /* try curl */
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_REFERER, $referer);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

            $data = curl_exec($ch);
            curl_close($ch);

            return $data;
        } else if (ini_get('allow_url_fopen')) {
                    /* try file get contents */

                    $opts = array(
                        'http' => array(
                            'header' => array("Referer: $referer\r\n")
                        )
                    );
                    $context = stream_context_create($opts);

            return file_get_contents($url, false, $context);
        }

        $error_msg = 'Could not get url: ' . $url;
        throw new Exception( $error_msg );
    }

    /**
    * Google geocoder (https://developers.google.com/maps/documentation/geocoding/start)
    *
    * @param string $address    the urlencoded address to look up
    * @return varies object from API or null (failed)
    */

    private function google_geocode ( $address ) {
        // Leaflet_Map_Plugin_Settings
        $settings = Leaflet_Map_Plugin_Settings::init();
        $key = $settings->get('google_appkey');
        
        $geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s';
        $geocode_url = sprintf($geocode_url, $address, $key);
        
        $json = $this->get_url($geocode_url);
        $json = json_decode($json);

        /* found location */
        if ($json->status == 'OK') {
            
            $location = $json->results[0]->geometry->location;

            return (Object) $location;
        }
        
        throw new Exception('No Address Found');
    }

    /**
    * OpenStreetMap geocoder Nominatim (https://nominatim.openstreetmap.org/)
    *
    * @param string $address    the urlencoded address to look up
    * @return varies object from API or null (failed)
    */

    private function osm_geocode ( $address ) {
        $geocode_url = 'https://nominatim.openstreetmap.org/?format=json&limit=1&q=';
        $geocode_url .= $address;
        $json = $this->get_url($geocode_url);
        $json = json_decode($json);

        if (isset($json[0]->lat) && isset($json[0]->lon)) {
            return (Object) array(
                'lat' => $json[0]->lat,
                'lng' => $json[0]->lon,
            );
        } else {
            return false;
        }
    }

    /**
     * TODO: does this still work?
     * Danish Addresses Web Application 
     * (https://dawa.aws.dk)
     *
     * @param string $address    the urlencoded address to look up
     * @return varies object from API or null (failed)
     */
    private function dawa_geocode ( $address ) {
        $geocode_url = 'https://dawa.aws.dk/adresser?format=json&q=';
        $geocode_url .= $address;
        $json = $this->get_url($geocode_url);
        $json = json_decode($json);
        
        /* found location */
        return (Object) array(
            'lat' => $json[0]->adgangsadresse->adgangspunkt->koordinater[1],
            'lng' => $json[0]->adgangsadresse->adgangspunkt->koordinater[0]
        );
    }


    /**
     * Get location from transient cache if it exists
     * cache key must be shorter than 172 chars as required by get_transient,
     * and not polluted by strange characters in address to work with every cache backend
     * so we use md5 to create a hash of the address
     *
     * @param string $address
     * @return varies result value from cache or false
     */
    private function geocache_transient_get($address)
    {
        $transient_key  =  self::GEOCACHE_PREFIX . md5($address);
        return get_transient($transient_key);
    }

    /**
     * Store lookup result in transient cache
     * so far, no error handling is done, so if cache is not available,
     * we silently fail and always return true
     *
     * @param string $address
     * @param object $location
     * @param int $ttl
     * @return bool
     */
    private function geocache_transient_set($address, $location, $ttl)
    {
        $transient_key  = self::GEOCACHE_PREFIX . md5($address);
        set_transient($transient_key, $location, $ttl);
        return true;
    }

    /**
     * set transient ttl depending on the geocoder used
     * for now we use a reasonable hardcoded value per geocoder,
     * but this should probably be configurable in admin @todo
     *
     * @param string $geocoder
     * @return int
     */
    private function geocache_get_ttl($geocoder)
    {
        switch ($geocoder) {

            case 'osm':
                return 3 * MONTH_IN_SECONDS;
            default:
                return MONTH_IN_SECONDS;
        }
    }


}
