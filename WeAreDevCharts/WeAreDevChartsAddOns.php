<?php
/**
 * Class WeAreDevChartsAddOns
 * This will be add the add ons page for the Charts
 */

class WeAreDevChartsAddOns {

  public function __construct() {
    add_action('admin_menu', [$this, 'addOnPage']);
    add_action('admin_enqueue_scripts', [$this, 'enqueueStyles']);
  }

  public function addOnPage() {
    add_submenu_page(
      'edit.php?post_type=chart',
      __('Add-ons', 'we-are-dev'),
      __('Add-ons', 'we-are-dev'),
      'manage_options',
      'wearedev-add-ons',
      [$this, 'renderAddOnPage']
    );
  }

  public function enqueueStyles($hook) {
    if ($hook !== 'chart_page_wearedev-add-ons') {
      return false;
    }
    wp_enqueue_style('wad-add-ons', plugins_url('assets/styles/wearedev-add-ons-page.css', __DIR__), '', $this->version);
  }

  public function WeAreDevChartsAvailableAddOns() {
    $addOn = [
      'wearedev-charts-bar' => [
        'name' => 'Bar chart - add on',
        'description' => 'Create your bar chart with this add-on',
        'image' => 'https://s3-eu-west-1.amazonaws.com/wearedev-charts/add-on-bar.png',
        'url' => 'https://wearedev.io/plugins/charts/bar-chart/?utm_source=wearedev-charts&utm_medium=plugin&utm_campaign=add-on-page&utm_content=wearedev-charts-bar',
        'installed' => class_exists('WeAreDevChartsBarChartAddOn')
      ],
      'wearedev-charts-line' =>[
        'name' => 'Line chart - add on',
        'description' => 'Create your line chart with this add-on',
        'image' => 'https://s3-eu-west-1.amazonaws.com/wearedev-charts/add-on-line.png',
        'url' => 'https://wearedev.io/plugins/charts/line-chart/?utm_source=wearedev-charts&utm_medium=plugin&utm_campaign=add-on-page&utm_content=wearedev-charts-line',
        'installed' => class_exists('WeAreDevChartsLineChartAddOn')
      ],
      'wearedev-charts-pie' =>[
        'name' => 'Pie chart - add on',
        'description' => 'Create your pie chart with this add-on',
        'image' => 'https://s3-eu-west-1.amazonaws.com/wearedev-charts/add-on-pie.png',
        'url' => 'https://wearedev.io/plugins/charts/pie-chart/?utm_source=wearedev-charts&utm_medium=plugin&utm_campaign=add-on-page&utm_content=wearedev-charts-pie',
        'installed' => class_exists('WeAreDevChartsPieChartAddOn')
      ],
    ];

    return $addOn;
  }

  public function renderAddOnPage() { ?>
    <div class="wrap">
      <h1><?php echo __('Add-ons', 'we-are-dev'); ?></h1>
      <?php
      if (!has_action('wearedev_addons_installed')) {
        echo __('No available add-ons founded for WeAreDev Charts.', 'wearedev-charts');
      }
      do_action('wearedev_addons_installed');
      ?>
      <h1><?php echo __('Available add-ons', 'we-are-dev'); ?></h1>
      <div class="wearedev-addons">
        <?php
          foreach ($this->WeAreDevChartsAvailableAddOns() as $addOn => $addOnInformation) {
            if ($addOnInformation['installed'] !== true) {
              echo '<div class="addon">';
            } else {
              echo '<div class="addon addon-installed">';
            }
            echo '<img src="' . $addOnInformation['image'] . '" alt="">';
            echo '<h3>' . $addOnInformation['name'] . '</h3>';
            echo '<p>' . $addOnInformation['description'] . '</p>';
            if ($addOnInformation['installed'] !== true) {
              echo '<a target="_blank" href="' . $addOnInformation['url'] . '" class="button button-primary">Get add-on</a>';
            } else {
              echo '<a class="button">Installed</a>';
            }
            echo '</div>';
          }
        ?>
      </div>
    </div>
  <?php
  }
}

new WeAreDevChartsAddOns();
