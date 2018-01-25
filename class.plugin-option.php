<?php
/** 
* 
* Leaflet_Map_Plugin_Option
* 
* store values; render widgets
* @param array $details array of option details
**/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Leaflet_Map_Plugin_Option {
	/**
	* @var $default default value
	* either string or int/boolean?
	*/
	public $default = '';
	/**
	* @var $type input type
	* ex: ('text', 'select', 'checkbox')
	*/
	public $type;
	/**
	* @var $options  optional
	* used for select; maybe checkbox/radio
	*/
	public $options = array();
	public $helptext = '';

	function __construct ($details = array()) {
		if (!$details) {
			// just an empty db entry (for now)
			// nothing to store, nothing to render
			return;
		}

		$option_filter = array(
			'display_name' 	=> 	FILTER_SANITIZE_STRING,
			'default' 		=> 	null,
			'type' 			=> 	FILTER_SANITIZE_STRING,
			'options' 		=> 	array(
								'filter' => FILTER_SANITIZE_STRING,
				                'flags'	 => FILTER_FORCE_ARRAY
				            ),
			'helptext' 		=> 	FILTER_SANITIZE_STRING
		);

		// get matching keys only
		$details = array_intersect_key($details, $option_filter);

		// apply filter
		$details = filter_var_array($details, $option_filter);

		foreach ($details as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	* Renders a widget
	* @param value required chosen value
	* @return HTML
	*/
	function widget ($name, $value) {
		switch ($this->type) {
			case 'text':
				?>
		<input 
			class="full-width" 
			name="<?php echo $name; ?>" 
			type="text" 
			id="<?php echo $name; ?>" 
			value="<?php echo htmlspecialchars( $value ); ?>" 
			/>
				<?php
				break;
			
			case 'textarea':
			?>

		<textarea 
			id="<?php echo $name; ?>"
			class="full-width" 
			name="<?php echo $name; ?>"><?php echo htmlspecialchars( $value ); ?></textarea>

				<?php
				break;

			case 'checkbox':
			?>

		<input 
			class="checkbox" 
			name="<?php echo $name; ?>" 
			type="checkbox" 
			id="<?php echo $name; ?>"
			<?php if ($value) echo ' checked="checked"' ?> 
			/>
			
				<?php
				break;

			case 'select':
			?>
		<select id="<?php echo $name; ?>"
			name="<?php echo $name; ?>"
			class="full-width">
		<?php
		foreach ($this->options as $o => $n) {
		?>
		    <option value="<?php echo $o; ?>"<?php if ($value == $o) echo ' selected' ?>>
		    	<?php echo $n; ?>
		   	</option>
		<?php
		}
		?>
		</select>
				<?php
				break;
			default:
				?>
		<div>No option type chosen for <?php echo $name; ?> with value <?php echo htmlspecialchars($value); ?></div>
				<?php
				break;
		}
	}
}