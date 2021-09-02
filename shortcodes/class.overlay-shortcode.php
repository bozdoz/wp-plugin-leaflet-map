<?php
/**
 * Image Overlay Shortcode
 *
 * Use with [leaflet-overlay ...]
 *
 * @category Shortcode
 * @author   Luca Cireddu <sardylan@gmail.com>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once LEAFLET_MAP__PLUGIN_DIR . 'shortcodes/class.shortcode.php';

/**
 * Leaflet Overlay Shortcode Class
 */
class Leaflet_Overlay_Shortcode extends Leaflet_Shortcode
{
    /**
     * Get Script for Shortcode
     *
     * @param string $atts could be an array
     * @param string $content optional
     *
     * @return null
     */
    protected function getHTML($atts = '', $content = null)
    {
        if (!empty($atts)) {
            extract($atts, EXTR_SKIP);
        }

        /* acquire image boundaries */
        $url = empty($url) ? "" : $url;

        /* acquire image boundaries */
        $north = empty($north) ? "0" : $north;
        $south = empty($south) ? "0" : $south;
        $east = empty($east) ? "0" : $east;
        $west = empty($west) ? "0" : $west;

        /* validate lat/lng */
        $north = $this->LM->filter_float($north);
        $south = $this->LM->filter_float($south);
        $east = $this->LM->filter_float($east);
        $west = $this->LM->filter_float($west);

        $default_image_overlay = 'L.imageOverlay';

        $options = array(
            'interactive' => true,
            'opacity' => isset($opacity) ? $opacity : null,
            'alt' => isset($alt) ? $alt : null,
            'crossOrigin' => isset($crossOrigin) ? $crossOrigin : null,
            'errorOverlayUrl' => isset($errorOverlayUrl) ? $errorOverlayUrl : null,
            'zIndex' => isset($zIndex) ? $zIndex : null,
            'className' => isset($className) ? $className : null
        );

        $args = array(
            'interactive' => FILTER_VALIDATE_BOOL,
            'opacity' => FILTER_VALIDATE_FLOAT,
            'alt' => FILTER_SANITIZE_STRING,
            'crossOrigin' => FILTER_SANITIZE_STRING,
            'errorOverlayUrl' => FILTER_SANITIZE_STRING,
            'zIndex' => FILTER_VALIDATE_INT,
            'className' => FILTER_SANITIZE_STRING,
        );

        $options = $this->LM->json_sanitize($options, $args);

        ob_start();
        ?>/*
        <script>*/
            var map = window.WPLeafletMapPlugin.getCurrentMap();
            var group = window.WPLeafletMapPlugin.getCurrentGroup();

            var imageUrl = "<?php echo $url; ?>";

            var southWest = new L.latLng(<?php echo $south; ?>, <?php echo $west; ?>);
            var northeast = new L.latLng(<?php echo $north; ?>, <?php echo $east; ?>);
            var imageBounds = new L.latLngBounds(southWest, northeast);

            var imageOptions = <?php echo $options ?>;

            var imageOverlay = <?php echo $default_image_overlay; ?>(imageUrl, imageBounds, imageOptions);
            imageOverlay.addTo(group);

            map.addLayer(imageOverlay);
        <?php

        $script = ob_get_clean();

        return $this->wrap_script($script, 'WPLeafletImageOverlayShortcode');
    }
}