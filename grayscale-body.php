<?php
/*
Plugin Name: Grayscale Body
Plugin URI: https://wordpress.org/plugins/grayscale-body/
Description: Automatically turn the site to grayscale
Version: 1.2.4
Author: Nathachai Thongniran
Author URI: http://jojoee.com/
Text Domain: gsb
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

define( 'GSB_BASE_FILE', plugin_basename( __FILE__ ) );

class Grayscale_Body {

  public function __construct() {
    $this->is_debug          = FALSE;
    $this->menu_page         = 'grayscale-body';
    $this->option_group_name = 'gsb_option_group';
    $this->option_field_name = 'gsb_option_field';
    $this->options           = get_option( $this->option_field_name );

    // set default prop
    // for only
    // - first time or
    // - no summiting form
    $this->gsb_set_default_prop();

    add_action( 'admin_menu', array( $this, 'gsb_add_menu' ) );
    add_action( 'admin_init', array( $this, 'gsb_page_init' ) );

    // add plugin link
    add_filter( 'plugin_action_links', array(
      $this,
      'gsb_plugin_action_links',
    ), 10, 4 );

    // hook
    add_action( 'wp_enqueue_scripts', array( $this, 'gsb_enqueue_scripts' ) );
    add_action( 'wp_head', array( $this, 'gsb_head' ) );
  }

  /*================================================================ Util
   */

  /**
   * Check is null or empty string
   *
   * @see http://stackoverflow.com/questions/381265/better-way-to-check-variable-for-null-or-empty-string
   *
   * @param string $str
   *
   * @return boolean
   */
  private function is_null_or_empty_string( $str ) {
    return ( ! isset( $str ) || trim( $str ) === '' );
  }

  /*================================================================ Debug
   */

  private function dd( $var = NULL, $is_die = TRUE ) {
    echo '<pre>';
    print_r( $var );
    echo '</pre>';

    if ( $is_die ) {
      die();
    }
  }

  private function da( $var = NULL ) {
    $this->dd( $var, FALSE );
  }

  private function dhead( $head, $var, $is_die = FALSE ) {
    echo '<div class="debug-box">';
    echo '================';
    echo ' ' . $head . ' ';
    echo '================';
    echo '<br>';
    $this->dd( $var, $is_die );
    echo '</div>';
  }

  private function dump( $is_die = FALSE ) {
    $this->da( $this->options, $is_die );
  }

  private function reset() {
    update_option( $this->option_field_name, array() );
  }

  /*================================================================ Public
   */
  public function gsb_head() {
    $is_enabled = $this->options['gsb_field_is_enabled'];
    $custom_css = $this->options['gsb_field_custom_css'];

    // custom css
    if ( $is_enabled && ! $this->is_null_or_empty_string( $custom_css ) ) {
      printf( '<style>%s</style>', $custom_css );
    }
    ?>

    <script>
      var gsbOption = '<?php echo json_encode( $this->options ); ?>'
    </script>
    <?php
  }

  public function gsb_enqueue_scripts() {
    global $post;

    $is_enabled         = $this->options['gsb_field_is_enabled'];
    $is_enable_switcher = $this->options['gsb_field_is_enable_switcher'];
    $ignored_post_ids   = $this->options['gsb_field_ignored_post_ids'];
    $ignored_post_ids   = explode( ",", $ignored_post_ids );
    $post_id            = $post->ID;
    $is_ignored_post    = in_array( $post_id, $ignored_post_ids );

    if ( $is_enabled && ! $is_ignored_post ) {
      if ( $is_enable_switcher ) {
        wp_enqueue_style( 'gsb-main-style', plugins_url( 'css/main.css', __FILE__ ) );
        wp_enqueue_script( 'gsb-main-script', plugins_url( 'js/main.js', __FILE__ ), array(), '120', TRUE );

      } else {
        wp_enqueue_style( 'gsb-main-style-noswitcher', plugins_url( 'css/main-noswitcher.css', __FILE__ ) );
      }
    }
  }

  /*================================================================ Callback
   */

  public function gsb_field_is_enabled_callback() {
    $field_id    = 'gsb_field_is_enabled';
    $field_name  = $this->option_field_name . "[$field_id]";
    $field_value = 1;
    $check_attr  = checked( 1, $this->options[ $field_id ], FALSE );

    printf(
      '<input type="checkbox" id="%s" name="%s" value="%s" %s />',
      $field_id,
      $field_name,
      $field_value,
      $check_attr
    );
  }

  public function gsb_field_is_enable_switcher_callback() {
    $field_id    = 'gsb_field_is_enable_switcher';
    $field_name  = $this->option_field_name . "[$field_id]";
    $field_value = 1;
    $check_attr  = checked( 1, $this->options[ $field_id ], FALSE );

    printf(
      '<input type="checkbox" id="%s" name="%s" value="%s" %s />',
      $field_id,
      $field_name,
      $field_value,
      $check_attr
    );
  }

  public function gsb_field_default_mode_callback() {
    $field_id   = 'gsb_field_default_mode';
    $field_name = $this->option_field_name . "[$field_id]";
    $positions  = array(
      array(
        'value' => 'color',
        'name'  => 'Color',
      ),
      array(
        'value' => 'grayscale',
        'name'  => 'Grayscale',
      ),
    );

    printf( '<select id="%s" name="%s">', $field_id, $field_name );
    foreach ( $positions as $position ) {
      $value       = $position['value'];
      $name        = $position['name'];
      $select_attr = selected( $this->options[ $field_id ], $value, FALSE );

      printf( '<option value="%s" %s>%s</option>',
        $value,
        $select_attr,
        $name
      );
    }
    echo '</select>';
  }

  public function gsb_field_switcher_position_callback() {
    $field_id   = 'gsb_field_switcher_position';
    $field_name = $this->option_field_name . "[$field_id]";
    $positions  = array(
      array(
        'value' => 'top-left',
        'name'  => 'Top left',
      ),
      array(
        'value' => 'top-right',
        'name'  => 'Top right',
      ),
      array(
        'value' => 'bottom-left',
        'name'  => 'Bottom left',
      ),
      array(
        'value' => 'bottom-right',
        'name'  => 'Bottom right',
      ),
    );

    printf( '<select id="%s" name="%s">', $field_id, $field_name );
    foreach ( $positions as $position ) {
      $value       = $position['value'];
      $name        = $position['name'];
      $select_attr = selected( $this->options[ $field_id ], $value, FALSE );

      printf( '<option value="%s" %s>%s</option>',
        $value,
        $select_attr,
        $name
      );
    }
    echo '</select>';
  }

  public function gsb_field_ignored_post_ids_callback() {
    $field_id    = 'gsb_field_ignored_post_ids';
    $field_name  = $this->option_field_name . "[$field_id]";
    $field_value = $this->options[ $field_id ];

    printf(
      '<input type="text" id="%s" name="%s" value="%s" placeholder="%s">',
      $field_id,
      $field_name,
      $field_value,
      "ignored post ids for examples: 1,3,4"
    );
  }

  public function gsb_field_custom_css_callback() {
    $field_id    = 'gsb_field_custom_css';
    $field_name  = $this->option_field_name . "[$field_id]";
    $field_value = $this->options[ $field_id ];

    printf(
      '<textarea id="%s" name="%s" type="textarea">%s</textarea>',
      $field_id,
      $field_name,
      $field_value
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
    //   'gsb_field_default_mode'           => 'grayscale'
    //   'gsb_field_switcher_position'      => 'top-right'
    //   'gsb_field_ignored_post_ids'       => ''
    //   'gsb_field_custom_css'             => ''
    // ]

    $options = $this->options;

    if ( ! isset( $options['gsb_field_is_enabled'] ) ) {
      $options['gsb_field_is_enabled'] = 1;
    }
    if ( ! isset( $options['gsb_field_is_enable_switcher'] ) ) {
      $options['gsb_field_is_enable_switcher'] = 0;
    }
    if ( ! isset( $options['gsb_field_default_mode'] ) || ( $options['gsb_field_default_mode'] === '' ) ) {
      $options['gsb_field_default_mode'] = 'grayscale';
    }
    if ( ! isset( $options['gsb_field_switcher_position'] ) || ( $options['gsb_field_switcher_position'] === '' ) ) {
      $options['gsb_field_switcher_position'] = 'top-right';
    }
    if ( ! isset( $options['gsb_field_ignored_post_ids'] ) ) {
      $options['gsb_field_ignored_post_ids'] = '';
    }
    if ( ! isset( $options['gsb_field_custom_css'] ) ) {
      $options['gsb_field_custom_css'] = '';
    }

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
    <?php if ( $this->is_debug ) {
      $this->dump();
    } ?>
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
        padding-bottom: 6px;
        line-height: 30px;
        height: 30px;
      }

      #gsb_field_ignored_post_ids {

      }

      #gsb_field_custom_css {

      }

      @media (min-width: 768px) {
        #gsb_field_ignored_post_ids {
          min-width: 600px;
        }

        #gsb_field_custom_css {
          min-width: 600px;
          min-height: 300px;
        }
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
    // - default_mode
    // - switcher_position
    // - ignored_post_ids
    // - custom_css
    add_settings_field(
      'gsb_field_is_enabled',
      'Plugin: enable the plugin',
      array( $this, 'gsb_field_is_enabled_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'gsb_field_is_enable_switcher',
      'Switcher: enable the switcher',
      array( $this, 'gsb_field_is_enable_switcher_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'gsb_field_default_mode',
      'Default mode',
      array( $this, 'gsb_field_default_mode_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'gsb_field_switcher_position',
      'Switcher: position',
      array( $this, 'gsb_field_switcher_position_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'gsb_field_ignored_post_ids',
      'Ignored Post IDs',
      array( $this, 'gsb_field_ignored_post_ids_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'gsb_field_custom_css',
      'Custom CSS',
      array( $this, 'gsb_field_custom_css_callback' ),
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
    $text_input_ids = array(
      'gsb_field_default_mode',
      'gsb_field_switcher_position',
      'gsb_field_ignored_post_ids',
      'gsb_field_custom_css',

    );
    foreach ( $text_input_ids as $text_input_id ) {
      $result[ $text_input_id ] = isset( $input[ $text_input_id ] )
        ? sanitize_text_field( $input[ $text_input_id ] )
        : '';
    }

    // number
    $number_input_ids = array(
      'gsb_field_is_enabled',
      'gsb_field_is_enable_switcher',
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
