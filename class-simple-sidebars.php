<?php
/**
 * Plugin Name.
 *
 * @package   Simple_Sidebars
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */

/**
 * Plugin class.
 *
 * TODO: Rename this class to a proper name for your plugin.
 *
 * @package Simple_Sidebars
 * @author  Your Name <email@example.com>
 */
class Simple_Sidebars {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'simple-sidebars';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Array of enabled sidebars
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected $sidebars = array();	

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Additional hooks and items.
    add_action('load-widgets.php', array(&$this, 'load_assets') , 5 );
    add_action('widgets_init', array(&$this, 'register_custom_sidebars') , 1000 );
    add_action('wp_ajax_delete_simple_sidebar', array(&$this, 'delete_sidebar_area') , 1000 );

    $this->title = __('Create Custom Widget', $this->plugin_slug);

	}


  /**
	 * Function to create the HTML used to create sidebars.
	 *
	 * @since     1.0.0
	 *
	 */  
  public function add_new_widget_area_box() {
    $nonce =  wp_create_nonce ('delete-simple-sidebar-nonce');
    ?>
      <script type="text/html" id="simple-add-widget-template">
        <input type="hidden" name="simple-nonce" value="<?php echo $nonce ?>" />
        <div id="simple-add-widget" class="widgets-holder-wrap">
          <div class="sidebar-name" style="cursor: inherit;">
            <h3><?php echo $this->title; ?> <span class="spinner"></span></h3>
          </div>
          <div id="jumbotron" class="widgets-sortables ui-sortable" style="min-height: 50px;">
            <form action="" method="post">
              <div class="widget-content">
                <p style="font-weight: bold;"><label for="simple-add-widget-input"><?php echo __('New Widget Name', $this->plugin_slug); ?>:</label>
                <input id="simple-add-widget-input" name="simple-add-widget-input" type="text" class="regular-text" title="<?php echo __('New Widget Name', $this->plugin_slug); ?>" />
              </div>
              <div class="widget-control-actions">
                <div class="aligncenter">
                  <input class="button-primary" type="submit" value="<?php echo __('Create New Widget', $this->plugin_slug); ?>" />
                </div>
                <br class="clear">
              </div>
            </form>
          </div>
        </div>
      </script>
    <?php
  }
 
  /**
	 * Function to add all needed assets when used.
	 *
	 * @since     1.0.0
	 *
	 */  
  function load_assets() {

    add_action('admin_print_scripts', array(&$this, 'add_new_widget_area_box') );
    add_action('load-widgets.php', array(&$this, 'add_sidebar_area'), 100);

    add_action('load-widgets.php', array(&$this, 'enqueue_admin_styles'), 100);
    add_action('load-widgets.php', array(&$this, 'enqueue_admin_scripts'), 100);

  }

  
  /**
	 * Function to create a new sidebar
	 *
	 * @since     1.0.0
	 *
	 * @param    string    Name of the sidebar to be deleted.
	 *
	 * @return    string     'sidebar-deleted' if successful.
	 *
	 */
  function add_sidebar_area() {
    if(!empty($_POST['simple-add-widget-input'])) {
        $this->sidebars = get_theme_mod($this->plugin_slug);
        $name           = $this->check_sidebar_name($_POST['simple-add-widget-input']);
        
        if(empty($this->sidebars)) {
            $this->sidebars = array($name);
        } else {
            $this->sidebars = array_merge($this->sidebars, array($name));
        }
        
        set_theme_mod($this->plugin_slug, $this->sidebars);
        wp_redirect( admin_url('widgets.php') );
        die();
    }
  }
  
  /**
	 * Before we create a new sidebar, verify it doesn't already exist. If it does, append a number to the name.
	 *
	 * @since     1.0.0
	 *
	 * @param    string    Name of the sidebar to be deleted.
	 *
	 * @return    string     'sidebar-deleted' if successful.
	 *
	 */
  function delete_sidebar_area() {
    check_ajax_referer('delete-simple-sidebar-nonce');
  
    if(!empty($_POST['name'])) {
        $name = stripslashes($_POST['name']);
        $this->sidebars = get_theme_mod($this->plugin_slug);
        print_r($this->sidebars);
        if(($key = array_search($name, $this->sidebars)) !== false) {
            unset($this->sidebars[$key]);
            set_theme_mod($this->plugin_slug, $this->sidebars);
            echo "sidebar-deleted";
        }
    }
    
    die();
  }
  
  
  
	/**
	 * Before we create a new sidebar, verify it doesn't already exist. If it does, append a number to the name.
	 *
	 * @since     1.0.0
	 *
	 * @param    string    $name    Name of the sidebar to be created.
	 *
	 * @return    name     $name 	  Name of the new sidebar just created.
	 *
	 */
  function check_sidebar_name($name) {
    if(empty($GLOBALS['wp_registered_sidebars'])) 
      return $name;

    $taken = array();
    foreach ( $GLOBALS['wp_registered_sidebars'] as $sidebar ) {
      $taken[] = $sidebar['name'];
    }
    
    if(empty($this->sidebars)) 
      $this->sidebars = array();
    
    $taken = array_merge($taken, $this->sidebars);
    
    if(in_array($name, $taken)) {
      $counter  = substr($name, -1);  
      $new_name = "";
          
      if(!is_numeric($counter)) {
        $new_name = $name . " 1";
      } else {
        $new_name = substr($name, 0, -1) . ((int) $counter + 1);
      }
      
      $name = $this->check_sidebar_name($new_name);
    }
    
    return $name;
  }
  
	/**
	 * Register and display the custom sidebar areas we have set.
	 *
	 * @since     1.0.0
	 *
	 */
  function register_custom_sidebars() {
    if(empty($this->sidebars)) $this->sidebars = get_theme_mod($this->plugin_slug);

    $options = array(
      'before_title'  => '<h3 class="widgettitle">', 
      'after_title'   => '</h3>',
      'before_widget' => '<div id="%1$s" class="widget clearfix %2$s">', 
      'after_widget'  => '</div>'
      );
      
    $options = apply_filters('simple_custom_widget_args', $options);
          
    if(is_array($this->sidebars)) {
      foreach ($this->sidebars as $sidebar) { 
        $options['class'] = 'simple-custom';
        $options['name']  = $sidebar;
        register_sidebar($options);
      }
    }
  }


	/**
	 * Return the sidebars array.
	 *
	 * @since     1.0.0
	 *
	 * @return    array    If not empty, active simple sidebars are returned.
	 */
	public static function get_simple_sidebars() {

		// If the single instance hasn't been set, set it now.
		if ( !empty($this->sidebars) ) {
			return $this->sidebars;
		}

	}



	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		// TODO: Define activation functionality here
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		wp_enqueue_style( 'Simple_Sidebars', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );		

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		wp_enqueue_script( 'Simple_Sidebars', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );

	}



}