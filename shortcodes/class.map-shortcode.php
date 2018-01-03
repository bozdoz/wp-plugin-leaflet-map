<?php
/**
* Map Shortcode
*
* Displays map with [leaflet-map ...atts] 
*
* JavaScript equivalent : L.map("id");
*
* @param array $atts
* @return string $content produced by adding atts to JavaScript
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

include_once(LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.shortcode.php');

class Leaflet_Map_Shortcode extends Leaflet_Shortcode {
	protected $map_id = 0;

	public function __construct ($atts) {
		parent::__construct();
		$this->enqueue();
		$this->incrementMap();
	}

	protected function incrementMap () {
		$this->LM->map_count++;
		$this->map_id = $this->LM->map_count;
	}

	/**
	* Merge shortcode options with default options
	*
	* @param array|string $atts key value pairs from shortcode 
	* @param string $content 	inner text in shortcode
	* @return array 			new atts, which is actually an array
	*/

	protected function getAtts ($atts='', $content=null) {
		$atts = (array) $atts;
		extract($atts);

		$settings = Leaflet_Map_Plugin_Settings::init();

		$atts['zoom'] = array_key_exists('zoom', $atts) ? $zoom : $settings->get('default_zoom');
		$atts['height'] = empty($height) ? $settings->get('default_height') : $height;
		$atts['width'] = empty($width) ? $settings->get('default_width') : $width;
		$atts['zoomcontrol'] = array_key_exists('zoomcontrol', $atts) ? $zoomcontrol : $settings->get('show_zoom_controls');
		$atts['min_zoom'] = array_key_exists('min_zoom', $atts) ? $min_zoom : $settings->get('default_min_zoom');
		$atts['max_zoom'] = empty($max_zoom) ? $settings->get('default_max_zoom') : $max_zoom;
		$atts['scrollwheel'] = array_key_exists('scrollwheel', $atts) ? $scrollwheel : $settings->get('scroll_wheel_zoom');
		$atts['doubleclickzoom'] = array_key_exists('doubleclickzoom', $atts) ? $doubleclickzoom : $settings->get('double_click_zoom');
		$atts['fit_markers'] = array_key_exists('fit_markers', $atts) ? $fit_markers : $settings->get('fit_markers');

		/* allow percent, but add px for ints */
		$atts['height'] .= is_numeric($atts['height']) ? 'px' : '';
		$atts['width'] .= is_numeric($atts['width']) ? 'px' : '';   

		/* 
		need to allow 0 or empty for removal of attribution 
		*/
		if (!array_key_exists('attribution', $atts)) {
		    $atts['attribution'] = $settings->get('default_attribution');
		}

		/* allow a bunch of other options */
		// http://leafletjs.com/reference-1.0.3.html#map-closepopuponclick
		$more_options = array(
		    'closePopupOnClick' => isset($closepopuponclick) ? $closepopuponclick : NULL,
		    'trackResize' => isset($trackresize) ? $trackresize : NULL,
		    'boxZoom' => isset($boxzoom) ? $boxzoom : NULL,
		    'doubleClickZoom' => isset($doubleclickzoom) ? $doubleclickzoom : NULL,
		    'dragging' => isset($dragging) ? $dragging : NULL,
		    'keyboard' => isset($keyboard) ? $keyboard : NULL
	    );
		
		// filter out nulls
		$more_options = $this->LM->filter_null( $more_options );
		
		// change string booleans to booleans
		$more_options = filter_var_array($more_options, FILTER_VALIDATE_BOOLEAN);

		// wrap as JSON
		if ($more_options) {
		    $more_options = json_encode( $more_options );
		} else {
		    $more_options = '{}';
		}

		$atts['more_options'] = $more_options;

		return $atts;
	}

	protected function enqueue () {
		wp_enqueue_style('leaflet_stylesheet');
		wp_enqueue_script('leaflet_js');
		wp_enqueue_script('leaflet_map_init');

		if (wp_script_is('leaflet_mapquest_plugin', 'registered')) {
		    // mapquest doesn't accept direct tile access as of July 11, 2016
		    wp_enqueue_script('leaflet_mapquest_plugin');
		}
	}

	protected function getHTML ($atts='', $content=null) {
		extract($this->getAtts($atts));

		if (!empty($address)) {
		    /* try geocoding */
		    include_once(LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php');
		    $location = new Leaflet_Geocoder( $address );
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
		    $subdomains = empty($subdomains) ? $settings->get('map_tile_url_subdomains') : $subdomains;
		}

		/* should be iterated for multiple maps */
		ob_start(); ?>
		<div 
		    id="leaflet-map-<?php echo $this->map_id; ?>" 
		    class="leaflet-map" 
		    style="height:<?php echo $height; ?>; width:<?php echo $width; ?>;"></div>
		<script>
		WPLeafletMapPlugin.add(function () {
		    var baseUrl = '<?php echo $tileurl; ?>',
		        base = (!baseUrl && window.MQ) ? MQ.mapLayer() : L.tileLayer(baseUrl, { 
		           subdomains: '<?php echo $subdomains; ?>'
		        }),
		        options = L.Util.extend({}, {
		            maxZoom: <?php echo $max_zoom; ?>,
		            minZoom: <?php echo $min_zoom; ?>,
		            layers: [base],
		            zoomControl: <?php echo $zoomcontrol; ?>,
		            scrollWheelZoom: <?php echo $scrollwheel; ?>,
		            doubleClickZoom: <?php echo $doubleclickzoom; ?>,
		            attributionControl: false
		        }, <?php echo $more_options; ?>),
		        map = L.map('leaflet-map-<?php echo $this->map_id; ?>', options).setView([<?php echo $lat . ',' . $lng . '],' . $zoom; ?>);
		    if (<?php echo $fit_markers; ?>) {
		        map.fit_markers = true;
		    }
		<?php
		    if ($attribution) {
		        /* add any attributions, semi-colon-separated */
		        $attributions = explode(';', $attribution);

		        ?>
		        var attControl = L.control.attribution({prefix:false}).addTo(map);
		        <?php

		        foreach ($attributions as $a) {
		            ?>
		            attControl.addAttribution('<?php echo trim($a); ?>');
		            <?php
		        }
		    }
		?>
		WPLeafletMapPlugin.maps.push(map);
		}); // end add
		</script> <?php

		return ob_get_clean();
	}
}