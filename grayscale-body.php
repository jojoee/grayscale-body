<?php
/*
Plugin Name: Grayscale Body
Plugin URI: https://wordpress.org/plugins/grayscale-body/
Description: Automatically turn the site to grayscale
Version: 1.2.2
Author: Nathachai Thongniran
Author URI: http://jojoee.com/
Text Domain: gsb
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

define( 'GSB_BASE_FILE', plugin_basename( __FILE__ ) );

class Grayscale_Body {

  public function __construct() {
    $this->is_debug = false;
    $this->menu_page = 'grayscale-body';
    $this->option_group_name = 'gsb_option_group';
    $this->option_field_name = 'gsb_option_field';
    $this->options = get_option( $this->option_field_name );

    // set default prop
    // for only
    // - first time or
    // - no summiting form
    $this->gsb_set_default_prop();

    add_action( 'admin_menu', array( $this, 'gsb_add_menu' ) );
    add_action( 'admin_init', array( $this, 'gsb_page_init' ) );

    // add plugin link
    add_filter( 'plugin_action_links', array( $this, 'gsb_plugin_action_links' ), 10, 4 );

    // hook
    add_action( 'wp_enqueue_scripts', array( $this, 'gsb_enqueue_scripts' ) );
    add_action( 'wp_head', array( $this, 'gsb_head' ) );
  }

  /*================================================================ Debug
   */

  private function dd( $var = null, $is_die = true ) {
    echo '<pre>';
    print_r( $var );
    echo '</pre>';

    if ( $is_die ) die();
  }

  private function da( $var = null ) {
    $this->dd( $var, false );
  }

  private function dhead( $head, $var, $is_die = false ) {
    echo '<div class="debug-box">';
    echo '================';
    echo ' ' . $head . ' ';
    echo '================';
    echo '<br>';
    $this->dd( $var, $is_die );
    echo '</div>';
  }

  private function dump( $is_die = false ) {
    $this->da( $this->options, $is_die );
  }

  private function reset() {
    update_option( $this->option_field_name, array() );
  }

  /*================================================================ Public
   */
  
  public function gsb_head() { ?>
    <script>
      var gsbOption = '<?php echo json_encode( $this->options ); ?>';
    </script>
    <?php
  }

  public function gsb_enqueue_scripts() {
    $is_enabled = $this->options['gsb_field_is_enabled'];
    $is_enable_switcher = $this->options['gsb_field_is_enable_switcher'];

    if ( $is_enabled ) {
      if ( $is_enable_switcher ) {
        wp_enqueue_style( 'gsb-main-style', plugins_url( 'css/main.css', __FILE__ ) );
        wp_enqueue_script( 'gsb-main-script', plugins_url('js/main.js', __FILE__), array(), '120', true);

      } else {
        wp_enqueue_style( 'gsb-main-style-noswitcher', plugins_url( 'css/main-noswitcher.css', __FILE__ ) );
      }
    }
  }

  /*================================================================ Callback
   */
  
  public function gsb_field_is_enabled_callback() {
    $field_id = 'gsb_field_is_enabled';
    $field_name = $this->option_field_name . "[$field_id]";
    $field_value = 1;
    $check_attr = checked( 1, $this->options[ $field_id ], false );

    printf(
      '<input type="checkbox" id="%s" name="%s" value="%s" %s />',
      $field_id,
      $field_name,
      $field_value,
      $check_attr
    );
  }

  public function gsb_field_is_enable_switcher_callback() {
    $field_id = 'gsb_field_is_enable_switcher';
    $field_name = $this->option_field_name . "[$field_id]";
    $field_value = 1;
    $check_attr = checked( 1, $this->options[ $field_id ], false );

    printf(
      '<input type="checkbox" id="%s" name="%s" value="%s" %s />',
      $field_id,
      $field_name,
      $field_value,
      $check_attr
    );
  }

  public function gsb_field_is_switcher_move2left_callback() {
    $field_id = 'gsb_field_is_switcher_move2left';
    $field_name = $this->option_field_name . "[$field_id]";
    $field_value = 1;
    $check_attr = checked( 1, $this->options[ $field_id ], false );

    printf(
      '<input type="checkbox" id="%s" name="%s" value="%s" %s />',
      $field_id,
      $field_name,
      $field_value,
      $check_attr
    );
  }

  /*================================================================ Option
   */

  public function gsb_set_default_prop() {
    // default
    // 
    // [
    //   'gsb_field_is_enabled'             => 1
    //   'gsb_field_is_enable_switcher'     => 0
    //   'gsb_field_is_switcher_move2left'  => 0
    // ]

    $options = $this->options;

    if ( ! isset( $options['gsb_field_is_enabled'] ) )              $options['gsb_field_is_enabled'] = 1;
    if ( ! isset( $options['gsb_field_is_enable_switcher'] ) )      $options['gsb_field_is_enable_switcher'] = 0;
    if ( ! isset( $options['gsb_field_is_switcher_move2left'] ) )   $options['gsb_field_is_switcher_move2left'] = 0;

    $this->options = $options;
  }

  public function gsb_add_menu() {
    // args
    // - page title
    // - menu title
    // - capability
    // - menu slug (menu page)
    // - function
    add_options_page(
      'Grayscale Body',
      'Grayscale Body',
      'manage_options',
      $this->menu_page,
      array( $this, 'gsb_admin_page' )
    );
  }

  /**
   * Options page callback
   * 
   * TODO: relocate style
   */
  public function gsb_admin_page() { ?>
    <?php if ( $this->is_debug ) $this->dump(); ?>
    <div class="wrap">
      <h1>Grayscale Body</h1>
      <form method="post" action="options.php">
        <?php
          settings_fields( $this->option_group_name );
          do_settings_sections( $this->menu_page );
          submit_button();
        ?>
      </form>
    </div>
    <style>
    .debug-box {
      padding: 12px 0;
    }
    .form-table th,
    .form-table td {
      padding: 0;
      line-height: 30px;
      height: 30px;
    }
    </style>
    <?php
  }

  public function gsb_page_init() {
    $section_id = 'gsb_setting_section_id';

    register_setting(
      $this->option_group_name,
      $this->option_field_name,
      array( $this, 'sanitize' )
    );

    // section
    add_settings_section(
      $section_id,
      'Settings',
      array( $this, 'print_section_info' ),
      $this->menu_page
    );

    // option field(s)
    // - is_enabled
    // - is_enable_switcher
    // - is_switcher_move2left
    add_settings_field(
      'gsb_field_is_enabled',
      'Enable',
      array( $this, 'gsb_field_is_enabled_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'gsb_field_is_enable_switcher',
      'Enable switcher',
      array( $this, 'gsb_field_is_enable_switcher_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'gsb_field_is_switcher_move2left',
      'Switcher: move to left',
      array( $this, 'gsb_field_is_switcher_move2left_callback' ),
      $this->menu_page,
      $section_id
    );
  }

  public function print_section_info() {
    print 'Enter your settings below:';
  }

  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function sanitize( $input ) {
    $result = array();

    // text
    // (unused)
    $text_input_ids = array();
    foreach ( $text_input_ids as $text_input_id ) {
      $result[ $text_input_id ] = isset( $input[ $text_input_id ] )
        ? sanitize_text_field( $input[ $text_input_id ] )
        : '';
    }

    // number
    $number_input_ids = array(
      'gsb_field_is_enabled',
      'gsb_field_is_enable_switcher',
      'gsb_field_is_switcher_move2left'
    );
    foreach ( $number_input_ids as $number_input_id ) {
      $result[ $number_input_id ] = isset( $input[ $number_input_id ] )
        ? sanitize_text_field( $input[ $number_input_id ] )
        : 0;
    }

    return $result;
  }

  public function gsb_plugin_action_links( $links, $plugin_file ) {
    $plugin_link = array();

    if ( $plugin_file == GSB_BASE_FILE ) {
      $plugin_link[] = '<a href="' . admin_url( 'options-general.php?page=' . $this->menu_page ) . '">Settings</a>';
    }

    return array_merge( $links, $plugin_link );
  }
}

$grayscale_body = new Grayscale_Body();
