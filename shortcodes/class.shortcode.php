<?php
/**
* Abstract Shortcode Class
*
* Use with add_shortcode('leaflet-map', array('Leaflet_Shortcode', 'shortcode'))
* extend and manipulate __construct
*
* @var array $atts
* @var string $content
*/
abstract class Leaflet_Shortcode {
	protected $LM;

	/**
	* Generate HTML from the shortcode
	* Maybe won't always be required
	* @since 2.8.2
	* @var array $atts
	* @var string $content
	* @return HTML
	*/
	abstract protected function getHTML($atts='', $content=null);

	public static function getClass () {
		return function_exists( 'get_called_class' ) ? get_called_class() : __CLASS__;
	}

	/**
	* Instantiate class and get HTML for shortcode
	* @var array $atts
	* @var string $content
	*/
	public static function shortcode ($atts, $content = null) {
		$class = self::getClass();
		$instance = new $class($atts, $content);
		return $instance->getHTML($atts, $content);
	}

	protected function __construct() {
		$this->LM = Leaflet_Map::init();
	}
}