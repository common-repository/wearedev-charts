var charts = top.document.getElementsByClassName('wearedev-chart');
var chartcount = charts.length;
for (var i = 0; i < chartcount; i++) {
  chartId = charts[i].getAttribute('data-chart-id');
  var chartObj = window['chart' + chartId];
  var ctx = document.getElementById('wearedev-chart-' + chartId);
  new Chart(ctx, {
    type: chartObj.chartType,
    data: chartObj.dataset[0]
  });
}
