#!/bin/sh
set -euo pipefail

if [[ -f wp-config.php && -f .cli-initialized ]]; then
    exit 0
fi

if [ ! -f wp-config.php ]; then
    echo "WordPress not found in $PWD!"
    ( set -x; ls -A; sleep 15 )
fi

if [ ! -f .cli-initialized ]; then
    echo "Initializing WordPress install!"

    function _wp {
        wp --allow-root "$@"
    }

    _wp core install \
        --url="localhost:$WORDPRESS_PORT" \
        --title="Test Leaflet Map" \
        --admin_user=admin \
        --admin_password=password \
        --admin_email=not@real.com \
        --skip-email

    _wp config set WP_DEBUG true --raw

    _wp core update

    _wp theme activate twentynineteen

    _wp plugin activate leaflet-map

    _wp post create \
        --post_type=post \
        --post_title='Test Map' \
        --post_content='[leaflet-map] [leaflet-marker]' \
        --post_status='publish'

    _wp rewrite structure /%postname%/

    touch .cli-initialized

    chown -R xfs:xfs .
fi