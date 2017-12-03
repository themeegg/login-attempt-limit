<?php
/**
 * Plugin Name: Login Attempt Limit
 * Plugin URI: https://themeegg.com/plugins/login-attempt-limit
 * Description: Limit rate of login attempts
 * Version: 1.0.0
 * Author: ThemeEgg
 * Author URI: https://themeegg.com
 * Requires at least: 3.0.1
 * Tested up to: 4.9.1
 *
 * Text Domain: login-attempt-limit
 * Domain Path: /i18n/languages/
 *
 * @package LoginAttemptLimit
 * @category Core
 * @author ThemeEgg
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Login_Attempt_Limit' ) ) :

	/**
	 * Main Login_Attempt_Limit Class.
	 *
	 * @class Login_Attempt_Limit
	 * @version    1.0.0
	 */
	final class Login_Attempt_Limit {

		/**
		 * Login_Attempt_Limit version.
		 *
		 * @var string
		 */
		public $version = '1.0.0';


		/**
		 * The single instance of the class.
		 *
		 * @var Login_Attempt_Limit
		 * @since 1.0
		 */
		protected static $_instance = null;


		/**
		 * Main Login_Attempt_Limit Instance.
		 *
		 * Ensures only one instance of Login_Attempt_Limit is loaded or can be loaded.
		 *
		 * @since 1.0
		 * @static
		 * @see TEGTApi()
		 * @return Login_Attempt_Limit - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 * @since 1.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'login-attempt-limit' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'login-attempt-limit' ), '1.0' );
		}

		/**
		 * Auto-load in-accessible properties on demand.
		 *
		 * @param mixed $key
		 *
		 * @return mixed
		 */
		public function __get( $key ) {

			return $this->$key();

		}

		/**
		 * Login_Attempt_Limit Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			//$this->init_hooks();

			do_action( 'login_attempt_limit_loaded' );
		}

		/**
		 * Hook into actions and filters.
		 * @since  1.0
		 */
		private function init_hooks() {
			//register_activation_hook( __FILE__, array( 'LAL_Install', 'install' ) );
			add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
			add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
			add_action( 'init', array( $this, 'init' ), 0 );


		}

		/**
		 * Define LAL Constants.
		 */
		private function define_constants() {
			$upload_dir = wp_upload_dir();

			$this->define( 'LAL_DS', DIRECTORY_SEPARATOR );
			$this->define( 'LAL_PLUGIN_FILE', __FILE__ );
			$this->define( 'LAL_ABSPATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );
			$this->define( 'LAL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'LAL_VERSION', $this->version );
			$this->define( 'LAL_DIRECT_ADDR', 'REMOTE_ADDR' );
			$this->define( 'LAL_PROXY_ADDR', 'HTTP_X_FORWARDED_FOR' );
			/* Notify value checked against these in limit_login_sanitize_variables() */
			$this->define( 'LAL_LOCKOUT_NOTIFY_ALLOWED', 'log,email' );

		}

		/**
		 * Define constant if not already set.
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 *
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * Check the active theme.
		 *
		 * @since  2.6.9
		 *
		 * @param  string $theme Theme slug to check
		 *
		 * @return bool
		 */
		private function is_active_theme( $theme ) {
			return get_template() === $theme;
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
			/**
			 * Class autoloader.
			 */

			include( LAL_ABSPATH . 'includes' . LAL_DS . 'main.php' );


			if ( $this->is_request( 'admin' ) ) {

			}

			if ( $this->is_request( 'frontend' ) ) {

			}


		}

		/**
		 * Include required frontend files.
		 */
		public function frontend_includes() {


		}

		/**
		 * Function used to Init Login_Attempt_Limit Template Functions - This makes them pluggable by plugins and themes.
		 */
		public function include_template_functions() {
		}

		/**
		 * Init Login_Attempt_Limit when WordPress Initialises.
		 */
		public function init() {
			// Before init action.
			do_action( 'before_login_attempt_limit_init' );
			// Set up localisation.
			$this->load_plugin_textdomain();
			// Init action.
			do_action( 'login_attempt_limit_init' );
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/login-attempt-limit/login-attempt-limit-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/login-attempt-limit-LOCALE.mo
		 */
		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'login-attempt-limit' );

			unload_textdomain( 'login-attempt-limit' );
			load_textdomain( 'login-attempt-limit', WP_LANG_DIR . '/login-attempt-limit/login-attempt-limit-' . $locale . '.mo' );
			load_plugin_textdomain( 'login-attempt-limit', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages' );
		}

		/**
		 * Ensure theme and server variable compatibility and setup image sizes.
		 */
		public function setup_environment() {

			$this->define( 'LAL_TEMPLATE_PATH', $this->template_path() );


		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Get the template path.
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'login_attempt_limit_template_path', 'login-attempt-limit/' );
		}

		/**
		 * Get Ajax URL.
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}

	}

endif;

/**
 * Main instance of Login_Attempt_Limit.
 *
 * Returns the main instance of LAL() to prevent the need to use globals.
 *
 * @since  1.0
 * @return Login_Attempt_Limit
 */
function LAL() {
	return Login_Attempt_Limit::instance();
}

// Global for backwards compatibility.
$GLOBALS['login_attempt_limit'] = LAL();
