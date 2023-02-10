<?php
/**
 * Settings View
 * 
 * PHP Version 5.5
 * 
 * @category Admin
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */

$title = $plugin_data['Name'];
$description = __('A plugin for creating a Leaflet JS map with a shortcode. Boasts two free map tile services and three free geocoders.', 'leaflet-map');
$version = $plugin_data['Version'];
?>
<div class="wrap">

<h1><?php echo $title; ?> <small>version: <?php echo $version; ?></small></h1>

<?php
/** START FORM SUBMISSION */

// validate nonce!
define('NONCE_NAME', 'leaflet-map-nonce');
define('NONCE_ACTION', 'leaflet-map-action');

function verify_nonce () {
    $verified = (
        isset($_POST[NONCE_NAME]) &&
        check_admin_referer(NONCE_ACTION, NONCE_NAME)
    );

    if (!$verified) {
        // side-effects can be fun?
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Sorry, your nonce did not verify', 'leaflet-map'); ?></p>
        </div>
        <?php
    }

    return $verified;
}

if (isset($_POST['submit']) && verify_nonce()) {
    /* copy and overwrite $post for checkboxes */
    $form = $_POST;

    foreach ($settings->options as $name => $option) {
        if (!$option->type) continue;

        /* checkboxes don't get sent if not checked */
        if ($option->type === 'checkbox') {
            $form[$name] = isset($_POST[ $name ]) ? 1 : 0;
        }

        $value = trim( stripslashes( $form[$name]) );

        $settings->set($name, $value);
    }
?>
<div class="notice notice-success is-dismissible">
    <p><?php _e('Options Updated!', 'leaflet-map'); ?></p>
</div>
<?php
} elseif (isset($_POST['reset']) && verify_nonce()) {
    $settings->reset();
?>
<div class="notice notice-success is-dismissible">
    <p><?php _e('Options have been reset to default values!', 'leaflet-map'); ?></p>
</div>
<?php
} elseif (isset($_POST['clear-geocoder-cache']) && verify_nonce()) {
    include_once LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php';
    Leaflet_Geocoder::remove_caches();
?>
<div class="notice notice-success is-dismissible">
    <p><?php _e('Location caches have been cleared!', 'leaflet-map'); ?></p>
</div>
<?php
}
/** END FORM SUBMISSION */

/** CHECK LEAFLET VERSION */
$db_js_url = $settings->get('js_url');
$unpkg_url = "https://unpkg.com/leaflet";
$is_unpkg_url = substr_compare($db_js_url, $unpkg_url, 0, strlen($unpkg_url)) === 0;

if ($is_unpkg_url && $db_js_url !== $settings->options[ 'js_url' ]->default) {
?>
    <div class="notice notice-info is-dismissible">
        <p><?php 
        _e('Info: your leaflet version may be out-of-sync with the latest default version: ', 'leaflet-map'); 
        echo Leaflet_Map::$leaflet_version;
        ?></p>
    </div>
<?php
}
/** END LEAFLET VERSION */
?>

<p><?php echo $description; ?></p>
<h3><?php _e('Found an issue?', 'leaflet-map') ?></h3>
<p><?php _e('Post it to ', 'leaflet-map') ?><b><?php _e('WordPress Support', 'leaflet-map') ?></b>: <a href="https://wordpress.org/support/plugin/leaflet-map/" target="_blank">Leaflet Map (WordPress)</a></p>
<p><?php _e('Add an issue on ', 'leaflet-map') ?><b>GitHub</b>: <a href="https://github.com/bozdoz/wp-plugin-leaflet-map/issues" target="_blank">Leaflet Map (GitHub)</a></p>

<div class="wrap">
    <div class="wrap">
    <form method="post">
        <?php wp_nonce_field(NONCE_ACTION, NONCE_NAME); ?>
        <div class="container">
            <h2><?php _e('Settings', 'leaflet-map'); ?></h2>
            <hr>
        </div>
    <?php
    foreach ($settings->options as $name => $option) {
        if (!$option->type) continue;
    ?>
    <div class="container">
        <label>
            <span class="label"><?php echo $option->display_name; ?></span>
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
            <p class="description"><?php echo $option->helptext; ?></p>
        </div>
        <?php
        }
        ?>
    </div>
    <?php
    }
    ?>
    <div class="submit">
        <input type="submit" 
            name="submit" 
            id="submit" 
            class="button button-primary" 
            value="<?php _e('Save Changes', 'leaflet-map'); ?>">
        <input type="submit" 
            name="reset" 
            id="reset" 
            class="button button-secondary" 
            value="<?php _e('Reset to Defaults', 'leaflet-map'); ?>">
        <input type="submit" 
            name="clear-geocoder-cache" 
            id="clear-geocoder-cache" 
            class="button button-secondary" 
            value="<?php _e('Clear Geocoder Cache', 'leaflet-map'); ?>">
    </div>

    </form>

    <div>
        <p><?php _e('Leaf icon provided by ', 'leaflet-map') ?><a href="https://fontawesome.com/" target="_blank">Font Awesome</a><?php _e( ', under their free license.', 'leaflet-map' ) ?></p>
    </div>

    </div>
</div>
