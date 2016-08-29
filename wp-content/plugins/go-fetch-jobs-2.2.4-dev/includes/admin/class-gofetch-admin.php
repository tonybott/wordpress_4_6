<?php
/**
 * Provides basic admin functionality.
 *
 * @package GoFetchJobs/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Base admin class.
 */
class GoFetch_JR_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {

		if ( get_option( 'goft-jobs-error' ) ) {
			add_action( 'admin_notices', array( $this, 'warnings' ) );
		}

		$this->hooks();
		$this->includes();

		if ( class_exists('GoFetch_JR_Guided_Tutorial') ) {
	        new GoFetch_JR_Guided_Tutorial;
	    }
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		require_once 'class-gofetch-admin-list.php';
		require_once 'class-gofetch-admin-sample-table.php';
		require_once 'class-gofetch-admin-settings.php';
		require_once 'class-gofetch-admin-ajax.php';
		require_once 'class-gofetch-guided-tutorial.php';
	}

	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 21 );
		add_action( 'admin_head', array( $this, 'admin_icon') );

	}

	/**
	 * Register admin JS scripts and CSS styles.
	 */
	public function register_admin_scripts( $hook ) {

		$ext = ( ! defined('SCRIPT_DEBUG') || ! SCRIPT_DEBUG ? '.min' : '' ) . '.js';

		wp_register_style(
			'fontello',
			GoFetch_JR()->plugin_url() . '/includes/admin/assets/font-icons/css/fontello.css'
		);

		// Selective load.
		if ( ! $this->load_scripts( $hook ) ) {
			return;
		}

		wp_register_script(
			'goft_jobs-settings',
			GoFetch_JR()->plugin_url() . '/includes/admin/assets/js/scripts' . $ext,
			array( 'jquery' ),
			GoFetch_JR()->version,
			true
		);

		wp_register_script(
			'select2-goft',
			GoFetch_JR()->plugin_url() . '/includes/admin/assets/select2/4.0.2/js/select2.min.js',
			array( 'jquery' ),
			GoFetch_JR()->version,
			true
		);

		wp_register_script(
			'select2-resize',
			GoFetch_JR()->plugin_url() . '/includes/admin/assets/select2/maximize-select2-height.min.js',
			array( 'select2-goft' ),
			GoFetch_JR()->version,
			true
		);

		wp_register_script(
			'validate',
			GoFetch_JR()->plugin_url() . '/includes/admin/assets/js/jquery.validate.min.js',
			array( 'jquery' ),
			GoFetch_JR()->version
		);

		$params = array(
			'sensor'    => false,
			'libraries' => 'places',
		);

		$google_api = add_query_arg( $params, 'https://maps.googleapis.com/maps/api/js' );

		wp_register_script(
			'gmaps',
			$google_api,
			array( 'jquery' ),
			GoFetch_JR()->version
		);

		wp_register_script(
			'geocomplete',
			GoFetch_JR()->plugin_url() . '/includes/admin/assets/js/jquery.geocomplete.min.js',
			array( 'jquery', 'gmaps' ),
			GoFetch_JR()->version
		);

		wp_register_style(
			'goft_jobs',
			GoFetch_JR()->plugin_url() . '/includes/admin/assets/css/styles.css'
		);

		wp_register_style(
			'select2-goft',
			GoFetch_JR()->plugin_url() . '/includes/admin/assets/select2/4.0.2/css/select2.min.css'
		);

	}

	/**
	 * Enqueue registered admin JS scripts and CSS styles.
	 */
	public function enqueue_admin_scripts( $hook ) {

		wp_enqueue_style( 'fontello' );

		// Selective load.
		if ( ! $this->load_scripts( $hook ) ) {
			return;
		}

		wp_enqueue_script( 'goft_jobs-settings' );
		wp_enqueue_script( 'select2-goft' );
		wp_enqueue_script( 'select2-resize' );
		wp_enqueue_script( 'validate' );

		wp_enqueue_script( 'gmaps' );
		wp_enqueue_script( 'geocomplete' );

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_style( 'goft_jobs' );
		wp_enqueue_style( 'select2-goft' );
		wp_enqueue_style( 'goft_jobs-jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );

		wp_localize_script( 'goft_jobs-settings', 'goft_jobs_admin_l18n', array(
			'ajaxurl'               => admin_url('admin-ajax.php'),
			'ajax_nonce'            => wp_create_nonce('goft_jobs_nonce'),
			'ajax_save_nonce'       => wp_create_nonce( GoFetch_JR()->slug ),
			'date_format'           => get_option('date_format'),

			// Messages.
			'msg_jobs_found'        => __( 'Total Jobs in Feed', 'gofetch-jobs' ),
			'msg_simple'            => __( 'Simple...', 'gofetch-jobs' ),
			'msg_advanced'          => __( 'Edit...', 'gofetch-jobs' ),
			'msg_specify_valid_url' => __( 'Please specify a valid URL to import the feed.', 'gofetch-jobs' ) ,
			'msg_invalid_feed'      => __( 'Could not load feed.', 'gofetch-jobs' ),
			'msg_no_jobs_found'     => __( 'No Jobs Found in Feed.', 'gofetch-jobs' ),
			'msg_template_missing'  => __( 'Please specify a template name.', 'gofetch-jobs' ),
			'msg_template_saved'    => __( 'Template Settings Saved.', 'gofetch-jobs' ),
			'msg_save_error'        => __( 'Save failed. Please try again later.', 'gofetch-jobs' ),
			'msg_rss_copied'        => __( 'Feed URL copied', 'gofetch-jobs' ),

			'title_close'           => esc_attr( __( 'Close/Hide', 'gofetch-jobs' ) ),

			'label_yes'             => __( 'Yes', 'gofetch-jobs' ),
			'label_no'              => __( 'No', 'gofetch-jobs' ),
			'label_providers'       => __( 'Choose an RSS Provider . . .', 'gofetch-jobs' ),
			'label_templates'       => __( 'Choose a Template . . .', 'gofetch-jobs' ),

			'cancel_feed_load'      => __( 'Cancel', 'gofetch-jobs' ),

			'default_query_args'    => GoFetch_JR_RSS_Providers::valid_item_tags(),
	    ) );

	}

	/**
	 * Criteria used for the selective load of scripts/styles.
	 */
	private function load_scripts( $hook = '' ) {

	 	if ( empty( $_GET['post_type'] ) && empty( $_GET['post'] ) && 'toplevel_page_' . GoFetch_JR()->slug !== $hook ) {
			return false;
	    }

		$post_type = '';

		if ( ! empty( $_GET['post'] ) ) {
			$post = get_post( (int) $_GET['post'] );
			$post_type = $post->post_type;
		} elseif ( isset( $_GET['post_type'] ) ) {
			$post_type = $_GET['post_type'];
		}

		if ( $post_type !== GoFetch_JR()->post_type && 'toplevel_page_' . GoFetch_JR()->slug !== $hook ) {
			return false;
		}

		return true;
	}

	/**
	 * Use external font icons in dashboard.
	 */
	public function admin_icon() {
	   echo "<style type='text/css' media='screen'>
	   		#toplevel_page_" . GoFetch_JR()->slug . " div.wp-menu-image:before {
	   		font-family: Fontello !important;
	   		content: '\\e802';
	     }
	     	</style>";
	 }

	/**
	 * Admin notices.
	 */
	public function warnings() {
		echo scb_admin_notice( sprintf( __( 'The <strong>%1$s</strong> was not found. Please install it first to be able to use <strong>%2$s</strong>.', 'gofetch-jobs' ),  GoFetch_JR()->theme_plugin, 'Go Fetch Jobs' ), 'error' );
	}

	public static function limited_plan_warn() {

		$text = '';

		if ( gofj_jr_fs()->is_not_paying() ) {
			$tooltip = __( 'Not available on the Free plan.', 'gofetch-jobs' );
			$text = html( 'span class="dashicons dashicons-warning tip-icon bc-tip limitation" data-tooltip="' . $tooltip . '"', '&nbsp;' );
		}
		return $text;
	}

}

new GoFetch_JR_Admin();
