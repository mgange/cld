/**
 *------------------------------------------------------------------------------
 * Main Javascript
 *------------------------------------------------------------------------------
 *
 */



/**
 * Ask the user if they're sure when they click a .confirm element
 */
$('.confirm').click(function(){
  return confirm('Are you sure you want to continue?');
});

function loadStatus(id) {
  window.location='../status?id='+id;
}

function stripTrailingSlash(str) {
  if(str.substr(-1) == '/') {
    return str.substr(0, str.length - 1);
  }
  return str;
}

// Remove no-js warning
if($('.no-js-alert').length) {
  $('.no-js-alert').remove();
}

// Menu Handler
$('.nav li a').each(function(){
  if(stripTrailingSlash(window.location.href) == stripTrailingSlash($(this).attr('href'))) {
    $(this).parent().addClass('active');
  }
});

// Date Picker
$('.datepick').datepick({
  dateFormat: 'yyyy-mm-dd',
  showSpeed: 1
});
// Time Picker
$('.timepick').timepicker({
  scrollDefaultNow: true,
  step: 10,
  timeFormat: 'h:i A'
});

// Submit a form when a select option is chosen
$('.selectSubmit').change(function(){
    this.form.submit();
});

// Auto Refresh
if($('.refresh').length) {
  window.setTimeout("window.location=window.location", 300000);
}

// Initiate Chart
/**
 * I'm using putting a lot of the chart data/options into variables an then
 * using those variables to fill options when the chart is called here. So when
 * you see something like
 *   (typeof variable != 'undefined')?variable:[]
 * I'm checking if the current page has data to put in, and if it doesn't I'll
 * fill in a default value. i.e. []
 */
if($('.chart-container').length) {
  $(function () {
      var chart;
      $(document).ready(function() {
          chart = new Highcharts.Chart({
              chart: {
                  renderTo: (typeof renderTo != 'undefined')?renderTo:'chart',
                  type: (typeof chartType != 'undefined')?chartType:'line',
                  zoomType: (typeof zoomType != 'undefined')?zoomType:'x'
              },
              legend: (typeof legend != 'undefined')?legend:{},
              loading: {hideDuration: 0},
              subtitle: {text: ''},
              title: {text: ''},
              tooltip: (typeof tooltip != 'undefined')?tooltip:{},
              plotOptions: (typeof plotOptions != 'undefined')?plotOptions:{},
              xAxis: {
                  categories: (typeof categories != 'undefined')?categories:[],
                  labels: {
                    step: Math.floor(data[0].data.length/Math.floor($(document).width()/180))-1
                  },
                  plotBands: (typeof xPlotBands != 'undefined')?xPlotBands:[]
              },
              yAxis: (typeof yAxisData != 'undefined')?yAxisData:{},
              series: data
          }); // End Highcharts.Chart
      }); // End $.ready()

  });
}

if(Modernizr.touch) {
  /**
   * Highcharts tracker now doesn't prevent default behavior (like scrolling on touch devices).
   * https://gist.github.com/2983403
   */
  Highcharts.Chart.prototype.callbacks.push(function(chart) {
    var hasTouch = document.documentElement.ontouchstart !== undefined,
        mouseTracker = chart.tracker,
        container = chart.container,
        mouseMove;

    mouseMove = function (e) {
      // let the system handle multitouch operations like two finger scroll
      // and pinching
      if (e && e.touches && e.touches.length > 1) {
        return;
      }

      // normalize
      e = mouseTracker.normalizeMouseEvent(e);
      if (!hasTouch) { // not for touch devices
        e.returnValue = false;
      }

      var chartX = e.chartX,
        chartY = e.chartY,
        isOutsidePlot = !chart.isInsidePlot(chartX - chart.plotLeft, chartY - chart.plotTop);

      // cancel on mouse outside
      if (isOutsidePlot) {

        /*if (!lastWasOutsidePlot) {
          // reset the tracker
          resetTracker();
        }*/

        // drop the selection if any and reset mouseIsDown and hasDragged
        //drop();
        if (chartX < chart.plotLeft) {
          chartX = chart.plotLeft;
        } else if (chartX > chart.plotLeft + chart.plotWidth) {
          chartX = chart.plotLeft + chart.plotWidth;
        }

        if (chartY < chart.plotTop) {
          chartY = chart.plotTop;
        } else if (chartY > chart.plotTop + chart.plotHeight) {
          chartY = chart.plotTop + chart.plotHeight;
        }
      }

      if (chart.mouseIsDown && e.type !== 'touchstart') { // make selection

        // determine if the mouse has moved more than 10px
        hasDragged = Math.sqrt(
          Math.pow(mouseTracker.mouseDownX - chartX, 2) +
          Math.pow(mouseTracker.mouseDownY - chartY, 2)
        );
        if (hasDragged > 10) {
          var clickedInside = chart.isInsidePlot(mouseTracker.mouseDownX - chart.plotLeft, mouseTracker.mouseDownY - chart.plotTop);

          // make a selection
          if (chart.hasCartesianSeries && (mouseTracker.zoomX || mouseTracker.zoomY) && clickedInside) {
            if (!mouseTracker.selectionMarker) {
              mouseTracker.selectionMarker = chart.renderer.rect(
                chart.plotLeft,
                chart.plotTop,
                zoomHor ? 1 : chart.plotWidth,
                zoomVert ? 1 : chart.plotHeight,
                0
              )
              .attr({
                fill: mouseTracker.options.chart.selectionMarkerFill || 'rgba(69,114,167,0.25)',
                zIndex: 7
              })
              .add();
            }
          }

          // adjust the width of the selection marker
          if (mouseTracker.selectionMarker && zoomHor) {
            var xSize = chartX - mouseTracker.mouseDownX;
            mouseTracker.selectionMarker.attr({
              width: mathAbs(xSize),
              x: (xSize > 0 ? 0 : xSize) + mouseTracker.mouseDownX
            });
          }
          // adjust the height of the selection marker
          if (mouseTracker.selectionMarker && zoomVert) {
            var ySize = chartY - mouseTracker.mouseDownY;
            mouseTracker.selectionMarker.attr({
              height: mathAbs(ySize),
              y: (ySize > 0 ? 0 : ySize) + mouseTracker.mouseDownY
            });
          }

          // panning
          if (clickedInside && !mouseTracker.selectionMarker && mouseTracker.options.chart.panning) {
            chart.pan(chartX);
          }
        }

      } else if (!isOutsidePlot) {
        // show the tooltip
        mouseTracker.onmousemove(e);
      }

      lastWasOutsidePlot = isOutsidePlot;
    };

    container.onmousemove = container.ontouchstart = container.ontouchmove = mouseMove;
  });
}
