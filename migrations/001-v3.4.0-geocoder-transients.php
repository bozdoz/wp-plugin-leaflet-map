<?php
/**
 * In 3.4.0 we moved to using transients for geocoder caches
 */
function migration001()
{
    $all_address_key = 'leaflet_geocoded_locations';
    // get option list of all addresses
    $all_options = get_option($all_address_key, []);

    foreach ($all_options as $address) {
        // update each address to a transient
        $value = get_option($address);

        if (!$value) {
            continue;
        }

        $expiry = apply_filters('leaflet_geocoder_expiry', null);

        if ($expiry === null) {
            // stagger caches between 200-400 days to prevent all caches expiring on the same day
            $stagger = random_int(200, 400);
            $expiry = DAY_IN_SECONDS * $stagger;
        }

        set_transient($address, $value, $expiry);

        // delete each option address
        delete_option($address);
    }

    // move list of all addresses to a transient
    // set to 25 year expiry since we never really want it to expire
    // but omitting expiry causes it to autoload
    set_transient($all_address_key, $all_options, YEAR_IN_SECONDS * 25);

    // delete option list of all addresses
    delete_option($all_address_key);
}
