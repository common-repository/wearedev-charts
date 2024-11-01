<?php
if (!class_exists('WeAreDevCharts')) {
  /**
   * Class WeAreDevCharts
   */
  class WeAreDevCharts {

    const VERSION = '1.0.1';

    public function __construct() {
      require_once __DIR__ . '/WeAreDevChartsPostType.php';
      require_once __DIR__ . '/WeAreDevChartsMediaModal.php';
      require_once __DIR__ . '/WeAreDevChartsShortcode.php';
      require_once __DIR__ . '/WeAreDevChartsAddOns.php';
    }

    public function WeAreDevGetAvailableChartsAddOn() {
      $availableCharts[] = 'doughnut';

      if (class_exists('WeAreDevChartsPieChartAddOn')) {
        $availableCharts[] = 'pie';
      }
      if (class_exists('WeAreDevChartsRadarChartAddOn')) {
        $availableCharts[] = 'radar';
      }
      if (class_exists('WeAreDevChartsLineChartAddOn')) {
        $availableCharts[] = 'line';
      }
      if (class_exists('WeAreDevChartsBarChartAddOn')) {
        $availableCharts[] = 'bar';
      }
      if (class_exists('WeAreDevChartsPolarAreaChartAddOn')) {
        $availableCharts[] = 'polar-area';
      }
      if (class_exists('WeAreDevChartsAreaChartAddOn')) {
        $availableCharts[] = 'bubble';
      }
      if (class_exists('WeAreDevChartsScatterChartAddOn')) {
        $availableCharts[] = 'scatter';
      }
      return $availableCharts;
    }
  }
}
new WeAreDevCharts();
