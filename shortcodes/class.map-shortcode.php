<?php
/**
 * Map Shortcode
 *
 * Displays map with [leaflet-map ...atts] 
 *
 * JavaScript equivalent : L.map("id");
 * 
 * PHP Version 5.5
 * 
 * @category Shortcode
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
} 

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.shortcode.php';

/**
 * Leaflet_Map_Shortcode Class
 */
class Leaflet_Map_Shortcode extends Leaflet_Shortcode
{
    /**
     * Instantiate class
     */
    public function __construct()
    {
        parent::__construct();
        $this->enqueue();
    }

    /**
     * Enqueue Scripts and Styles for Leaflet 
     * 
     * @return null
     */
    protected function enqueue()
    {
        wp_enqueue_style('leaflet_stylesheet');
        wp_enqueue_script('wp_leaflet_map');

        if (wp_script_is('leaflet_mapquest_plugin', 'registered')) {
            // mapquest doesn't accept direct tile access as of July 11, 2016
            wp_enqueue_script('leaflet_mapquest_plugin');
        }

        // enqueue user-defined scripts
        do_action('leaflet_map_enqueue');
    }

    /**
     * Merge shortcode options with default options
     *
     * @param array|string $atts    key value pairs from shortcode 
     * 
     * @return array new atts, which is actually an array
     */
    protected function getAtts($atts='')
    {
        $atts = (array) $atts;
        extract($atts);

        $settings = Leaflet_Map_Plugin_Settings::init();

        $atts['zoom'] = array_key_exists('zoom', $atts) ? 
            $zoom : $settings->get('default_zoom');
        $atts['height'] = empty($height) ? 
            $settings->get('default_height') : $height;
        $atts['width'] = empty($width) ? $settings->get('default_width') : $width;
        $atts['zoomcontrol'] = array_key_exists('zoomcontrol', $atts) ?
            $zoomcontrol : $settings->get('show_zoom_controls');
        $atts['min_zoom'] = array_key_exists('min_zoom', $atts) ? 
            $min_zoom : $settings->get('default_min_zoom');
        $atts['max_zoom'] = empty($max_zoom) ? 
            $settings->get('default_max_zoom') : $max_zoom;
        $atts['scrollwheel'] = array_key_exists('scrollwheel', $atts) 
            ? $scrollwheel 
            : $settings->get('scroll_wheel_zoom');
        $atts['doubleclickzoom'] = array_key_exists('doubleclickzoom', $atts) ? 
            $doubleclickzoom : $settings->get('double_click_zoom');
        
        // @deprecated backwards-compatible fit_markers
        $atts['fit_markers'] = array_key_exists('fit_markers', $atts) ? 
            $fit_markers : $settings->get('fit_markers');

        // fitbounds is what it should be called @since v2.12.0
        $atts['fitbounds'] = array_key_exists('fitbounds', $atts) ? 
            $fitbounds : $atts['fit_markers'];

        /* allow percent, but add px for ints */
        $atts['height'] .= is_numeric($atts['height']) ? 'px' : '';
        $atts['width'] .= is_numeric($atts['width']) ? 'px' : '';   

        // maxbounds as string: maxbounds="50, -114; 52, -112"
        $maxBounds = isset($maxbounds) ? $maxbounds : null;

        if ($maxBounds) {
            try {
                // explode by semi-colons and commas
                $maxBounds = preg_split("[;|,]", $maxBounds);
                $maxBounds = array(
                    array(
                        $maxBounds[0], $maxBounds[1]
                    ),
                    array(
                        $maxBounds[2], $maxBounds[3]
                    )
                );
            } catch (Exception $e) {
                $maxBounds = null;
            }
        }

        /* 
        need to allow 0 or empty for removal of attribution 
        */
        if (!array_key_exists('attribution', $atts)) {
            $atts['attribution'] = $settings->get('default_attribution');
        }

        /* allow a bunch of other options */
        // http://leafletjs.com/reference.html#map
        $more_options = array(
            'closePopupOnClick' => isset($closepopuponclick) ? 
                $closepopuponclick : null,
            'trackResize' => isset($trackresize) ? $trackresize : null,
            'boxZoom' => (isset($boxzoom) 
                ? $boxzoom 
                : isset($boxZoom))
                    ? $boxZoom
                    : null,
            'touchZoom' => isset($touchZoom) ? $touchZoom : null,
            'dragging' => isset($dragging) ? $dragging : null,
            'keyboard' => isset($keyboard) ? $keyboard : null,
        );

        // filter out nulls
        $more_options = $this->LM->filter_null($more_options);
        
        // custom field for moving to JavaScript
        $more_options['fitBounds'] = $atts['fitbounds'];

        // change string booleans to booleans
        $more_options = filter_var_array($more_options, FILTER_VALIDATE_BOOLEAN);

        if ($maxBounds) {
            $more_options['maxBounds'] = $maxBounds;
        }

        // custom field for moving to javascript
        $more_options['attribution'] = $atts['attribution'];

        // wrap as JSON
        $atts['more_options'] = json_encode($more_options);

        return $atts;
    }

    /**
     * Get script for shortcode
     * 
     * @param array  $atts    sometimes this is null
     * @param string $content anything within a shortcode
     * 
     * @return string HTML
     */
    protected function getHTML($atts='', $content=null)
    {
        extract($this->getAtts($atts));

        if (!empty($address)) {
            /* try geocoding */
            include_once LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php';
            $location = new Leaflet_Geocoder($address);
            $lat = $location->lat;
            $lng = $location->lng;
        }

        $settings = Leaflet_Map_Plugin_Settings::init();

        /*
        map uses lat/lng
        */
        $lat = empty($lat) ? $settings->get('default_lat') : $lat;
        $lng = empty($lng) ? $settings->get('default_lng') : $lng;

        /*
        mapquest doesn't need tile urls
        */
        if (wp_script_is('leaflet_mapquest_plugin', 'registered')) {
            $tileurl = '';
            $subdomains = '';
        } else {
            $tileurl = empty($tileurl) ? $settings->get('map_tile_url') : $tileurl;
            $subdomains = empty($subdomains) ? 
                $settings->get('map_tile_url_subdomains') : $subdomains;
        }
        
        $detect_retina = empty($detect_retina) ? $settings->get('detect_retina') : $detect_retina;

        /* should be iterated for multiple maps */
        ob_start(); ?>
        <div class="leaflet-map WPLeafletMap"
            style="height:<?php 
                echo $height; 
            ?>; width:<?php 
                echo $width; 
            ?>;"></div>
        <script>
        // push deferred map creation function
        window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
        window.WPLeafletMapPlugin.push(function () {
            var baseUrl = '<?php echo $tileurl; ?>';
            var base = (!baseUrl && window.MQ) ? 
                MQ.mapLayer() : L.tileLayer(baseUrl, { 
                    subdomains: '<?php echo $subdomains; ?>',
                    detectRetina: <?php echo $detect_retina; ?>,
                });
            var options = L.Util.extend({}, {
                    maxZoom: <?php echo $max_zoom; ?>,
                    minZoom: <?php echo $min_zoom; ?>,
                    layers: [base],
                    zoomControl: <?php echo $zoomcontrol; ?>,
                    scrollWheelZoom: <?php echo $scrollwheel; ?>,
                    doubleClickZoom: <?php echo $doubleclickzoom; ?>,
                    attributionControl: false
                }, <?php echo $more_options; ?>);
            window.WPLeafletMapPlugin.createMap(options)
                .setView(<?php 
                    echo '[' . $lat . ',' . $lng . '],' . $zoom; 
                ?>);
        });</script><?php

        $show_scale = isset($show_scale) ? $show_scale : $settings->get('show_scale');

        if ($show_scale) {
            echo do_shortcode('[leaflet-scale]');
        }

        return ob_get_clean();
    }
}