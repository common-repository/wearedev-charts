<?php
/**
 * Charts on WeAreDevChartsMediaModal
 * Add button and frame to the media modal
 */

class WeAreDevChartsMediaModal {

  public function __construct() {
    if (is_admin()) {
      add_filter('media_upload_tabs', [$this, 'WeAreDevChartsMediaUploadTab']);
      add_action('media_upload_charts', [$this, 'WeAreDevChartsAddMediaUploadTabForm']);
      add_action('admin_head', [$this, 'WeAreDevChartsAdminHead']);
      add_action('wp_ajax_get_all_charts', [$this, 'WeAreDevGetAllCharts']);
      add_action('wp_ajax_get_chart_data', [$this, 'WeAreDevGetChartData']);
    }
  }

  /**
   * Custom charts tab to media modal
   * This function will be add an extra tab 'Charts' to the media modal
   * @param $tabs
   * @return mixed
   */
  public function WeAreDevChartsMediaUploadTab($tabs) {
    $tabs['charts'] = __('Charts', 'wearedev-charts');

    return $tabs;
  }

  /**
   * Call the custom media frame for 'Charts'
   * Load the custom media-frame in the wp_iframe function
   */
  public function WeAreDevChartsAddMediaUploadTabForm() {
    wp_enqueue_style('vue-charts', plugins_url('assets/styles/main.css', __DIR__), '', WeAreDevCharts::VERSION);
    wp_enqueue_script('vue-manifest', plugins_url('assets/scripts/manifest.js', __DIR__), '', WeAreDevCharts::VERSION, true);
    wp_enqueue_script('vue-vendor', plugins_url('assets/scripts/vendor.js', __DIR__), '', WeAreDevCharts::VERSION, true);
    wp_register_script('vue-charts', plugins_url('assets/scripts/app.js', __DIR__), '', WeAreDevCharts::VERSION, true);
    // Localize the script with store data
    $chartArray = [
      'ajaxUrl' => admin_url('admin-ajax.php')
    ];
    wp_localize_script('vue-charts', 'chartOverview', $chartArray);
    // Localize the script with store data
    if (get_post_meta($post->ID, 'chart_data')) {
      $storeArray = [
        'id' => $post->ID,
        'selectedChart' => get_post_meta($post->ID, 'chart_sort', true),
        'data' => get_post_meta($post->ID, 'chart_data', true),
        'dataset' => get_post_meta($post->ID, 'chart_dataset', true),
      ];
    } else {
      $storeArray = [
        'id' => '',
        'availableCharts' => WeAreDevCharts::WeAreDevGetAvailableChartsAddOn(),
        'selectedChart' => ''
      ];
    }
    wp_localize_script('vue-charts', 'store', $storeArray);
    wp_enqueue_script('vue-charts');

    wp_iframe([$this, 'WeAreDevChartsMediaUploadTabForm']);
  }

  /**
   * Charts overview content for media frame
   * The content of the frame will be visible when you select Charts in the media modal
   */
  public function WeAreDevChartsMediaUploadTabForm() {
    if (isset($_POST['action']) && $_POST['action'] === 'insert_chart') {
      $chart_id = $this->WeAreDevChartsMediaUploadTabSaveForm($_POST);
      $shortcode = '[wearedev_charts id="' . $chart_id . '" type="' . $_POST['chartSort'] . '"]';
      media_send_to_editor($shortcode);
    }
    ?>
    <form action="" method="post" class="chart-modal">
      <div id="wearedev-charts">
        <div id="app"></div>
        <?php do_action('wearedev_chart_media_modal'); ?>
      </div>
      <div class="media-frame-toolbar">
        <div class="media-toolbar">
          <div class="media-toolbar-secondary"></div>
          <div class="media-toolbar-primary search-form">
            <?php wp_nonce_field('wearedev_add_chart', 'wearedev_add_chart_nonce'); ?>
            <input type="hidden" name="action" value="insert_chart">
            <input type="submit" class="button media-button button-primary button-large media-button-select" value="<?php echo __('Insert into page', 'wp-charts'); ?>">
          </div>
        </div>
      </div>
    </form>
    <?php
  }

  /**
   * Save the new chart in the custom post type 'Charts'
   * $chart_data is the post_data from the chart.
   * @param $chart_data
   * @return mixed
   */
  public function WeAreDevChartsMediaUploadTabSaveForm($chartData) {
    if (!isset($chartData['wearedev_add_chart_nonce']) || !wp_verify_nonce($chartData['wearedev_add_chart_nonce'], 'wearedev_add_chart')) {
      return;
    }

    $json = $chartData['dataset'];
    $json_string = stripslashes($json);
    $dataset = json_decode($json_string, true);
//    $data = array_map(sanitize_text_field($item), $chartData['data']);
    $data = $this->WeAreDevArrayMap('trim', $chartData['data']);

    if ($chartData['chartId'] === 'add_chart') {
      $newChart = [
        'post_title' => wp_strip_all_tags($chartData['title']),
        'post_status' => 'publish',
        'post_type' => 'chart'
      ];
      $chartId = wp_insert_post($newChart);
      add_post_meta($chartId, 'chart_type', sanitize_title($_POST['chartSort']));
      add_post_meta($chartId, 'chart_data', $data);
      add_post_meta($chartId, 'chart_dataset', $dataset);
    } else {
      $chartId = $chartData['chartId'];
      update_post_meta($chartId, 'chart_data', $data);
      update_post_meta($chartId, 'chart_dataset', $dataset);
    }

    return $chartId;
  }

  public function WeAreDevChartsAdminHead() {
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
      return;
    }
    ?>
    <script type="text/javascript">
      var wpChartsPluginUrl = '<?php echo plugins_url('', __DIR__); ?>';
    </script>
    <?php
    if (get_user_option('rich_editing') === 'true') {
      add_filter('mce_external_plugins', [$this ,'WeAreDevMceChartsPlugin']);
    }
  }

  /**
   * WeAreDevMceChartsPlugin
   * Adds our tinymce plugin
   * @param  array $plugin_array
   * @return array
   */
  public function WeAreDevMceChartsPlugin($plugin_array) {
    $plugin_array['wearedev_charts'] = plugins_url('assets/scripts/wearedev-shortcode.js', __DIR__);

    return $plugin_array;
  }

  public function WeAreDevGetAllCharts() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
      die();
    }

    $charts = new WP_Query(['post_type' => 'chart', 'showposts' => -1]);
    $allCharts = [];

    foreach($charts->posts as $chart) {
      $chart_new = [];
      $chart_new['id'] = $chart->ID;
      $chart_new['chart_sort'] = get_post_meta($chart->ID, 'chart_type', true);
      $chart_new['title'] = $chart->post_title;
      $allCharts[] = $chart_new;
    }

    wp_send_json($allCharts);
    die();
  }

  /**
   *
   */
  public function WeAreDevGetChartData() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
      die();
    }

    $chart_id = is_int($_POST['id']);
    // Localize the script with new data
    $chartArray = [
      'chart_sort' => get_post_meta($chart_id, 'chart_type', true),
      'data' => get_post_meta($chart_id, 'chart_data', true),
      'dataset' => get_post_meta($chart_id, 'chart_dataset', true),
      'title' => get_the_title($chart_id),
      'id' => $chart_id
    ];

    wp_send_json($chartArray);
    die();
  }

  /**
   * @param $function
   * @param $array
   * @return array
   */
  public function WeAreDevArrayMap($function, $array) {
    $result = [];
    foreach ($array as $key => $val) {
      $result[$key] = (is_array($val) ? $this->WeAreDevArrayMap($function, $val) : $function($val));
    }

    return $result;
  }
}

new WeAreDevChartsMediaModal();
