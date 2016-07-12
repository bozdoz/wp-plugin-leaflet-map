// initialize the function declared in construct-leaflet-map.js in document head
if (window.addEventListener) {
  window.addEventListener('load', WPLeafletMapPlugin.init, false);
} else if (window.attachEvent)  {
  window.attachEvent('onload', WPLeafletMapPlugin.init);
}
