<?php
/**
 * Image Overlay Shortcode
 *
 * Use with [leaflet-image-overlay ...]
 *
 * @category Shortcode
 * @author   Luca Cireddu <sardylan@gmail.com>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.shortcode.php';

class Leaflet_Image_Overlay_Shortcode extends Leaflet_Shortcode
{
    protected $type = 'L.imageOverlay';
    protected $default_src = 'https://maps.lib.utexas.edu/maps/historical/newark_nj_1922.jpg';
    protected $default_bounds = "40.712216,-74.22655;40.773941,-74.12544";

    /**
     * Get Script for Shortcode
     *
     * @param string $atts could be an array
     * @param string $content optional
     *
     * @return string HTML script
     */
    protected function getHTML($atts = '', $content = null)
    {
        if (!empty($atts)) {
            extract($atts, EXTR_SKIP);
        }

        /* acquire image boundaries */
        $url = empty($url) ? $this->default_src : $url;
        /* prefer src */
        $url = empty($src) ? $url : $src;

        $bounds = isset($bounds) ? $bounds : $this->default_bounds;
        $bounds = $this->LM->convert_bounds_str_to_arr($bounds);

        $options = array(
            'interactive' => isset($interactive) ? $interactive : null,
            'opacity' => isset($opacity) ? $opacity : null,
            'alt' => isset($alt) ? $alt : null,
            'crossOrigin' => isset($crossorigin) ? $crossorigin : null,
            'errorOverlayUrl' => isset($erroroverlayurl) ? $erroroverlayurl : null,
            'zIndex' => isset($zindex) ? $zindex : null,
            'className' => isset($classname) ? $classname : null,
            // filter out any unwanted HTML tags (including img)
            'attribution' => isset($attribution) ? wp_kses_post($attribution) : null,
            'keepAspectRatio' => isset($keepAspectRatio) ? $keepAspectRatio : true,
        );

        $args = array(
            'interactive' => FILTER_VALIDATE_BOOLEAN,
            'opacity' => FILTER_VALIDATE_FLOAT,
            'alt' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'crossOrigin' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'errorOverlayUrl' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'zIndex' => FILTER_VALIDATE_INT,
            'className' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'attribution' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'keepAspectRatio' => FILTER_VALIDATE_BOOLEAN
        );

        $options = $this->LM->json_sanitize($options, $args);

        ob_start();
        ?>/*<script>*/
            var group = window.WPLeafletMapPlugin.getCurrentGroup();
            var src = '<?php echo htmlspecialchars($url, ENT_QUOTES); ?>';
            var options = <?php echo $options; ?>;
            var bounds = <?php echo json_encode($bounds); ?>;
            var layer = <?php echo $this->type; ?>(src, bounds, options);
            layer.addTo(group);
            window.WPLeafletMapPlugin.overlays.push( layer );
        <?php
        $script = ob_get_clean();

        return $this->wrap_script($script, 'WPLeafletOverlayShortcode');
    }
}