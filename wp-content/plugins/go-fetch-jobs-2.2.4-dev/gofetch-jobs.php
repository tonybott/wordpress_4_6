<?php
/**
 * Plugin Name: Go Fetch Jobs (for JobRoller)
 * Version:     2.2.4
 * Description: Instantly populate your JobRoller database using RSS job feeds from the most popular job sites.
 * Author:      SebeT
 * Author URI:  http://bruno-carreco.com
 * Plugin URI:  https://marketplace.appthemes.com/plugins/go-fetch-jobs
 * Other:       Icons by freepik 'http://www.freepik.com'

 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

### Freemius dummy class.

class FS_Premium {

	function __construct() {
	}

	public function is__premium_only() {
		return true;
	}

	public function is_plan__premium_only( $plan = '', $exact = false ) {
		return true;
	}

	public function is__premium() {
		return true;
	}

	public function is_paying() {
		return true;
	}

	public function is_not_paying() {
		return false;
	}

}

// Create a helper function for easy SDK access.
function gofj_jr_fs() {
	return new FS_Premium;
}

// Init Freemius.
gofj_jr_fs();

###


register_activation_hook( __FILE__, array( 'GoFetch_JR', 'activate' ) );

// Initialize the plugin only after the JobRoller theme is fully initialized.
add_action( 'plugins_loaded', 'GoFetch_JR', 998 );

if ( ! class_exists( 'GoFetch_JR' ) ) :

/**
 * Core class.
 */
final class GoFetch_JR {

	/**
	 * @var The plugin version.
	 */
	public $version = '2.2.4';

	/**
	 * @var The plugin slug.
	 */
	public $slug = 'go-fetch-jobs';

	/**
	 * @var The plugin post type.
	 */
	public $post_type = 'goft_jobs_schedule';

	/**
	 * @var The expected theme post type.
	 */
	public $parent_post_type = 'job_listing';

	/**
	 * @var The expected theme post type.
	 */
	public $theme_plugin = 'JobRoller theme';

	/**
	 * @var The log messages limit.
	 */
	public $log_limit = 10;

	/**
	 * @var The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Main 'Go Fetch Jobs' Instance.
	 *
	 * Ensures only one instance is loaded or can be loaded.
	 *
	 * @see GoFetch_Jobs()
	 *
	 * @return GoFetch_Jobs - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->define_constants();
		$this->init_hooks();

		do_action( 'gofetch_jobs_loaded' );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		// Framework Core.
		require_once 'includes/framework/load.php';

		// Common dependencies.
		require_once 'includes/settings.php';
		require_once 'includes/class-gofetch-html-table.php';
		require_once 'includes/class-gofetch-helper.php';
		require_once 'includes/class-gofetch-importer.php';

		// RSS providers.
		require_once 'includes/class-gofetch-rss-providers.php';

		// Specific.
		if ( ! $this->is_request( 'admin' ) ) {
			// Frontend.
			require_once 'includes/jobroller/class-gofetch-jobroller.php';
		}
		require_once 'includes/jobroller/class-gofetch-jobroller-capabilities.php';
		require_once 'includes/jobroller/admin/class-gofetch-jobroller-importer.php';

		// External.
		require_once 'includes/external/OpenGraph.php';

		// Premium.
		if ( gofj_jr_fs()->is__premium_only() ) {
			require_once dirname( __FILE__ ) . '/includes/premium/load__premium_only.php';
		}

		if ( $this->is_request( 'admin' ) ) {
			require_once 'includes/admin/class-gofetch-admin.php';

			if ( class_exists('GoFetch_JR_Admin_Settings') ) {
				require_once 'includes/jobroller/admin/class-gofetch-jobroller-settings.php';
			}

		}

	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {
		$this->define( 'GOFT_JOBS_PLUGIN_FILE', __FILE__ );
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		add_action( 'init', array( $this, 'init' ), 0 );
	}

	/**
	 * Init plugin when WordPress Initializes.
	 */
	public function init() {

		add_action( 'init', array( $this, 'maybe_install' ), 99 );

		// Before init action.
		do_action( 'before_gofetch_jobs_init' );

		// Set up localization.
		$this->load_plugin_textdomain();

		// Init action.
		do_action( 'gofetch_jobs_init' );
	}

	/**
	 * Load Localization files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales are found in:
	 * - WP_LANG_DIR/plugins/gofetch-jobs-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'gofetch-jobs' );

		load_textdomain( 'gofetch-jobs', WP_LANG_DIR . '/plugins/gofetch-jobs-' . $locale . '.mo' );
		load_plugin_textdomain( 'gofetch-jobs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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
	 * string $type ajax, frontend or admin
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

	/*
	 * On deactivation, remove plugin related stuff.
	 */
	public function deactivate() {
		wp_clear_scheduled_hook('goft_jobs_scheduler');
	}

	/*
	 * On activate, set default stuff.
	 */
	public static function activate() {
		add_option( 'goft-jobs-activated', 1 );
	}

	/**
	 * Install any sample options.
	 */
	public function maybe_install() {
		global $goft_jobs_options;

		$theme = wp_get_theme();

		if ( 'jobroller' !== $theme->template ) {
			add_option( 'goft-jobs-error', 1 );
			return;
		} else {
			delete_option( 'goft-jobs-error' );
		}

		$example = 'rss-example-theguardian-wordpress-fulltime-jobs';
		if ( ! is_admin() || get_option( 'goft-jobs-samples-installed' ) || ! get_option( 'goft-jobs-activated' ) ) {
			return;
		}

		add_option( 'goft-jobs-samples-installed', 1 );
		delete_option( 'goft-jobs-activated' );

		// Skip sample install if there are already templates set.
		if ( ! empty( $goft_jobs_options->templates ) ) {
			return;
		}

		// Pre configure a default template.

		// Provider.
		$feed_url = 'https://jobs.theguardian.com/jobsrss/?keywords=wordpress%20full-time&radialtown=&';

		// Taxonomy.
		$job_type = apply_filters( 'goft_jobs_default_value_for_taxonomy', '', APP_TAX_TYPE, 'full-time' );

		// Meta.
		$expires  = date( 'Y-m-d', strtotime( current_time('mysql') . ' +' . absint( get_option( 'job_manager_submission_duration' ) ) . ' days' ) );

		$templates = $goft_jobs_options->templates;

		$templates[ $example ] = array(
			'rss_feed_import' => $feed_url,
			'fetch_images'    => 1,
			'tax_input'       => array(
				APP_TAX_TYPE => $job_type,
			),
		 	'meta' => array(
		    	'_job_expires' => $expires,
		    ),
		    'source' => array(
				'name'    => 'Guardian Jobs',
				'website' => 'jobs.theguardian.com',
				'logo'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/The_Guardian.svg/2000px-The_Guardian.svg.png',
			),
		);

		$goft_jobs_options->templates = $templates;

		// Add a sample schedule.

		$post = array(
			'post_title'  => 'Daily Import Example [WordPress Full-Time Jobs] (theguardian.com)',
			'post_type'   => $this->post_type,
			'post_status' => 'pending',
			'meta_input'  => array(
				'_goft_jobs_template'      => $example,
				'_goft_jobs_period'        => 'custom',
				'_goft_jobs_period_custom' => 15,
				'_goft_jobs_limit'         => 5,
				'_goft_jobs_cron'          => 'daily',
			),
		);
		wp_insert_post( $post );
	}

	/**
	 * Get the plugin URL.
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

}

endif;

/**
 * Returns the main instance of 'Go Fetch Jobs'.
 *
 * @return GoFetch_Jobs instance.
 */
function GoFetch_JR() {
	return GoFetch_JR::instance();
}
