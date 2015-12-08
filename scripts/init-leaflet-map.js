// initialize the function declared in construct-leaflet-map.js in document head
(function(){
    var onLoad = function(){
        WPLeafletMapPlugin.init();
    };

    if(window.addEventListener){
        window.addEventListener('load', onLoad)
    }else{
        window.attachEvent('onload', onLoad)
    }
})();
