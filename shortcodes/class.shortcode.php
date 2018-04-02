<?php
/**
 * Abstract Shortcode
 *
 * PHP Version 5.5
 * 
 * @category Shortcode
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */

/**
 * Abstract Shortcode Class
 * 
 * Use with add_shortcode('leaflet-map', array ('Leaflet_Shortcode', 'shortcode'))
 * extend and manipulate __construct
 */
abstract class Leaflet_Shortcode
{
    protected $LM;

    /**
     * Generate HTML from the shortcode
     * Maybe won't always be required
     *
     * @param array  $atts    string
     * @param string $content Optional
     * 
     * @since 2.8.2
     * 
     * @return string (typically, return a script tag with Leaflet logic)
     */
    abstract protected function getHTML($atts='', $content=null);

    public static function getClass()
    {
        return function_exists('get_called_class') ? get_called_class() : __CLASS__;
    }

    /**
     * Instantiate class and get HTML for shortcode
     *
     * @param array  $atts    string
     * @param string $content Optional
     * 
     * @return string (see above)
     */
    public static function shortcode($atts, $content = null)
    {
        $class = self::getClass();
        $instance = new $class($atts, $content);
        return $instance->getHTML($atts, $content);
    }

    /**
     * Create an LM variable for each shortcode class 
     * instance
     */
    protected function __construct()
    {
        $this->LM = Leaflet_Map::init();
    }
}