<?php
$defaults = $this::$defaults;

if (isset($_POST['submit'])) {
	/* copy and overwrite $post for checkboxes */
	$form = $_POST;

	foreach ($defaults as $type=>$arrs) {
		foreach($arrs as $k=>$v) {
			/* checkboxes don't get sent if not checked */
			if ($type === 'checks') {
				$form[$k] = isset($_POST[$k]) ? 1 : 0;
			}
			update_option($k, $form[$k]);
		}
	}
?>
<div class="updated">
   <p>Options Updated!</p>
</div>
<?php
} elseif (isset($_POST['reset'])) {
	foreach ($defaults as $type=>$arrs) {
		foreach ($arrs as $k=>$v) {
			update_option($k, $v);
		}
	}
?>
<div class="updated">
   <p>Options have been reset to default values!</p>
</div>
<?php
}
?>

<div class="wrap">
	<h2>Leaflet Map Plugin</h2>
	<div class="wrap">
	<form method="post">
	<?php
	function option_label ($opt = 'leaflet_map_tile_url') {
	    $opt = explode('_', $opt);
	    array_shift($opt);
	    foreach($opt as &$v) {
	        $v = ucfirst($v);
	    }
	    echo implode(' ', $opt);
	}

	foreach ($defaults as $type=>$arrs) {
		foreach ($arrs as $k=>$v) {
	?>
	<div class="container">
		<label>
			<span class="label"><?php option_label($k); ?></span>
			<span class="input-group">
			<?php
			if ($type === 'text') {
			?>
				<input class="regular-text" name="<?php echo $k; ?>" type="text" id="<?php echo $k; ?>" value="<?php echo get_option($k, $v); ?>" />
			<?php
			} elseif ($type === 'checks') {
			?>
				<input class="checkbox" name="<?php echo $k; ?>" type="checkbox" id="<?php echo $k; ?>"<?php if (get_option($k, $v)) echo ' checked="checked"' ?> />
			<?php
			}
			?>
			</span>
		</label>
		<?php 
		if (array_key_exists($k, $this::$helptext)) {
		?>
		<div class="helptext">
		<p class="description"><?php echo $this::$helptext[$k]; ?></p>
		</div>
		<?php
		}
		?>
	</div>
	<?php
		}
	}
	?>

	<div class="container">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		<input type="submit" name="reset" id="reset" class="button button-secondary" value="Reset to Defaults">
	</div>

	</form>
	</div>
</div>