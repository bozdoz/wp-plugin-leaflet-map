<?php
/**
 * Image Shortcode
 *
 * Displays map with [leaflet-image src="path/to/image.jpg"] 
 *
 * JavaScript equivalent : L.imageOverlay('path/to/image.jpg');
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

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.map-shortcode.php';

/**
 * Leaflet Image Shortcode Class
 */
class Leaflet_Image_Shortcode extends Leaflet_Map_Shortcode
{
    /**
     * Get HTML for shortcode
     * 
     * @param string $atts    or Array
     * @param string $content produced by adding atts to JavaScript
     * 
     * @return string HTML script
     */
    protected function getHTML($atts='', $content=null)
    {
        extract($this->getAtts($atts));

        /* only required field for image map (src/source) */
        $src = empty($src) ? '' : $src;
        $source = empty($source) ? 'https://picsum.photos/1000/1000/' : $source;
        $source = empty($src) ? $source : $src;

        ob_start();
        ?>
        <div 
            id="leaflet-map-image-<?php echo $this->map_id; ?>" 
            class="leaflet-map" 
            style="height:<?php 
                echo $height; 
            ?>; width:<?php 
                echo $width; 
            ?>;"></div>
        <script>
        window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
        window.WPLeafletMapPlugin.push(function () {
            var map,
                options = L.Util.extend({}, {
                    maxZoom: <?php echo $max_zoom; ?>,
                    minZoom: <?php echo $min_zoom; ?>,
                    zoomControl: <?php echo $zoomcontrol; ?>,
                    scrollWheelZoom: <?php echo $scrollwheel; ?>,
                    doubleClickZoom: <?php echo $doubleclickzoom; ?>,
                    attributionControl: false
                }, <?php echo $more_options; ?>, {
                    crs: L.CRS.Simple
                }),
                image_src = '<?php echo $source; ?>',
                img = new Image(),
                zoom = <?php echo $zoom; ?>;
            img.onload = function() {
                var h = img.height,
                    w = img.width,
                    projected_zoom = zoom + 1,
                    southWest = map.unproject([-w, h], projected_zoom),
                    northEast = map.unproject([w, -h], projected_zoom),
                    bounds = new L.LatLngBounds(southWest, northEast);
                L.imageOverlay( image_src, bounds ).addTo( map );
                map.setMaxBounds(bounds);
            };
            img.src = image_src;
            map = L.map('leaflet-map-image-<?php echo $this->map_id; ?>', options).setView([0, 0], zoom);
            // make it known that it is an image map
            map.is_image_map = true;
            if (<?php echo $fitbounds; ?>) {
                map._shouldFitBounds = true;
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
            window.WPLeafletMapPlugin.maps.push( map );
            window.WPLeafletMapPlugin.images.push( img );
        }); // end add
        </script>
        <?php
        return ob_get_clean();
    }
}
