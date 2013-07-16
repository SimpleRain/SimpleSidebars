<?php
/**
 *
 * @package   Simple Sidebars
 * @author    Dovy Pauktys <dovy@simplerain.com>
 * @license   GPL-3.0+
 * @link      http://simplerain.com
 * @copyright 2013 SimpleRain
 *
 * @wordpress-plugin
 * Plugin Name: Simple Sidebars
 * Plugin URI:  https://github.com/SimpleRain/SimpleSidebars
 * Description: Class to dynamically create sidebars from the Widget area
 * Version:     1.0.0
 * Author:      Dovy Paukstys
 * Author URI:  http://simplerain.com
 * Text Domain: simple-sidebars
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-simple-sidebars.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'Simple_Sidebars', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Simple_Sidebars', 'deactivate' ) );

Simple_Sidebars::get_instance();