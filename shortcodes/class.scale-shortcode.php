<?php
/**
 * Scale Shortcode
 *
 * Use with [leaflet-scale ...]
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
 * Leaflet Scale Shortcode Class
 */
class Leaflet_Scale_Shortcode extends Leaflet_Shortcode
{
    /**
     * Get Script for Shortcode
     * 
     * @param string $atts    shortcode attributes
     * @param string $content optional
     * 
     * @return string HTML
     */
    protected function getHTML($atts='', $content=null)
    {
        if (!empty($atts)) {
            extract($atts);
        }

        /**
         * Options:
         * https://leafletjs.com/reference.html#control-scale
        */

        $options = array(
          'maxWidth' => isset($maxwidth) ? $maxwidth : null,
          'metric' => isset($metric) ? $metric : null,
          'imperial' => isset($imperial) ? $imperial : null,
          'updateWhenIdle' => isset($updateWhenIdle) ? $updateWhenIdle : null,
          'position' => isset($position) ? $position : null,
        );

        $filters = array(
          'maxWidth' => FILTER_VALIDATE_INT,
          'metric' => FILTER_VALIDATE_BOOLEAN,
          'imperial' => FILTER_VALIDATE_BOOLEAN,
          'updateWhenIdle' => FILTER_VALIDATE_BOOLEAN,
          'position' => FILTER_SANITIZE_STRING,
        );

        $options = $this->LM->json_sanitize($options, $filters);

        ob_start();
        ?>
        <script>
        // push deferred function
        window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
        window.WPLeafletMapPlugin.push(function () {
            window.WPLeafletMapPlugin.createScale(<?php echo $options; ?>);
        });
        </script>
        <?php
        return ob_get_clean();
    }
}