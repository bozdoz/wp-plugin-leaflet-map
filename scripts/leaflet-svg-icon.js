(function () {
  window.WPLeafletMapPlugin = window.WPLeafletMapPlugin || [];
  window.WPLeafletMapPlugin.unshift(initSVGIcon);

  function initSVGIcon() {
    L.SVGIcon = L.Icon.extend({
      options: {
        iconSize: [26, 42],
        popupAnchor: [1, -42],
        tooltipAnchor: [16, -28],
        iconClass: '',
        background: '#2b82cb',
        color: 'white',
      },
      createIcon: function (oldIcon) {
        var div =
          oldIcon && oldIcon.tagName === 'DIV'
            ? oldIcon
            : document.createElement('div');
        var options = this.options;
        var size = options.iconSize;
        var x = size[0];
        var y = size[1];

        function svgCreate(tag, attrs) {
          var el = document.createElementNS('http://www.w3.org/2000/svg', tag);
          for (var k in attrs) {
            el.setAttribute(k, attrs[k]);
          }
          return el;
        }

        var svg = svgCreate('svg', {
          viewBox: '0 0 365 560',
        });

        var path = svgCreate('path', {
          fill: this.options.background,
          d:
            'M182.9,551.7c0,0.1,0.2,0.3,0.2,0.3S358.3,' +
            '283,358.3,194.6c0-130.1-88.8-186.7-175.4-186.9' +
            'C96.3,7.9,7.5,64.5,7.5,194.6c0,88.4,175.3,357.4,' +
            '175.3,357.4S182.9,551.7,182.9,551.7z',
        });

        svg.appendChild(path);
        div.appendChild(svg);

        div.setAttribute(
          'style',
          'margin-left: ' +
            -x / 2 +
            'px;' +
            'margin-top: ' +
            -y +
            'px;' +
            'width: ' +
            x +
            'px;' +
            'height: ' +
            y +
            'px;'
        );

        // add icon
        var i = document.createElement('i');
        i.className = this.options.iconClass;
        i.setAttribute(
          'style',
          'position:absolute;' +
            'top: 20%;' +
            'left: 50%;' +
            'transform: translate3d(-50%, -20%, 0);' +
            'color: ' +
            this.options.color +
            ';' +
            'line-height: 0;' +
            'display: flex;' +
            'justify-content: center;' +
            'align-items: center;'
        );
        div.appendChild(i);

        return div;
      },
    });

    L.SVGMarker = L.Marker.extend({
      initialize: function (latlng, options) {
        options = options || {};
        var svg_options = L.extend({}, L.SVGIcon.prototype.options, options);
        options.icon = new L.SVGIcon(svg_options);
        L.Marker.prototype.initialize.call(this, latlng, options);
      },
    });
  }
})();
