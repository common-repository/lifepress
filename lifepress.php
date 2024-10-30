<?php
/*
 * Plugin Name: LifePress
 * Plugin URI: http://www.ashanjay.com/lifepress
 * Description: You are the creator of events in your life. Record and track progress in your life.
 * Author: Ashan Jay
 * Version: 2.1
 * Requires at least: 6.0
 * Tested up to: 6.6.1
 * Author URI: http://www.ashanjay.com/
 * 
 * Text Domain: lp
 * Domain Path: /lang/languages/
 * 
 * @package LifePress
 * @category Core
 * @author ashanjay
 */

class LIFEPRESS{
	
	public $version='2.1
	';
	public $name = 'LifePress';
	public $date_format = 'Y-m-d';
	public $template_url;
	public $temp, $time, $front, $props, $help, $shortcode, $assets_path; 

	protected static $_instance = null;

	// setup one instance of lifepress
		public static function instance(){
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

	// CONSTRUCT
		public function __construct() {

			// Define constants
			$this->define_constants();	
			
			// Installation
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			
			// Include required files
			$this->includes();
					
			// Hooks
			add_action( 'init', array( $this, 'init' ), 0 );
			add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
			
			// Deactivation
			register_deactivation_hook( LIFEPRESS_FILE, array($this,'deactivate'));
		}

	// INCLUDE Files
		private function includes(){
			include_once('includes/class-install.php');
			include_once('includes/class-helper.php');
			include_once('includes/class-time.php');
			include_once('includes/class-lp_type_data.php');
			include_once('includes/class-entry.php');
			include_once('includes/class-entries.php');
			include_once('templates/class-template_parts.php');
			include_once('includes/class-shortcode.php');
			include_once('includes/class-frontend.php');
			include_once('includes/class-settings.php');

			include_once('includes/admin/class-admin.php');

			if ( defined('DOING_AJAX') ){
				include_once('includes/class-ajax.php');
			}
		}

	// INIT
		function init(){

			if( !class_exists('LIFEPRESS_Helper')) return;

			$this->template_url = apply_filters('lifepress_template_url','lifepress/');

			$this->temp = new LIFEPRESS_Temp();
			$this->time = new LIFEPRESS_Time();
			$this->front = new LIFEPRESS_Front();
			$this->props = new LIFEPRESS_Settings();
			$this->help = new LIFEPRESS_Helper();
			$this->shortcode = new LIFEPRESS_Shortcodes();
		}

	public function template_path(){	return 'LifePress/';	}

	// Activate
		public function activate(){
			set_transient( '_lp_activation_redirect', 1, 60 * 60 );		
			do_action('lp_activate');
		}	

	// deactivate 
		public function deactivate(){	do_action('lp_deactivate');	}

	// Define ET Constants
		public function define_constants() {
			if(!defined('LIFEPRESS_VERSION'))	define('LIFEPRESS_VERSION', $this->version);

			define( "LIFEPRESS_DIR", WP_PLUGIN_DIR ); //E:\xampp\htdocs\WP/wp-content/plugins
			define( "LIFEPRESS_PATH", dirname( __FILE__ ) );// E:\xampp\htdocs\WP/wp-content/plugins/LifePress/
			define( "LIFEPRESS_FILE", ( __FILE__ ) ); //E:\xampp\htdocs\WP\wp-content\plugins\LifePress\lifepress.php
			define( "LIFEPRESS_URL", path_join(plugins_url(), basename(dirname(__FILE__))) );
			define( "LIFEPRESS_BASENAME", plugin_basename(__FILE__) ); //LifePress/lifepress.php
			define( "LIFEPRESS_BASE", basename(dirname(__FILE__)) ); //LifePress
			define( "LIFEPRESS_BACKEND_URL", get_bloginfo('url').'/wp-admin/' ); 
			$this->assets_path = str_replace(array('http:','https:'), '',LIFEPRESS_URL).'/assets/';
			
		}
	/** Ensure theme and server variable compatibility and setup image sizes.. */
		public function setup_environment() {
			// Post thumbnail support
			if ( ! current_theme_supports( 'post-thumbnails', 'lp_entry' ) ) {
				add_theme_support( 'post-thumbnails' );
				remove_post_type_support( 'post', 'thumbnail' );
				remove_post_type_support( 'page', 'thumbnail' );
			} else {
				add_post_type_support( 'lp_entry', 'thumbnail' );
			}
		}

}

function LIFEPRESS(){return LIFEPRESS::instance();}

// Initiate this addon within the plugin
$GLOBALS['LIFEPRESS'] = LIFEPRESS();
?>