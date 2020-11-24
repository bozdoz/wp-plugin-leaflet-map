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
        $atts['zoomcontrol'] = isset($zoomControl) 
            ? $zoomControl
            : (array_key_exists('zoomcontrol', $atts) 
                ? $zoomcontrol 
                : $settings->get('show_zoom_controls'));
        $atts['min_zoom'] = array_key_exists('min_zoom', $atts) ? 
            $min_zoom : $settings->get('default_min_zoom');
        $atts['max_zoom'] = empty($max_zoom) ? 
            $settings->get('default_max_zoom') : $max_zoom;
        $atts['scrollwheel'] = isset($scrollWheelZoom)
            ? $scrollWheelZoom
            : (array_key_exists('scrollwheel', $atts) 
                ? $scrollwheel 
                : $settings->get('scroll_wheel_zoom'));
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

        /* allow a bunch of other (boolean) options */
        // http://leafletjs.com/reference.html#map
        $map_options = array(
            'closePopupOnClick' => isset($closePopupOnClick)
                ? $closePopupOnClick
                : (isset($closepopuponclick)
                    ? $closepopuponclick 
                    : null),
            'trackResize' => isset($trackResize) 
                ? $trackResize
                : (isset($trackresize) 
                    ? $trackresize 
                    : null),
            'boxZoom' => isset($boxzoom) 
                ? $boxzoom 
                : (isset($boxZoom)
                    ? $boxZoom
                    : null),
            'touchZoom' => isset($touchZoom) ? $touchZoom : null,
            'dragging' => isset($dragging) ? $dragging : null,
            'keyboard' => isset($keyboard) ? $keyboard : null,
            'zoomAnimation' => isset($zoomAnimation) ?  $zoomAnimation : null,
            'fadeAnimation' => isset($fadeAnimation) ?  $fadeAnimation : null,
            'markerZoomAnimation' => isset($markerZoomAnimation) ?  $markerZoomAnimation : null,
            'inertia' => isset($inertia) ?  $inertia : null,
            'worldCopyJump' => isset($worldCopyJump) ?  $worldCopyJump : null,
            'tap' => isset($tap) ? $tap : null,
            'bounceAtZoomLimits' => isset($bounceAtZoomLimits) ? $bounceAtZoomLimits : null,
        );

        // filter out nulls
        $map_options = $this->LM->filter_null($map_options);
        
        // custom field for moving to JavaScript
        $map_options['fitBounds'] = $atts['fitbounds'];

        // change string booleans to booleans
        $map_options = filter_var_array($map_options, FILTER_VALIDATE_BOOLEAN);

        if ($maxBounds) {
            $map_options['maxBounds'] = $maxBounds;
        }

        // custom field for moving to javascript
        $map_options['attribution'] = $atts['attribution'];

        // wrap as JSON
        $atts['map_options'] = json_encode($map_options);

        // get raw variables, allowing for JavaScript variables in values
        $raw_map_options = array();
        foreach($map_options as $key=>$val) {
            $original_value = isset($atts[$key]) ? $atts[$key] : null;
            
            $liquid = $this->LM->liquid($original_value);

            if ($liquid && isset($liquid['raw']) && $liquid['raw']) {
                // raw leaves original value un-quoted
                $raw_map_options[$key] = $liquid['original'];
            }
        }

        $atts['raw_map_options'] = $this->LM->rawDict($raw_map_options);

        $tile_layer_options = array(
            'tileSize' => empty($tilesize) ? $settings->get('tilesize') : $tilesize,
            'subdomains' => empty($subdomains) ? $settings->get('map_tile_url_subdomains') : $subdomains,
            'id' => empty($mapid) ? $settings->get('mapid') : $mapid,
            'accessToken' => empty($accesstoken) ? $settings->get('accesstoken') : $accesstoken,
            'zoomOffset' => empty($zoomoffset) ? $settings->get('zoomoffset') : $zoomoffset,
            'noWrap' => filter_var(empty($nowrap) ? $settings->get('tile_no_wrap') : $nowrap, FILTER_VALIDATE_BOOLEAN),
        );
        
        $tile_layer_options = $this->LM->filter_empty_string($tile_layer_options);
        $tile_layer_options = $this->LM->filter_null($tile_layer_options);

        $atts['tile_layer_options'] = json_encode($tile_layer_options, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);

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
        } else {
            $tileurl = empty($tileurl) ? $settings->get('map_tile_url') : $tileurl;
        }
        
        $detect_retina = empty($detect_retina) ? $settings->get('detect_retina') : $detect_retina;

        $tile_min_zoom = $min_zoom;
        $tile_max_zoom = $max_zoom;
        
        // fix #114 tilelayer zoom with detect_retina
        if ($detect_retina && $min_zoom == $max_zoom) {
            $tile_min_zoom = 'undefined';
            $tile_max_zoom = 'undefined';
        }

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
                MQ.mapLayer() : L.tileLayer(baseUrl, 
                    L.Util.extend({}, {
                        detectRetina: <?php echo $detect_retina; ?>,
                        minZoom: <?php echo $tile_min_zoom; ?>,
                        maxZoom: <?php echo $tile_max_zoom; ?>,
                    }, <?php echo $tile_layer_options; ?>)
                );
            var options = L.Util.extend({}, {
                    maxZoom: <?php echo $max_zoom; ?>,
                    minZoom: <?php echo $min_zoom; ?>,
                    layers: [base],
                    zoomControl: <?php echo $zoomcontrol; ?>,
                    scrollWheelZoom: <?php echo $scrollwheel; ?>,
                    doubleClickZoom: <?php echo $doubleclickzoom; ?>,
                    attributionControl: false
                }, <?php echo $map_options; ?>, 
                <?php echo $raw_map_options; ?>);
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