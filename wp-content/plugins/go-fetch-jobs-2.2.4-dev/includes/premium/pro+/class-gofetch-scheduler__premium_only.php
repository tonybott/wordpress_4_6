<?php
/**
 * Registers and handles the scheduler custom post type: 'goft_jobs_schedule'.
 *
 * @package GoFetchJobs/Scheduler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( gofj_jr_fs()->is_plan__premium_only( 'proplus', true ) ) {

/**
 * Scheduler core class.
 */
class GoFetch_JR_Scheduler {

	/**
	 * Constructor.
	 */
	function __construct() {
		add_action( 'admin_notices', array( $this, 'warnings' ) );
		add_action( 'init', array( $this, 'register_post_types' ), 99 );
		add_action( 'init', array( $this, 'maybe_create_schedule' ), 100 );
		add_action( 'admin_head', array( $this, 'schedule_list_style' ) );
	}

	/**
	 * Register the custom post type for the import scheduler.
	 */
	public function register_post_types() {

		$labels = array(
			'name'               => __( 'Schedules', 'gofetch-jobs' ),
			'singular_name'      => __( 'Schedule', 'gofetch-jobs' ),
			'all_items'          => __( 'Schedules', 'gofetch-jobs' ),
			'add_new'            => __( 'Add New Schedule', 'gofetch-jobs' ),
			'add_new_item'       => __( 'Add New Schedule', 'gofetch-jobs' ),
			'edit_item'          => __( 'Edit Schedule', 'gofetch-jobs' ),
			'new_item'           => __( 'New Schedule', 'gofetch-jobs' ),
			'view_item'          => __( 'View Schedule', 'gofetch-jobs' ),
			'search_items'       => __( 'Search Schedule', 'gofetch-jobs' ),
			'not_found'          => __( 'No Schedule found', 'gofetch-jobs' ),
			'not_found_in_trash' => __( 'No Schedule found in Trash', 'gofetch-jobs' ),
			'menu_name'          => __( 'Go Fetch Jobs', 'gofetch-jobs' ),
		 );

		 $args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => GoFetch_JR()->slug,
			'query_var'          => true,
			'rewrite'            => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 100,
			'supports'           => array( 'title', 'author' ),
		 );

		 register_post_type( GoFetch_JR()->post_type, $args );
	}


	// Helpers.

	/**
	 * Create a single cron job for all schedules when the user adds a schedule for the first time.
	 */
	public function maybe_create_schedule() {

		// Create the new cron schedule.
		if ( ! wp_next_scheduled('goft_jobs_scheduler') ) {
			wp_schedule_event( time(), 'daily', 'goft_jobs_scheduler' );
		}

	}

	/**
	 * The main schedule callback that manages all the user schedules.
	 */
	public static function scheduler_manager() {

		$args = array(
			'post_type' => GoFetch_JR()->post_type,
			'status'    => 'publish',
			'nopaging'  => true,
		);

		$schedules = get_posts( $args );

		$i = 0;

		foreach( $schedules as $post ) {

			$schedule_name = sprintf( "goft_jobs_sch_%s", $post->post_name );

			$meta = get_post_custom( $post->ID );

			// Recurrence: daily, weekly, monthly, etc.
			$recurrence = $meta['_goft_jobs_cron'][0];

			$month_day = date('d');
			$week_day = date('w');

			switch( $recurrence ) {
				case 'monthly':
					$run = '01' == $month_day;
					break;

				case 'daily':
					$run = true;
					break;

				case 'weekly':
					$run = '01' === $week_day;
					break;

				default:
					$run = false;
			}

			if ( $run ) {

				// Sleep for 30 seconds before starting a new schedule.
				if ( $i++ > 0 ) {
					$sleep = apply_filters( 'goft_jobs_schedules_interval_sleep', 30 );

					sleep( $sleep );
				}

				self::_run_schedule( $post->ID, $schedule_name, $meta );
			}

		}

	}

	/**
	 * Runs a schedule, exports and sends any CSV's by email.
	 */
	private static function _run_schedule( $post_ID, $schedule_name, $meta ) {
		global $goft_jobs_options;

		$post = get_post( $post_ID );

		$template_name = $meta['_goft_jobs_template'][0];
		$period        = $meta['_goft_jobs_period'][0];
		$limit         = $meta['_goft_jobs_limit'][0];
		$keywords      = $meta['_goft_jobs_keywords'][0];
		$post_author   = $post->post_author;

		switch( $period ) {

			case 'custom':
				$custom_period = $meta['_goft_jobs_period_custom'][0];
				$from_date     = strtotime( sprintf( "-%s days", $custom_period ) );
				$to_date       = current_time('timestamp');
				break;

			default:
				$from_date = current_time('timestamp');
				$to_date   = current_time('timestamp');

		}

		$from_date = date( 'Y-m-d 00:00:00', $from_date );
		$to_date   = date( 'Y-m-d 00:00:00', $to_date );

		$log_type = 'success';

		$templates = GoFetch_JR_Helper::get_sanitized_templates();

		if ( empty( $templates[ $template_name ] ) ) {
			$message  = __( 'Template not found!', 'gofetch-jobs' );
			$log_type = 'error';
		} else {

			// Get all the saved parameters for the current template.
			$defaults = $templates[ $template_name ];

			$args = compact( 'from_date', 'to_date', 'limit', 'keywords', 'post_author', 'fetch_images' );

			// Merge the default parameters with new parameters defined on each schedule.
			$params = wp_parse_args( $args, $defaults );

			// Import the data.

			$items = GoFetch_JR_Importer::import_feed( $params['rss_feed_import'], $params['fetch_images'], $cache = false );

			extract( $items );

			if ( ! empty( $items ) ) {
				$results = GoFetch_JR_Importer::import( $items, $params );

				extract( $results );

				if ( ! is_wp_error( $total ) ) {
					$message = $results;
				} else {
					$message  = sprintf( __( 'Error importing data - %s', 'gofetch-jobs' ), $imported );
					$log_type = 'error';
				}

			} else {
				$message = __( 'No jobs found in feed', 'gofetch-jobs' );
			}

		}

		$logger = new BC_Framework_Logger( $post_ID );
		$logger->log( $message, $log_type, GoFetch_JR()->log_limit );

		unset( $items );

		update_post_meta( $post_ID, '_scheduler_log_timestamp', current_time('mysql') );
		update_post_meta( $post_ID, '_scheduler_log_status', $message );
	}

	/**
	 * CSS Styles for the schedule list page.
	 */
	public function schedule_list_style() {
		$screen = get_current_screen();

		if ( 'edit-' . GoFetch_JR()->post_type !== $screen->id ) {
			return;
		}
?>
		<style type="text/css">
			.log-stats {
			    padding-right: 5px;
			}
		</style>
<?php
	}

	/**
	 * Admin notice for schedules with missing template names.
	 */
	public function warnings() {
		global $post;

		if ( empty( $_GET['post'] ) || empty( $post ) || GoFetch_JR()->post_type !== $post->post_type ) {
			return;
		}

		if ( ! get_post_meta( $post->ID, '_goft_jobs_template', true ) ) {
			echo scb_admin_notice( __( "NOTE: You haven't selected a template for this schedule. It will remain inactive until you assign an existing template.", 'gofetch-jobs' ), 'error' );
		}
	}

}

new GoFetch_JR_Scheduler;

}