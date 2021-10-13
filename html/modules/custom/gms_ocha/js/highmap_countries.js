jQuery(function() {

    // Initiate the chart
    var mapData = Highcharts.maps['custom/world'];
     var data_circle = [];
    jQuery.each(Drupal.settings.gms_ocha.data, function () {
        this.z = this.contribution;
        this.code = this.name;
        data_circle.push(this);
    });


       jQuery('#map-div').highcharts('Map', {
        title: {
            text: '<none>'
        },
        legend: {
            enabled: false
        },
        credits: {
            enabled: false
        },
        mapNavigation: {
            enabled: false,
            buttonOptions: {
                verticalAlign: 'top',
           },
            enableButtons:true,
            enableMouseWheelZoom: false,
        },

        xAxis: {
            minRange: 4000
        },
        tooltip: {
            borderWidth:1,
            borderRadius:5,
            enabled: false,
            positioner: function () {
                return { x: 650, y: 20 };
            },
            shadow:false,
            useHTML: true,
            formatter: function () {
                return '<div class="tooltip-close" onclick="javascript: close_tooltip(); function close_tooltip(){  }">X</div><div class="small-main"><small class="small-left">(In USD)</small><small class="small-right">' + this.point.name + '</small></div><div id="tooltip-div" style="height:85px; overflow:auto; width:255px"><table class="popup_table">' +
                    '<tr><th>Year</th><th>Contributions</th><th>Allocations</th>' +
                    '<th>#of <br />projects <br />approved</th></tr>' + this.point.data_table + '</table></div>';
            },
            backgroundColor: {
                linearGradient: [0, 0, 0, 0],
                stops: [
                    [0, '#FFFFFF'],
                    [1, '#ffffff']
                ]
            },
        },
        chart: {
            events: {
                load: function(){
                    this.mapZoom(0.6);
                    this.myTooltip = new Highcharts.Tooltip(this, this.options.tooltip);
                },
                click: function() {
                    this.myTooltip.hide(0);
                }
            }
        },
        plotOptions: {
            series: {
                stickyTracking: false,
                events: {
                    click: function(evt) {
                        this.chart.myTooltip.refresh(evt.point, evt);
                    },

                }

            }
        },
        series: [{
                name: 'Countries',
                mapData: mapData,
                color: '#E0E0E0',
                nullColor: '#ebebeb',
                enableMouseTracking: false,
            marker: {
                symbol: 'triangle'
            }
            }, {
                type: 'mappoint',

            dataLabels: {
                enabled: false
            },
                zThreshold: 62036,
                mapData: Highcharts.geojson(mapData),
                data: data_circle,
                maxSize: '14%',
                minSize: '5%',
            "marker": {
                "radius": 5,
                "symbol": 'url(' + Drupal.settings.basePath + 'sites/all/modules/custom/gms_ocha/pin.png)',
            },
            }]
    });



    jQuery('#map-div .highcharts-container').on('mouseenter',function(){
        mouse_scroll();
    });
    jQuery('#tooltip-div').on('mouseover',function(){
        console.log('enter');
        var chart = jQuery('#map-div').highcharts();
        jQuery('#map-div').unbind(document.onmousewheel === undefined ? 'DOMMouseScroll' : 'mousewheel');
    });
    jQuery('#tooltip-div').on('mouseout',function(){
        mouse_scroll();
    });

    jQuery('#map-div .highcharts-container').on('click',function(){
        mouse_scroll();
    });

    function mouse_scroll() {
        var chart = jQuery('#map-div').highcharts();
        jQuery('#map-div').bind(document.onmousewheel === undefined ? 'DOMMouseScroll' : 'mousewheel', function (e) {
            chart.pointer.onContainerMouseWheel(e);
            return false;
        });
    }



});
