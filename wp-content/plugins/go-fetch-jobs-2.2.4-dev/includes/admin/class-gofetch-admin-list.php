<?php
/**
 * Customizes listings on the custom post type admin page.
 *
 * @package GoFetchJobs/Admin/Listing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Admin class.
 */
class GoFetch_JR_Admin_List {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->customize_post_type_listing();
	}

	/**
	 * Hooks for customizing backend listings.
	 */
	public function customize_post_type_listing() {
		add_filter( 'manage_' . GoFetch_JR()->post_type . '_posts_columns', array( $this, 'manage_columns' ) , 11 );
		add_action( 'manage_' . GoFetch_JR()->post_type . '_posts_custom_column', array( $this, 'add_column_data' ), 10, 2 );
	}

	/**
	 * Output custom columns.
	 */
	public function manage_columns( $columns ) {

		$date = $columns['date'];

		unset( $columns['date'] );

		$columns['schedule']  = __( 'Schedule', 'gofetch-jobs' );
		$columns['time_span'] = __( 'Content Period', 'gofetch-jobs' );
		$columns['template']  = __( 'Template', 'gofetch-jobs' );
		$columns['date']      = $date;
		$columns['last_run']  = __( 'Last Run', 'gofetch-jobs' );

		return $columns;
	}

	/**
	 * Output custom columns data.
	 */
	public function add_column_data( $column_index, $post_id ) {

		switch ( $column_index ) {

			case 'schedule':
				$schedule = get_post_meta( $post_id, '_goft_jobs_cron', true );

				switch( $schedule ) {
					case 'daily':
						$schedule = __( 'Daily', 'gofetch-jobs' );
						break;

					case 'weekly':
						$schedule = __( 'Weekly', 'gofetch-jobs' );
						break;

					case 'monthly':
						$schedule = __( 'Monthly', 'gofetch-jobs' );
						break;
				}

				echo $schedule;
				break;

	        case 'time_span' :
	            $time_span = get_post_meta( $post_id, '_goft_jobs_period', true );
				if ( 'custom' == $time_span ) {
					$time_span = (int) get_post_meta( $post_id, '_goft_jobs_period_custom', true );
				}

				switch( $time_span ) {
					case 'today':
						$time_span = __( 'Today', 'gofetch-jobs' );
						break;

					default:
						$time_span = sprintf( __( 'Last %s %s', 'gofetch-jobs' ), $time_span, _n( 'day', 'days', $time_span, 'gofetch-jobs' ) );
						break;
				}
				echo $time_span;
	            break;

			case 'template':
				echo get_post_meta( $post_id, '_goft_jobs_template', true );
				break;

	        case 'last_run':
	        	$last_message = get_post_meta( $post_id, '_scheduler_log_status', true );
				echo get_post_meta( $post_id, '_scheduler_log_timestamp', true ) . '<br/>';
				echo ( is_array( $last_message ) ? GoFetch_JR_Log_Message_Table::get_stats_message( $last_message ) : $last_message );
	            break;

		}

	}

}

new GoFetch_JR_Admin_List();
