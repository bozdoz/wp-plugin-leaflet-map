<?php
/**
* Image Shortcode
*
* Displays map with [leaflet-image source="path/to/image.jpg"] 
*
* JavaScript equivalent : L.imageOverlay('path/to/image.jpg');
*
* @param array $atts
* @return string $content produced by adding atts to JavaScript
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

include_once(LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.map-shortcode.php');

class Leaflet_Image_Shortcode extends Leaflet_Map_Shortcode {
	protected function getHTML ($atts, $content) {
		extract($this->getAtts($atts));

		/* only required field for image map */
		$source = empty($source) ? 'https://lorempixel.com/1000/1000/' : $source;

		ob_start();
        ?>
        <div 
            id="leaflet-map-image-<?php echo $this->map_id; ?>" 
            class="leaflet-map" 
            style="height:<?php echo $height; ?>; width:<?php echo $width; ?>;"></div>
        <script>
        WPLeafletMapPlugin.add(function () {
            var map,
                image_src = '<?php echo $source; ?>',
                img = new Image(),
                zoom = <?php echo $zoom; ?>;
            img.onload = function() {
                var center_h = img.height / (zoom * 4),
                    center_w = img.width / (zoom * 4);

                map.setView([center_h, center_w], zoom);

                L.imageOverlay( image_src, [[ center_h * 2, 0 ], [ 0, center_w * 2]] ).addTo( map );

                img.is_loaded = true;
            };
            img.src = image_src;
            map = L.map('leaflet-map-image-<?php echo $map_count; ?>', {
                maxZoom: 10,
                minZoom: 1,
                crs: L.CRS.Simple,
                zoomControl: <?php echo $zoomcontrol ?>,
                scrollWheelZoom: <?php echo $scrollwheel ?>
            }).setView([0, 0], zoom);

            // make it known that it is an image map
            map.is_image_map = true;

            WPLeafletMapPlugin.maps.push( map );
            WPLeafletMapPlugin.images.push( img );
        }); // end add
        </script>
        <?php
        return ob_get_clean();
	}
}