L.AjaxGeoJSON = L.GeoJSON.extend({
    options : {},

    initialize : function (url, options) {
        L.setOptions(this, options);
        this._url = url;
        this.layer = L.geoJson(null, this.options);
    },

    onAdd : function (map) {
        var _this = this,
            xhr;

        this.map = map;

        map.addLayer( this.layer );

        if (!this.request) {
            this.request = xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function () {
                var data;
                if (xhr.readyState === xhr.DONE &&
                    xhr.status === 200) {
                    data = xhr.response;
                    _this.json = data;
                    _this.layer.addData( data );
                    _this.fire('ready');
                }
            };

            xhr.responseType = 'json';

            xhr.open('get', this._url, true);

            xhr.send();
        }
        this.fire('add');
    },

    eachLayer : function (fnc) {
        this.layer.eachLayer( fnc );
    },

    setStyle : function (style) {
        
        this.layer.setStyle( style );
    },

    resetStyle : function (layer) {
        this.layer.resetStyle( layer );
    },

    onRemove : function (map) {
        this.map.removeLayer( this.layer );        
    }
});

L.ajaxGeoJson = function( url, options ) {
    return new L.AjaxGeoJSON( url, options );
};