<?php
/**
* Geocoder
*
* calls the specific geocoder function (chosen in admin or default: osm)
*
*/

class Leaflet_Geocoder {
    /**
    * Geocoder should return this on error/not found
    * @var array $not_found
    */
    private static $not_found = array('lat' => 0, 'lng' => 0);
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

    /** key for all locations */
    public static $locations_key = 'leaflet_geocoded_locations';

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

        $cached_address = 'leaflet_' . $geocoder . '_' . $address;

        /* retrieve cached geocoded location */
        $found_cache = $this->get_cache( $cached_address, $address );

        if ( $found_cache ) {
            $location = $found_cache;
        } else {
            // try geocoding
            $geocoding_method = $geocoder . '_geocode';

            try {
                $location = (Object) $this->$geocoding_method( $address );

                /* update location data in db/cache */
                $this->set_cache( $cached_address, $location );

                /* add cache to cached list for cleanup */
                $this->update_caches( $cached_address );
            } catch (Exception $e) {
                /**
                 * @since 3.4.0
                 * use 'leaflet_geocoder_not_found' filter to return your own not_found response
                 */
                $location = apply_filters( 'leaflet_geocoder_not_found', self::$not_found );
            }
        }

        if (isset($location->lat) && isset($location->lng)) {
            $this->lat = $location->lat;
            $this->lng = $location->lng;
        }
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
     * gets a single location's coordinates from the cached locations
     */
    public function get_cache($address_key, $plain_address) {
        /** 
         * @since 3.4.0
         * using 'leaflet_geocoder_get_cache', 
         * you can return any value that is not identical to the address_key to avoid using get_transient
         */
        $filtered = apply_filters( 'leaflet_geocoder_get_cache', $address_key, $plain_address );

        if ($filtered === $address_key) {
            return get_transient( $address_key );
        }

        return $filtered;
    }

    /** 
     * gets the array of saved locations and updates an individual location
     */
    public function set_cache($key, $value) {
        /** 
         * @since 3.4.0
         * using 'leaflet_geocoder_set_cache', 
         * you can return any falsy value to omit the set_transient
         */
        if (apply_filters('leaflet_geocoder_set_cache', $key, $value)) {
            // get user-defined expiry (maybe this should be an admin option?)
            $expiry = apply_filters('leaflet_geocoder_expiry', null);

            if ($expiry === null) {
                // stagger caches between 200-400 days to prevent all caches expiring on the same day
                $stagger = random_int(200, 400);
                $expiry = DAY_IN_SECONDS * $stagger;
            }

            set_transient( $key, $value, $expiry );
        }
    }

    /**
     * Appends an address to a list of addresses in the db, for cleanup
     */
    public function update_caches( $address ) {
        /** 
         * @since 3.4.0
         * using 'leaflet_geocoder_update_caches', 
         * you can return any falsy value to omit the set_transient
         */
        if (apply_filters('leaflet_geocoder_update_caches', $address)) {
            $locations = get_transient( self::$locations_key );
            
            if (!$locations) {
                $locations = array();
            }
            
            array_push( $locations, $address );
            
            // set to 25 year expiry since we never really want it to expire
            // but omitting expiry causes it to autoload
            set_transient( self::$locations_key, $locations, YEAR_IN_SECONDS * 25 );
        }
    }

    /**
    * Removes all location caches
    */
    public static function remove_caches() {
        /** @since 3.4.0 */
        do_action('leaflet_geocoder_remove_caches');

        $addresses = get_transient( self::$locations_key );

        if ( !$addresses ) {
            return;
        }

        foreach ($addresses as $address) {
            delete_transient( $address );
        }

        delete_transient( self::$locations_key );
    }
}