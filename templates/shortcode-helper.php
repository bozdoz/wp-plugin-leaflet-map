<?php
/**
 * Shortcode Helper Page
 * 
 * PHP Version 5.5
 * 
 * @category Admin
 * @author   Benjamin J DeLong <ben@bozdoz.com>
 */
?>
<style>
    .marching-ants {
      animation: dash 35s infinite linear;
    }

    @keyframes dash {
      to {
        stroke-dashoffset: -1000;
      }
    }
</style>

<div class="wrap">
    <h2>Shortcode Helper</h2>
    <div class="wrap">
        <?php
        $drag = __('Drag Me', 'leaflet-map');

        echo do_shortcode('[leaflet-map zoom=2 zoomcontrol doubleClickZoom height=300 scrollwheel]');
        echo do_shortcode(sprintf('[leaflet-marker draggable visible] %s [/leaflet-marker]',
            $drag
        ));
        ?>
        <div class="wrap">
            <hr>
            <h2><?php _e('Interactive Shortcodes:', 'leaflet-map'); ?></h2>
            <p class="description"><?php _e('Move the map and the marker to generate shortcodes below:', 'leaflet-map'); ?></p>
            <div class="flex"><label class="h3" for="map-shortcode"><?php _e('Map Shortcode', 'leaflet-map'); ?></label> <input type="text" id="map-shortcode" readonly="readonly" /></div>
            <div class="flex"><label class="h3" for="marker-shortcode"><?php _e('Marker Shortcode', 'leaflet-map'); ?></label> <input type="text" id="marker-shortcode" readonly="readonly" /></div>
            <hr>
            <h2><?php _e('Examples', 'leaflet-map'); ?>:</h2>
            <div class="examples">
            <?php
            $examples = array(
                __("Standard", 'leaflet-map') => array(
                    '[leaflet-map zoom=12 lat=51.05 lng=-114.06]',
                    ),
                __("Many Markers!", 'leaflet-map') => array(
                    '[leaflet-map zoom=10 lat=43.65 lng=-79.385]',
                    '[leaflet-marker]',
                    '[leaflet-marker lat=43.68 lng=-79.275]',
                    '[leaflet-marker lat=43.67 lng=-79.4]',
                    ),
                __("Draggable Marker", 'leaflet-map') => array(
                    '[leaflet-map zoom=8 lat=-33.85 lng=151.21 scrollwheel]',
                    '[leaflet-marker draggable]',
                    ),
                __("Marker Icon", 'leaflet-map') => array(
                    '[leaflet-map zoom=14 address="Ha Ling, canmore" scrollwheel !detect-retina show_scale]',
                    '[leaflet-marker iconUrl="https://i.imgur.com/Q54ueuO.png" iconSize="80,50" iconAnchor="40,60"]'
                    ),
                __("SVG Marker Icon", 'leaflet-map') => array(
                    '[leaflet-map address="twilight lane, nova scotia" scrollwheel]',
                    '[leaflet-marker svg background="#777" iconClass="dashicons dashicons-star-filled" color="gold"]My Favorite Place in the World[/leaflet-marker]'
                    ),
                __("Zoom Buttons", 'leaflet-map') => array(
                    '[leaflet-map zoom=10 lat=48.855 lng=2.35 zoomcontrol !detect-retina]',
                    ),
                __("Alternate Map Tiles w/scrollwheel", 'leaflet-map') => array(
                    '[leaflet-map zoom=2 scrollwheel lat=-2.507 lng=32.902 tileurl=https://stamen-tiles-{s}.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.jpg subdomains=abcd attribution="Map tiles by Stamen Design, under CC BY 3.0."]',
                    ),
                __("Marker Popup Messages (on click)", 'leaflet-map') => array(
                    '[leaflet-map lat=59.913 lng=10.739 zoom=12]',
                    '[leaflet-marker]OSLO![/leaflet-marker]',
                    ),
                __("Links In Marker Messages (visible)", 'leaflet-map') => array(
                    '[leaflet-map lat=28.41 lng=-81.58 zoom=15 detect-retina]',
                    '[leaflet-marker visible] Disney World! <a href="https://disneyworld.disney.go.com">Link</a> [/leaflet-marker]',
                    ),
                __("Basic Lines w/Scrollwheel", 'leaflet-map') => array(
                    '[leaflet-map lat=41 lng=29 scrollwheel zoom=6]',
                    '[leaflet-line latlngs="41, 29; 44, 18;"]'
                    ),
                __("Basic Polygon", 'leaflet-map') => array(
                    '[leaflet-map fitbounds]',
                    '[leaflet-polygon addresses="Miami; San Juan; Bermuda" color="green" fillColor="yellow"]<a href="https://en.wikipedia.org/wiki/Bermuda_Triangle" target="_blank">Bermuda Triangle</a>[/leaflet-polygon]'
                    ),
                __("Basic Circle", 'leaflet-map') => array(
                    '[leaflet-map lat=52 lng=5 zoom=8.2 zoomcontrol !show_scale]',
                    '[leaflet-circle lat=52 lng=5 radius=17500]',
                    '[leaflet-scale position=topright]'
                    ),
                __("Fitted Colored Line on Addresses", 'leaflet-map') => array(
                    '[leaflet-map fitbounds]',
                    '[leaflet-line color="purple" addresses="Sayulita; Puerto Vallarta;"]'
                    ),
                __("More Crazy Line Attributes", 'leaflet-map') => array(
                    '[leaflet-map fitbounds]',
                    '[leaflet-line color="red" weight=10 dasharray="2,15" addresses="Halifax, Nova Scotia; Tanzania" classname=marching-ants]'
                    ),
                __("Disable all Interaction", 'leaflet-map') => array(
                    '[leaflet-map address="las vegas" !boxZoom !doubleClickZoom !dragging !keyboard !scrollwheel !attribution !touchZoom !show_scale]',
                    ),
                __("Add GeoJSON by URL", 'leaflet-map') => array(
                    '[leaflet-map fitbounds scrollwheel]',
                    '[leaflet-geojson src=https://gist.githubusercontent.com/bozdoz/064a7101b95a324e8852fe9381ab9a18/raw/ee100561f5a0a8cf55430e9f2157e4a1e2560a2e/map.geojson]'
                    ),
                __("Add GeoJSON with circle markers and popups", 'leaflet-map') => array(
                    '[leaflet-map fitbounds scrollwheel]',
                    '[leaflet-geojson circleMarker radius=10 src=https://gist.githubusercontent.com/bozdoz/064a7101b95a324e8852fe9381ab9a18/raw/ee100561f5a0a8cf55430e9f2157e4a1e2560a2e/map.geojson]{popup-text}[/leaflet-geojson]'
                    ),
                __("Add KML by URL", 'leaflet-map') => array(
                    '[leaflet-map fitbounds]',
                    '[leaflet-kml src=https://cdn.jsdelivr.net/gh/mapbox/togeojson@master/test/data/polygon.kml fillColor=red color=white]'
                    ),
                __("Add GPX by URL", 'leaflet-map') => array(
                    '[leaflet-map fitbounds]',
                    '[leaflet-gpx src=https://cdn.jsdelivr.net/gh/mapbox/togeojson@master/test/data/run.gpx color=black]'
                    ),
                __("Keep them in Newfoundland", 'leaflet-map') => array(
                    '[leaflet-map maxbounds="46.22545288226939, -59.61181640625;51.72702815704774, -52.36083984375" zoom=5 zoomcontrol]',
                ),
                __("Image Map", 'leaflet-map') => array(
                    '[leaflet-image zoom=1 zoomcontrol scrollwheel !attribution]',
                    '[leaflet-marker draggable]'
                    ),
                );

            foreach ($examples as $title => $collection) {
                echo '<div class="list-item">';
                echo "<h3>$title</h3>";
                foreach ($collection as $shortcode) {
                    echo do_shortcode($shortcode);
                    echo "<p>$shortcode</p>";
                }
                echo '</div>';
            }
            ?>    
            </div>
        </div>
    </div>
</div>
