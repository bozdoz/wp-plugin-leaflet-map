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

<h1><?php echo esc_html($title); ?> <small>version: <?php echo esc_html($version); ?></small></h1>

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
            <p><?php esc_html_e('Sorry, your nonce did not verify', 'leaflet-map'); ?></p>
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
    <p><?php esc_html_e('Options Updated!', 'leaflet-map'); ?></p>
</div>
<?php
} elseif (isset($_POST['reset']) && verify_nonce()) {
    $settings->reset();
?>
<div class="notice notice-success is-dismissible">
    <p><?php esc_html_e('Options have been reset to default values!', 'leaflet-map'); ?></p>
</div>
<?php
} elseif (isset($_POST['clear-geocoder-cache']) && verify_nonce()) {
    include_once LEAFLET_MAP__PLUGIN_DIR . 'class.geocoder.php';
    Leaflet_Geocoder::remove_caches();
?>
<div class="notice notice-success is-dismissible">
    <p><?php esc_html_e('Location caches have been cleared!', 'leaflet-map'); ?></p>
</div>
<?php
}
/** END FORM SUBMISSION */

// TODO: maybe we should fetch unpkg for latest and load current and get L.version, and just inform

/** CHECK LEAFLET VERSION */
$db_js_url = $settings->get('js_url');
$unpkg_url = "https://unpkg.com/leaflet";
$is_unpkg_url = substr_compare($db_js_url, $unpkg_url, 0, strlen($unpkg_url)) === 0;

if ($is_unpkg_url && $db_js_url !== $settings->options[ 'js_url' ]->default) {
?>
    <div class="notice notice-info is-dismissible">
        <p><?php 
        esc_html_e('Info: your leaflet version may be out-of-sync with the latest default version: ', 'leaflet-map'); 
        echo Leaflet_Map::$leaflet_version;
        ?></p>
    </div>
<?php
}
/** END LEAFLET VERSION */
?>

<p><?php echo esc_html($description); ?></p>
<?php
/** FILTERS for helptext */
$allowed_helptext_tags = [
    'a'      => [ 'href' => [], 'title' => [], 'target' => [] ],
    'code' => [],
    'br'     => [],
    'p'     => [],
    'b'     => [],
];
?>

<h3><?php esc_html_e('Found an issue?', 'leaflet-map') ?></h3>
<p><?php esc_html_e('Post it to ', 'leaflet-map') ?><b><?php esc_html_e('WordPress Support', 'leaflet-map') ?></b>: <a href="https://wordpress.org/support/plugin/leaflet-map/" target="_blank">Leaflet Map (WordPress)</a></p>
<p><?php esc_html_e('Add an issue on ', 'leaflet-map') ?><b>GitHub</b>: <a href="https://github.com/bozdoz/wp-plugin-leaflet-map/issues" target="_blank">Leaflet Map (GitHub)</a></p>

<div class="wrap">
    <div class="wrap">
    <form method="post">
        <?php wp_nonce_field(NONCE_ACTION, NONCE_NAME); ?>
        <div class="container">
            <h2><?php esc_html_e('Settings', 'leaflet-map'); ?></h2>
            <hr>
        </div>
    <?php
    foreach ($settings->options as $name => $option) {
        if (!$option->type) continue;
    ?>
    <div class="container">
        <label>
            <span class="label"><?php echo esc_html($option->display_name); ?></span>
            <span class="input-group">
            <?php
            $option->widget($name, $settings->get($name));
            ?>
            </span>
        </label>

        <?php if (!property_exists($option, 'noreset') || $option->noreset != true) : ?>
        <div class="reset-option">
            <button type="button"
                class="button button-secondary reset-field-button"
                data-setting-name="<?php echo esc_attr($name); ?>"
            ><?php esc_html_e('Reset', 'leaflet-map'); ?></button>
            <span class="reset-feedback" aria-live="polite"></span>
        </div>
        <?php endif; ?>

        <?php
        if ($option->helptext) {
        ?>
        <div class="helptext">
            <p class="description"><?php 
                echo wp_kses( $option->helptext, $allowed_helptext_tags );
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
        <input type="submit" 
            name="submit" 
            id="submit" 
            class="button button-primary" 
            value="<?php esc_html_e('Save Changes', 'leaflet-map'); ?>">
        <input type="submit" 
            name="reset" 
            id="reset" 
            class="button button-secondary" 
            value="<?php esc_html_e('Reset to Defaults', 'leaflet-map'); ?>">
        <input type="submit" 
            name="clear-geocoder-cache" 
            id="clear-geocoder-cache" 
            class="button button-secondary" 
            value="<?php esc_html_e('Clear Geocoder Cache', 'leaflet-map'); ?>">
    </div>

    </form>

    <script type="text/javascript">
    (function () {
        var resetNonce = '<?php echo esc_js( wp_create_nonce('leaflet-map-reset-setting') ); ?>';

        function updateFieldValue(name, value) {
            var field = document.querySelector('[name="' + name + '"]');
            if (!field) {
                return;
            }

            if (field.type === 'checkbox') {
                field.checked = value == 1 || value === '1';
                return;
            }

            if (field.tagName.toLowerCase() === 'select' || field.tagName.toLowerCase() === 'textarea' || field.type === 'text' || field.type === 'number') {
                field.value = value;
            }
        }

        function showFeedback(button, message, isError) {
            var feedback = button.parentNode.querySelector('.reset-feedback');
            if (!feedback) {
                return;
            }
            feedback.textContent = message;
            feedback.style.color = isError ? '#a00' : '#080';
            setTimeout(function () {
                feedback.textContent = '';
            }, 4000);
        }

        document.addEventListener('click', function (event) {
            var button = event.target.closest('.reset-field-button');
            if (!button) {
                return;
            }

            event.preventDefault();
            var settingName = button.getAttribute('data-setting-name');
            if (!settingName) {
                return;
            }

            button.disabled = true;

            var data = new URLSearchParams();
            data.append('action', 'leaflet_map_reset_setting');
            data.append('security', resetNonce);
            data.append('setting', settingName);

            fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: data.toString()
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {
                if (!result.success) {
                    showFeedback(button, result.data && result.data.message ? result.data.message : 'Reset failed.', true);
                    return;
                }

                updateFieldValue(settingName, result.data.default);
                showFeedback(button, '<?php echo esc_js( __( 'Reset to default.', 'leaflet-map' ) ); ?>', false);
            })
            .catch(function () {
                showFeedback(button, '<?php echo esc_js( __( 'Request failed.', 'leaflet-map' ) ); ?>', true);
            })
            .finally(function () {
                button.disabled = false;
            });
        });
    })();
    </script>

    <div>
        <p><?php esc_html_e('Leaf icon provided by ', 'leaflet-map') ?><a href="https://fontawesome.com/" target="_blank">Font Awesome</a><?php esc_html_e( ', under their free license.', 'leaflet-map' ) ?></p>
    </div>

    </div>
</div>
