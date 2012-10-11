/**
 *------------------------------------------------------------------------------
 * Main Javascript
 *------------------------------------------------------------------------------
 *
 */



// Sample date
var data = [
            {
              name: 'Water In',
              data: [49.44,49.44,49.55,49.55,49.55,49.55,49.44]
            },
            {
              name: 'Water out',
              data: [43.03,43.03,43.03,43.03,43.03,43.03,42.8]
            },
            {
              name: 'Air In',
              data: [64.85,65.07,64.74,64.85,64.96,64.96,64.85]
            },
            {
              name: 'Air Out',
              data: [91.06,91.18,91.06,91.18,91.18,91.29,91.51]
            },
            {
              name: 'Outside',
              data: [66.65,67.21,68.45,68.22,68.56,69.01,69.57]
            },
            {
              name: 'In 1',
              data: [1,1,1,1,1,1,1]
            },
            {
              name: 'In 2',
              data: [0,0,0,0,0,0,0]
            },
            {
              name: 'In 3',
              data: [0,0,0,0,1,1,1]
            },
            {
              name: 'In 4',
              data: [1,1,1,1,1,1,1]
            },
            {
              name: 'In 5',
              data: [0,0,0,0,0,0,0]
            },
            {
              name: 'In 6',
              data: [0,0,0,0,0,0,0]
            },
            {
              name: 'In 7',
              data: [0,0,0,0,0,0,0]
            },
            {
              name: 'In 8',
              data: [1,1,1,1,1,1,1]
            },
            {
              name: 'Flow 1 GPM',
              data: [5.62,5.65,5.47,5.65,7.07,7.58,7.58]
            },
            {
              name: 'Pressure PSI',
              data: [37.5,37.5,37.56,37.5,13.03,12.77,12.74]
            }
          ]; // End series


// sample chart
$(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'chart',
                type: 'line'
            },
            // legend: {align: 'right',
            //          borderRadius: 3,
            //          layout: 'vertical',
            //          verticalAlign: 'middle'
            //        },
            // loading: {hideDuration: 0},
            // subtitle: {text: 'From RFP'},
            // title: {text: 'Sample Data'},
            // tooltip: {enabled: true,
            //           animate: false
            // },
            plotOptions: {
                line: {
                    allowPointSelect: false,
                    dataLabels: {
                        enabled: false
                    },
                    enableMouseTracking: true
                    },
                    lineWidth: 1,
                    series: {
                      point: {
                        events: {
                          click: function(){console.log(this);}
                        }
                      }
                    },
                    shadow: false
            },
            xAxis: {
                categories: ['13:48', '13:48','13:48','13:48','13:48','13:49','13:49']
            },
            yAxis: {
              title: {text: 'Temperature (°F)'}
            },
            series: data
        }); // End Highcharts.Chart
    }); // End $.ready()

});


/**
 * Highcharts tracker now don't prevent default behavior (like scrolling on touch devices).
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
  }

  container.onmousemove = container.ontouchstart = container.ontouchmove = mouseMove;
});
