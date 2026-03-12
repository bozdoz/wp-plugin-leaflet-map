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

        $cached_address = 'leaflet_' . $geocoder . '_' . $address;

        /* retrieve cached geocoded location */
        $found_cache = get_option( $cached_address );

        if ( $this->is_valid_cached_location( $found_cache ) ) {
            $location = $found_cache;
        } else {
            if ( false !== $found_cache ) {
                $this->remove_cache_key( $cached_address );
            }

            // try geocoding
            $geocoding_method = $geocoder . '_geocode';

            try {
                $location = (Object) $this->$geocoding_method( $address );

                if ( ! $this->is_valid_cached_location( $location ) ) {
                    throw new Exception('Invalid geocoder response');
                }

                /* add location */
                add_option($cached_address, $location);

                /* add option key to locations for clean up purposes */
                $locations = get_option('leaflet_geocoded_locations', array());
                if ( ! in_array( $cached_address, $locations, true ) ) {
                    array_push($locations, $cached_address);
                    update_option('leaflet_geocoded_locations', $locations);
                }
            } catch (Exception $e) {
                // failed
                $location = $this->not_found;
            }
        }

        if (isset($location->lat) && isset($location->lng)) {
            $this->lat = $location->lat;
            $this->lng = $location->lng;
        }
    }

    /**
    * Removes location caches
    */
    public static function remove_caches () {
        $addresses = get_option('leaflet_geocoded_locations', array());
        foreach ($addresses as $address) {
            delete_option($address);
        }
        delete_option('leaflet_geocoded_locations');
    }

    /**
    * Determines whether a cached geocode entry is valid.
    *
    * @param mixed $location Cached location value from WordPress options.
    * @return bool
    */
    private function is_valid_cached_location( $location ) {
        if ( ! is_object( $location ) ) {
            return false;
        }

        if ( ! isset( $location->lat ) || ! isset( $location->lng ) ) {
            return false;
        }

        return filter_var( $location->lat, FILTER_VALIDATE_FLOAT ) !== false
            && filter_var( $location->lng, FILTER_VALIDATE_FLOAT ) !== false;
    }

    /**
    * Removes a single geocode cache entry and its registry reference.
    *
    * @param string $cached_address Option key for the cached geocode.
    * @return void
    */
    private function remove_cache_key( $cached_address ) {
        delete_option( $cached_address );

        $addresses = get_option( 'leaflet_geocoded_locations', array() );
        $addresses = array_values(
            array_filter(
                $addresses,
                function ( $address ) use ( $cached_address ) {
                    return $address !== $cached_address;
                }
            )
        );

        if ( empty( $addresses ) ) {
            delete_option( 'leaflet_geocoded_locations' );
        } else {
            update_option( 'leaflet_geocoded_locations', $addresses );
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
    * @param string $address The URL-encoded address to look up.
    * @return object Object containing lat and lng properties.
    * @throws Exception When the request fails or no valid coordinates are returned.
    */
    private function osm_geocode( $address ) {
        $request_url = sprintf(
            'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=%s',
            $address
        );

        $accept_language = str_replace( '_', '-', get_locale() );
        
        $settings = Leaflet_Map_Plugin_Settings::init();
        $contact_email = $settings->get( 'nominatim_contact_email' );

        if ( empty( $contact_email ) ) {
            $contact_email = get_bloginfo( 'admin_email' );
        }

        $contact_email = apply_filters( 'leaflet_map_nominatim_contact_email', $contact_email );

        $agent = 'Nominatim query for ' . get_bloginfo( 'url' ) . '; contact ' . $contact_email;

        $response = wp_remote_get(
            $request_url,
            array(
                'user-agent' => $agent,
                'headers' => array(
                    'Accept-Language' => $accept_language,
                ),
            )
        );

        if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
            $json = json_decode( $response['body'] );

            if ( isset( $json[0]->lat ) && isset( $json[0]->lon ) ) {
                return (object) array(
                    'lat' => $json[0]->lat,
                    'lng' => $json[0]->lon,
                );
            }
        }

        throw new Exception('No Address Found');
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
}
