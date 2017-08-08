<?php
$title = $plugin_data['Name'];
$description = $plugin_data['Description'];
$version = $plugin_data['Version'];

function option_label ($opt) {
    $opt = explode('_', $opt);
    
    foreach($opt as &$v) {
        $v = ucfirst($v);
    }
    echo implode(' ', $opt);
}
?>
<div class="wrap">
	<h1><?php echo $title; ?> <small>version: <?php echo $version; ?></small></h1>
	<p><?php echo $description; ?></p>
<?php
if (isset($_POST['submit'])) {
	/* copy and overwrite $post for checkboxes */
	$form = $_POST;

	foreach ($settings->options as $name => $option) {
		if (!$option->type) continue;

		/* checkboxes don't get sent if not checked */
		if ($option->type === 'checkbox') {
			$form[$name] = isset($_POST[ $name ]) ? 1 : 0;
		}

		$settings->set($name, stripslashes( $form[$name]));
	}
?>
<div class="notice notice-success is-dismissible">
	<p>Options Updated!</p>
</div>
<?php
} elseif (isset($_POST['reset'])) {
	$settings->reset();
?>
<div class="notice notice-success is-dismissible">
	<p>Options have been reset to default values!</p>
</div>
<?php
} elseif (isset($_POST['clear-geocoder-cache'])) {
	include_once(LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php');
	Leaflet_Geocoder::remove_caches();
?>
<div class="notice notice-success is-dismissible">
	<p>Location caches have been cleared!</p>
</div>
<?php
}
?>
<div class="wrap">
	<div class="wrap">
	<form method="post">
		<div class="container">
			<h2>Settings</h2>
			<hr>
		</div>
	<?php
	foreach ($settings->options as $name => $option) {
		if (!$option->type) continue;
	?>
	<div class="container">
		<label>
			<span class="label"><?php option_label($name); ?></span>
			<span class="input-group">
			<?php
			$option->widget($name, $settings->get($name));
			?>
			</span>
		</label>

		<?php
		if ($option->helptext) {
		?>
		<div class="helptext">
			<p class="description"><?php 
				echo $option->helptext; 
			?></p>
		</div>
		<?php
		}
		?>
	</div>
	<?php
	}
	?>
	<div class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		<input type="submit" name="reset" id="reset" class="button button-secondary" value="Reset to Defaults">
		<input type="submit" 
			name="clear-geocoder-cache" 
			id="clear-geocoder-cache" 
			class="button button-secondary" 
			value="Clear Geocoder Cache">
	</div>

	</form>
	</div>
</div>
