(function ($,drupalSettings) {
  if (typeof jQuery != 'undefined') {
    // jQuery is loaded => print the version
    // alert(jQuery.fn.jquery);
  }
  /*
  window.onbeforeunload = function() {
    localStorage.removeItem("splash");
    return '';
  };
   */
   console.log('loaded--1', window.location.origin);
  Drupal.behaviors.pdflinks = {
    attach: function (context, settings) {
        console.log('loaded--2');
      const base = drupalSettings.path.baseUrl;
      // Call API to fetch the detail.
      /*
      $.getJSON(base + 'api/highstockchart?_format=json', function (data) {
        // Create the chart
        if (data) {
          Highcharts.stockChart('highChartContainer', {
            rangeSelector: {
              selected: 1
            },
            title: {
              text: 'AAPL Stock Price'
            },
            series: [{
              name: 'AAPL',
              data: data,
              type: 'areaspline'
            }]
          });
        }
      }); */
      // let loaded = false;
      // var once = $("#hiddenElement").val();
      // if(once){
      //
      // }

    }
  };
})(jQuery, drupalSettings);
