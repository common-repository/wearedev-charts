<?php
/**
 * Class WeAreDevChartsPostType
 * This will be add the custom post type for the Charts
 */

class WeAreDevChartsPostType {

  public function __construct() {
    add_action('init', [$this, 'WeAreDevRegisterPostType']);
    add_action('add_meta_boxes', [$this, 'WeAreDevAddMetaBox']);
    add_action('save_post', [$this, 'WeAreDevSaveMetaBox']);
    add_filter('manage_chart_posts_columns', [$this, 'WeAreDevAddChartsColumns']);
    add_action('manage_chart_posts_custom_column', [$this, 'WeAreDevAddListingActions'], 10, 2);
  }

  /**
   * Register post type: Charts
   * This will be add the custom post type for the Charts
   */
  public function WeAreDevRegisterPostType() {
    $args = [
      'labels' => [
        'name' => __('Charts', 'wearedev-charts'),
        'singular_name' => __('Chart', 'wearedev-charts'),
        'add_new' => __('Add chart', 'wearedev-charts'),
        'add_new_item' => __('Add new chart', 'wearedev-charts'),
        'edit_item' => __('Edit chart', 'wearedev-charts'),
        'new_item' => __('New chart', 'wearedev-charts'),
        'view_item' => __('View chart', 'wearedev-charts'),
        'search_items' => __('Search chart', 'wearedev-charts'),
        'not_found' => __('Nothing found', 'wearedev-charts'),
        'not_found_in_trash' => __('Nothing found in trash', 'wearedev-charts')
      ],
      'public' => false,
      'show_ui' => true,
      'capability_type' => 'post',
      'menu_position' => 11,
      'supports' => ['title'],
      'exclude_from_search' => true,
      'show_in_admin_bar' => false,
      'show_in_nav_menus' => false,
      'publicly_queryable' => true,
      'query_var' => true,
      'has_archive' => false,
      'menu_icon' => 'dashicons-chart-bar',
    ];
    register_post_type('chart', $args);
  }

  public function WeAreDevAddChartsColumns($columns) {
    unset($columns['author'], $columns['date']);
    return array_merge($columns, [
        'type' => __('Type chart', 'wearedev-charts')
      ]
    );
  }

  public function WeAreDevAddListingActions($column, $post_id) {
    if ($column === 'type') {
      echo ucfirst(get_post_meta($post_id, 'chart_type', true));
    }
  }

  public function WeAreDevAddMetaBox() {
    add_meta_box('wearedev-charts', 'Chart', [$this, 'WeAreDevMetaBoxCallback'], 'chart', 'normal', 'high');
  }

  public function WeAreDevMetaBoxCallback($post) {
    wp_enqueue_style('vue-charts', plugins_url('assets/styles/main.css', __DIR__), '', WeAreDevCharts::VERSION);
    wp_enqueue_script('vue-manifest', plugins_url('assets/scripts/manifest.js', __DIR__), '', WeAreDevCharts::VERSION, true);
    wp_enqueue_script('vue-vendor', plugins_url('assets/scripts/vendor.js', __DIR__), '', WeAreDevCharts::VERSION, true);
    wp_register_script('vue-charts', plugins_url('assets/scripts/app.js', __DIR__), '', WeAreDevCharts::VERSION, true);
    // Localize the script with store data
    if (get_post_meta($post->ID, 'chart_data')) {
      $storeArray = [
        'id' => $post->ID,
        'selectedChart' => get_post_meta($post->ID, 'chart_type', true),
        'data' => get_post_meta($post->ID, 'chart_data', true),
        'dataset' => get_post_meta($post->ID, 'chart_dataset', true),
        'location' => 'charts-cpt'
      ];
    } else {
      $storeArray = [
        'id' => 'add_chart',
        'availableCharts' => WeAreDevCharts::WeAreDevGetAvailableChartsAddOn(),
        'selectedChart' => '',
        'location' => 'charts-cpt'
      ];
    }
    wp_localize_script('vue-charts', 'store', $storeArray);
    wp_enqueue_script('vue-charts');
    wp_nonce_field('wearedev_meta_box_save', 'wearedev_nonce');
    ?>
    <div id="app"></div>
    <?php
    do_action('wearedev_chart_metabox');
  }

  /**
   * @param $postId
   */
  public function WeAreDevSaveMetaBox($postId) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    if (!isset($_POST['wearedev_nonce']) || !wp_verify_nonce($_POST['wearedev_nonce'], 'wearedev_meta_box_save')) {
      return;
    }

    if (!current_user_can('edit_post')){
      return;
    }

    $postData = $this->WeAreDevArrayMap('trim', $_POST['data']);
    if (isset($postData)) {
      update_post_meta($postId, 'chart_type', sanitize_text_field($_POST['chartSort']));
      update_post_meta($postId, 'chart_data', $postData);
    }
    if (isset($_POST['dataset'])) {
      $json = $_POST['dataset'];
      $json_string = stripslashes($json);
      $dataset = json_decode($json_string, true);

      update_post_meta($postId, 'chart_dataset', $dataset);
    }

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

new WeAreDevChartsPostType();
