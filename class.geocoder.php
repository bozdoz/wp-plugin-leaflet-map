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

        if ( $found_cache ) {
            $location = $found_cache;
        } else {
            // try geocoding
            $geocoding_method = $geocoder . '_geocode';

            try {
                $location = (Object) $this->$geocoding_method( $address );

                /* add location */
                add_option($cached_address, $location);

                /* add option key to locations for clean up purposes */
                $locations = get_option('leaflet_geocoded_locations', array());
                array_push($locations, $cached_address);
                update_option('leaflet_geocoded_locations', $locations);
            } catch (Exception $e) {
                // failed
                $location = $this->not_found;
            }
        }

        // set variables
        $this->lat = $location->lat;
        $this->lng = $location->lng;
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
        
        return (Object) array(
            'lat' => $json[0]->lat,
            'lng' => $json[0]->lon,
        );
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