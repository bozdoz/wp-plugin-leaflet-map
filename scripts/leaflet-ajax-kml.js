L.AjaxKML = L.AjaxGeoJSON.extend({
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
                    data = window.toGeoJSON.kml( xhr.responseXML );
                    _this.json = data;
                    _this.layer.addData( data );
                    _this.fire('ready');
                }
            };

            xhr.open('get', this._url, true);

            xhr.send();
        }
        this.fire('add');
    }
});

L.ajaxKML = function( url, options ) {
    return new L.AjaxKML( url, options );
};