<div class="wrap">
	<h2>Shortcodes</h2>
	<div class="wrap">
		<?php
		echo do_shortcode('[leaflet-map zoom=2 zoomcontrol=1 height=300 scrollwheel=1]');
		echo do_shortcode('[leaflet-marker draggable=1 message="Drag me!" visible="true"]');
		?>
		<div class="wrap">
			<h2>Interactive Shortcodes:</h2>
			<p class="description">Move the map and the marker to generate shortcodes below:</p>
			<p><label class="h3" for="map-shortcode">Map Shortcode</label> <input type="text" id="map-shortcode" readonly="readonly" /></p>
			<p><label class="h3" for="marker-shortcode">Marker Shortcode</label> <input type="text" id="marker-shortcode" readonly="readonly" /></p>
			<h2>Examples:</h2>
			<?php
			$examples = array(
				"Standard" => array(
					'[leaflet-map zoom=12 lat=51.05 lng=-114.06]',
					),
				"Many Markers and icon!" => array(
					'[leaflet-map zoom=10 lat=43.65 lng=-79.385]',
					'[leaflet-marker]',
					'[leaflet-marker lat=43.68 lng=-79.275]',
					'[leaflet-marker lat=43.60 lng=-79.235 ico-icon-url="//i.imgur.com/Q54ueuO.png" ico-icon-size="array(80, 50)" ico-popup-anchor="array(0, -25)"]
',
					),
				"Draggable Marker" => array(
					'[leaflet-map zoom=8 lat=-33.85 lng=151.21 scrollwheel=1]',
					'[leaflet-marker draggable=1]',
					),
				"Zoom Buttons" => array(
					'[leaflet-map zoom=9 lat=48.855 lng=2.35 zoomcontrol=1]',
					),
				"Alternate Map Tiles" => array(
					'[leaflet-map zoom=3 lat=-25.165 lng=-57.832 tileurl=http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png subdomains=abc]',
					),
				"Heat Map" => array(
							'[leaflet-map zoom=3 lat=-25.165 lng=-57.832 heatmap=1 heatmap-radius=20 heatmap-max-opacity=0.5]',
					),
				"Marker Popup Messages" => array(
					'[leaflet-map lat=59.913 lng=10.739 zoom=12]',
					'[leaflet-marker message="OSLO!" visible="true"]',
					),
				"Links In Marker Messages" => array(
					'[leaflet-map lat=28.41 lng=-81.58 zoom=15]',
					'[leaflet-marker visible="true"] Disney World! <a href="https://disneyworld.disney.go.com">Link</a> [/leaflet-marker]',
					),
				"Basic Lines w/Scrollwheel Zoom" => array(
					'[leaflet-map lat=41 lng=29 scrollwheel=1 zoom=6]',
					'[leaflet-line latlngs="41, 29; 44, 18;"]'
					),
				"Fitted Colored Line on Addresses" => array(
					'[leaflet-map]',
					'[leaflet-line color="purple" addresses="Sayulita; Puerto Vallarta;" fitline=1]'
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