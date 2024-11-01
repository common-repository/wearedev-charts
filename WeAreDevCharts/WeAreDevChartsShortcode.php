<?php
/**
 * Class WeAreDevChartsShortcode
 * This will be add the custom post type for the Charts
 */

class WeAreDevChartsShortcode {

  public function __construct() {
    add_shortcode('wearedev_charts', [$this, 'WeAreDevDisplayChart']);
  }

  public function WeAreDevDisplayChart($atts) {
    $chartId = $atts['id'];
    ob_start();
    wp_enqueue_script('chart-js', plugins_url('assets/scripts/Chart.min.js', __DIR__), '', WeAreDevCharts::VERSION, true);
    wp_register_script('wearedev-chart', plugins_url('assets/scripts/wearedev-view.js', __DIR__), 'chart-js', WeAreDevCharts::VERSION, true);
    $chartArray = [
      'chartType' => get_post_meta($chartId, 'chart_type', true),
      'dataset' => get_post_meta($chartId, 'chart_dataset'),
      'id' => $chartId
    ];
    wp_localize_script('wearedev-chart', 'chart' . $chartId, $chartArray);
    wp_enqueue_script('wearedev-chart');
  ?>
    <figure id="chart_<?php echo $chartId; ?>" class="wp-caption">
      <canvas id="wearedev-chart-<?php echo $chartId; ?>" class="wearedev-chart" data-chart-id="<?php echo $chartId; ?>"></canvas>
      <figcaption class="wp-caption-text"><?php echo get_the_title($chartId); ?></figcaption>
    </figure>
  <?php
    return ob_get_clean();
  }

}

new WeAreDevChartsShortcode();
