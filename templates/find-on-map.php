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
		echo do_shortcode('[leaflet-map zoom=2 zoomcontrol=1 doubleClickZoom=1 height=300 scrollwheel=1]');
		echo do_shortcode('[leaflet-marker draggable=1 message="Drag me!" visible="true"]');
		?>
		<div class="wrap">
			<hr>
			<h2>Interactive Shortcodes:</h2>
			<p class="description">Move the map and the marker to generate shortcodes below:</p>
			<p><label class="h3" for="map-shortcode">Map Shortcode</label> <input type="text" id="map-shortcode" readonly="readonly" /></p>
			<p><label class="h3" for="marker-shortcode">Marker Shortcode</label> <input type="text" id="marker-shortcode" readonly="readonly" /></p>
			<hr>
			<h2>Examples:</h2>
			<?php
			$examples = array(
				"Standard" => array(
					'[leaflet-map zoom=12 lat=51.05 lng=-114.06]',
					),
				"Many Markers!" => array(
					'[leaflet-map zoom=10 lat=43.65 lng=-79.385]',
					'[leaflet-marker]',
					'[leaflet-marker lat=43.68 lng=-79.275]',
					'[leaflet-marker lat=43.67 lng=-79.4]',
					),
				"Draggable Marker" => array(
					'[leaflet-map zoom=8 lat=-33.85 lng=151.21 scrollwheel=1]',
					'[leaflet-marker draggable=1]',
					),
				"Marker Icon" => array(
					'[leaflet-map zoom=10 address="cochrane, Ontario" scrollwheel=1]',
					'[leaflet-marker iconUrl="http://i.imgur.com/Q54ueuO.png" iconSize="80,50" iconAnchor="40,60"]'
					),
				"Zoom Buttons" => array(
					'[leaflet-map zoom=9 lat=48.855 lng=2.35 zoomcontrol=1]',
					),
				"Alternate Map Tiles w/scrollwheel" => array(
					'[leaflet-map zoom=2 scrollwheel=1 lat=-2.507 lng=32.902 tileurl=http://{s}.tile.stamen.com/watercolor/{z}/{x}/{y}.jpg subdomains=abcd]',
					),
				"Marker Popup Messages (on click)" => array(
					'[leaflet-map lat=59.913 lng=10.739 zoom=12]',
					'[leaflet-marker]OSLO![/leaflet-marker]',
					),
				"Links In Marker Messages (visible)" => array(
					'[leaflet-map lat=28.41 lng=-81.58 zoom=15]',
					'[leaflet-marker visible="true"] Disney World! <a href="https://disneyworld.disney.go.com">Link</a> [/leaflet-marker]',
					),
				"Basic Lines w/Scrollwheel" => array(
					'[leaflet-map lat=41 lng=29 scrollwheel=1 zoom=6]',
					'[leaflet-line latlngs="41, 29; 44, 18;"]'
					),
				"Fitted Colored Line on Addresses" => array(
					'[leaflet-map]',
					'[leaflet-line color="purple" addresses="Sayulita; Puerto Vallarta;" fitline=1]'
					),
				"More Crazy Line Attributes" => array(
					'[leaflet-map]',
					'[leaflet-line color="red" weight=10 dasharray="2,15" addresses="Halifax, NS; Tanzania" classname=marching-ants fitbounds=1]CSS makes me march![/leaflet-line]'
					),
				"Disable all Interaction" => array(
					'[leaflet-map address="las vegas" boxZoom=false doubleClickZoom=false dragging=false keyboard=false scrollwheel=0]',
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