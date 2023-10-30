<?php
/**
 * Wms Shortcode
 *
 * Displays map with [leaflet-wms src="path/to/wms"]
 *
 * JavaScript equivalent : L.TileLayer.wms('path/to/wms?', {layer: 'layername', crs: L.CRS.EPSG3857});
 *
 * @category Shortcode
 * @author Janne Jakob Fleischer <janne.fleischer@ils-forschung.de>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.map-shortcode.php';

/**
 * Leaflet Image Shortcode Class
 */
class Leaflet_Wms_Shortcode extends Leaflet_Map_Shortcode
{
  /**
   * Get HTML for shortcode
   *
   * @param string $atts    or Array
   * @param string $content produced by adding atts to JavaScript
   *
   * @return string HTML script
   */
  protected function getHTML($atts = '', $content = null)
  {
    extract($this->getAtts($atts));

    if (!empty($address)) {
      include_once LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php';
      $location = new Leaflet_Geocoder($address);
      $lat = $location->lat;
      $lng = $location->lng;
    }

    $lat_set = isset($lat) || isset($y);
    $lng_set = isset($lng) || isset($x);

    $lat = empty($lat) ? (empty($y) ? '0' : $y) : $lat;
    $lng = empty($lng) ? (empty($x) ? '0' : $x) : $lng;

    // validate lat/lng
    $lat = $this->LM->filter_float($lat);
    $lng = $this->LM->filter_float($lng);

    /* only required field for image map (src/source) */
    $src = empty($src) ? '' : $src;
    $source = empty($source)
      ? 'https://ows.mundialis.de/services/service?'
      : $source;
    $source = empty($src) ? $source : $src;

    $layer = empty($layer) ? 'TOPO-OSM-WMS' : $layer;

    $crs = str_replace(':', '', empty($crs) ? 'EPSG:3857' : $crs);

    ob_start();
    ?>/*<script>*/
var options = L.Util.extend({}, {
        attributionControl: false
    }, <?php echo $map_options; ?>);
var zoom = <?php echo $zoom; ?>;
var map = window.WPLeafletMapPlugin.createMap(options).setView(L.latLng(<?php echo $lat; ?>, <?php echo $lng; ?>), zoom);
var wmslayer = L.tileLayer.wms(
	'<?php echo $source; ?>', 
	{
		layers: '<?php echo $layer; ?>',
		crs: L.CRS['<?php echo $crs; ?>']
	}
).addTo( map );

<?php
$script = ob_get_clean();

return $this->getDiv($height, $width) .
  $this->wrap_script($script, 'WPLeafletWmsShortcode');
  }
}
