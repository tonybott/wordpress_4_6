<?php
/**
 * Sets up the write panels used by the schedules (custom post types).
 *
 * @package GoFetch/Admin/Meta Boxes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Schedules meta boxes base class.
 */
class GoFetch_JR_Schedule_Meta_Boxes{

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'rename_meta_boxes' ), 20 );
		add_action( 'admin_init', array( $this, 'add_meta_boxes' ), 30 );
	}

	/**
	 * Removes Meta boxes.
	 */
	public function remove_meta_boxes() {

		$remove_boxes = array( 'authordiv' );

		foreach ( $remove_boxes as $id ) {
			remove_meta_box( $id, GoFetch_JR()->post_type, 'normal' );
		}

	}

	/**
	 * Renames Meta boxes.
	 */
	public function rename_meta_boxes() {
		add_meta_box( 'authordiv', __( 'Job Listers', 'gofetch-jobs' ) , array( $this, 'post_author_meta_box' ), GoFetch_JR()->post_type, 'side', 'low' );
	}

	/**
	 * Add Meta boxes.
	 */
	public function add_meta_boxes() {
		new GoFetch_JR_Schedule_Import_Meta_Box;
		new GoFetch_JR_Schedule_Cron_Meta_Box;
		new GoFetch_JR_Schedule_Period_Meta_Box;
		new GoFetch_JR_Schedule_Logger_Meta_Box;
	}

	/**
	 * Display custom form field with list of job listers.
	 */
	public function post_author_meta_box( $post ) {
		global $user_ID;

	?>
	<label class="screen-reader-text" for="post_author_override2"><?php _e( 'Job Lister', 'gofetch-jobs' ); ?></label>
	<?php
		$job_listers_raw = get_users( array( 'role' => 'job_lister' ) );
		$admins_raw      = get_users( array( 'role' => 'administrator' ) );

		$job_listers = apply_filters( 'goft_jobs_job_listers', array_merge( $job_listers_raw, $admins_raw ) );

		$include = array();

		foreach( $job_listers as $user ) {
			$include[] = $user->ID;
		}

		wp_dropdown_users(
			array(
				'name'     => 'post_author_override',
				'include'  => implode( ',', $include ),
				'show'     => 'display_name_with_login',
				'selected' => empty( $post->ID ) ? $user_ID : $post->post_author,
			)
		);

	}

}

/**
 * The import settings meta box for the schedules.
 */
class GoFetch_JR_Schedule_Import_Meta_Box extends scbPostMetabox {

	/**
	 * Constructor.
	 */
	public function __construct() {

		parent::__construct( 'goft_jobs-export', __( 'Import Template', 'gofetch-jobs' ), array(
			'post_type' => GoFetch_JR()->post_type,
			'context'   => 'normal',
			'priority'  => 'high'
		) );

	}

	public function before_form( $post ) {
		echo __( 'Select the pre-defined template to use in the import process. The process will use the selected template setup for importing jobs to your database.', 'gofetch-jobs' );
	}

	/**
	 * Meta box custom meta fields.
	 */
	public function form_fields() {
		global $goft_jobs_options;

		if ( empty( $goft_jobs_options->templates ) ) {
			$templates = array( '' => __( 'No templates found', 'gofetch-jobs' ) );
		} else {
			$templates = GoFetch_JR_Helper::get_sanitized_templates();
			$templates = array_keys( $templates );
		}

		return array(
			array(
				'title'   => __( 'Template Name', 'gofetch-jobs' ),
				'type'    => 'select',
				'name'    => '_goft_jobs_template',
				'choices' => $templates,
				'desc'    => sprintf( __( '<a href="%s">Create Template</a>', 'gofetch-jobs' ), esc_url( add_query_arg( 'page', GoFetch_JR()->slug, 'admin.php' ) ) ),

			),
		);

	}

}

/**
 * The cron settings meta box for the schedules.
 */
class GoFetch_JR_Schedule_Cron_Meta_Box extends scbPostMetabox {

	/**
	 * Constructor.
	 */
	public function __construct() {

		parent::__construct( 'goft_jobs-time', __( 'Schedule', 'gofetch-jobs' ), array(
			'post_type' => GoFetch_JR()->post_type,
			'context'   => 'side',
		) );

	}

	public function after_form( $post ) {
		echo __( '<strong>Daily:</strong> Runs every day / <strong>Weekly:</strong> Runs every monday / <strong>Monthly:</strong> Runs on the 1st of each month', 'gofetch-jobs' );
	}

	/**
	 * Meta box custom meta fields.
	 */
	public function form_fields() {

		return array(
			array(
				'title'   => __( 'Run Once Every...', 'gofetch-jobs' ),
				'type'    => 'select',
				'name'    => '_goft_jobs_cron',
				'choices' => array(
					'daily'   => __( 'Day', 'gofetch-jobs' ),
					'weekly'  => __( 'Week', 'gofetch-jobs' ),
					'monthly' => __( 'Month', 'gofetch-jobs' ),
				),
			),
		);

	}

}

/**
 * The time period meta box for the schedules.
 */
class GoFetch_JR_Schedule_Period_Meta_Box extends scbPostMetabox {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'goft_jobs-content-period', __( 'Content', 'gofetch-jobs' ), array(
			'post_type' => GoFetch_JR()->post_type,
			'context'   => 'normal',
		) );
	}

	public function before_form( $post ) {
		echo __( 'Limit the content being imported by choosing the time period that should match the jobs being imported and the number of jobs to import every time this scheduled import runs.', 'gofetch-jobs' );
		echo ' ' . __( 'You can also limit the jobs being imported to those who contain a pre-set list of keywords.', 'gofetch-jobs' );
	}

	/**
	 * Meta box custom meta fields.
	 */
	public function form_fields() {

		return array(
			array(
				'title'   => __( 'Jobs From...', 'gofetch-jobs' ),
				'type'    => 'select',
				'name'    => '_goft_jobs_period',
				'choices' => array(
					'today'  => __( 'Today', 'gofetch-jobs' ),
					'custom' => __( 'Custom', 'gofetch-jobs' ),
				),
				'extra' => array( 'id' => '_goft_jobs_period' ),
			),
			array(
				'title' => __( 'Last...', 'gofetch-jobs' ),
				'type'  => 'text',
				'name'  => '_goft_jobs_period_custom',
				'extra' => array(
					'id'    => '_goft_jobs_period_custom',
					'class' => 'small-text',
				),
				'desc' => __( 'days', 'gofetch-jobs' ),
			),
			array(
				'title' => __( 'Limit', 'gofetch-jobs' ),
				'type'  => 'text',
				'name'  => '_goft_jobs_limit',
				'extra' => array(
					'class'     => 'small-text',
					'maxlength' => 5,
				),
				'desc' => __( 'job(s)', 'gofetch-jobs' ) .
						  '<br/><br/>' .__( 'Leave empty to import all jobs found.', 'gofetch-jobs' ),
			),
			array(
				'title' => __( 'Keywords', 'gofetch-jobs' ),
				'type'  => 'text',
				'name'  => '_goft_jobs_keywords',
				'extra' => array(
					'class'       => 'large-text',
					'placeholder' => 'e.g: design, sales, marketing',
				),
				'desc'  => __( 'Comma separated list of keywords.', 'gofetch-jobs' ),
			),
		);

	}

}

/**
 * Displays a list of messages for the current schedule.
 */
class GoFetch_JR_Schedule_Logger_Meta_Box extends scbPostMetabox {

	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct( 'goft_jobs-import-log', __( 'Import Log', 'gofetch-jobs' ), array(
			'post_type' => GoFetch_JR()->post_type,
			'context'   => 'normal',
		) );
	}

	public function display( $post ) {
		$table = new GoFetch_JR_Log_Message_Table( new BC_Framework_Logger( $post->ID ) );
		$table->display();
	}
}

/**
 * The table class for the log messages.
 */
class GoFetch_JR_Log_Message_Table  {

	protected $log;

	public function __construct( BC_Framework_Log $log ){
		$this->log = $log;
	}

	public function display() {

		$log_limit = GoFetch_JR()->log_limit;

		$this->admin_style();
		$messages = $this->log->get_log();

		if ( ! $messages ) {
			echo '<tr><td colspan="3">' . __( 'Move along. Nothing to see here yet.', 'gofetch-jobs' ) . '</td></tr>';
			return;
		}

		echo '<table class="gofetch-message-log widefat">';
		echo '<tr>
			  <th>' . __( 'Date', 'gofetch-jobs' ) . '</th>
			  <th>' . __( 'Stats', 'gofetch-jobs' ) . '</th>
			  <th>' . __( 'Status', 'gofetch-jobs' ) . '</th>
			  </tr>';

	  	$messages = array_reverse( $messages );

		foreach( $messages as $data ) {
			echo '<tr class="' . esc_attr( $data['type'] ) . '">';
			echo '<td><span class="timestamp" >' . $data['time'] . '</span></td>';
			echo '<td><span class="message" >' . ( is_array( $data['message'] ) ? self::get_stats_message( $data['message'] ) : $data['message'] ) . '</span></td>';
			echo '<td><span class="type" >' . $this->get_type_label( $data['type'] ) . '</span></td>';
			echo '</tr>';
		}

		echo '</table>';

		echo html( 'p', html( 'small', html( 'em', sprintf( __( '<strong>Note:</strong> The log keeps only the last %d import stats.', 'gofetch-jobs' ), $log_limit ) ) ) );
	}

	/**
	 * Retrieves the label that corresponds to the message type.
	 */
	protected function get_type_label( $type ) {

		$labels = array(
			'success' => __( 'Success', 'gofetch-jobs' ),
			'error'   => __( 'Error', 'gofetch-jobs' ),
		);

		if ( empty( $labels[ $type ] ) ) {
			return;
		}
		return $labels[ $type ];
	}

	/**
	 * Retrieves log messages stored as a stats array.
	 */
	public static function get_stats_message( $data ) {

		$message = '';

		foreach( $data as $type => $total ) {
			$message .= self::formatted_stats_message( $type, $total );
		}
		return $message;
	}

	/**
	 * Retrieves a formated stats message considering the stats type.
	 */
	protected static function formatted_stats_message( $type, $total ) {

		switch ( $type ) {

			case 'imported':
				$icon  = 'icon icon-download-cloud';
				$desc = __( 'Jobs Imported', 'gofetch-jobs' );
				break;

			case 'limit':
				$icon  = 'icon icon-to-end-alt';
				$desc = __( 'Skipped Jobs (discarded - enforced import limit)', 'gofetch-jobs' );
				break;

			case 'duplicates':
				$icon  = 'icon icon-docs';
				$desc = __( 'Duplicate Jobs (discarded - already exist in DB)', 'gofetch-jobs' );
				break;

			case 'excluded':
				$icon = 'icon icon-attention';
				$desc = __( 'Excluded Jobs (discarded - unmatched keywords)', 'gofetch-jobs' );
				break;

			default:
				$icon = 'icon icon-rss';
				$desc = __( 'Jobs in RSS Feed', 'gofetch-jobs' );
				break;
		}

		return sprintf( '<span class="log-stats" title="%1$s">%2$s %3$s</span>', esc_attr( $desc ), '<span class="' . esc_attr( $icon ) . '"></span>', $total );
	}

	/**
	 * Custom CSS for the meta box.
	 */
	protected function admin_style() {
?>
		<style type="text/css">
			.gofetch-message-log.widefat {
				border: 0;
			}
			.gofetch-message-log th {
				font-weight: bold;
				padding-left: 3px;
			}
			.gofetch-message-log th {
				border-bottom: 1px solid rgba(0, 0, 0, 0.18);
			}
			.gofetch-message-log td {
				border-top: 1px solid #F3F3F3;
				padding: 5px;
			}
			.gofetch-message-log td:first-child {
				width: 200px;
			}
			.gofetch-message-log .major .message {
				font-weight: bold;
			}
			.gofetch-message-log .minor .timestamp {
				color: #999;
			}
			.gofetch-message-log .info .timestamp {
				display: none;
			}
			.gofetch-message-log .log-stats {
			    padding-right: 15px;
			}
			.gofetch-message-log.widefat .log-stats:first-of-type {
				min-width: 40px;
				display: inline-block;
			}
			.gofetch-message-log.widefat .log-stats:nth-child(2) {
				margin-left: 20px;
			}
			@media screen and (max-width: 782px) {
				.gofetch-message-log.widefat .log-stats {
					display: block;
				}
				.gofetch-message-log.widefat .log-stats:nth-child(2) {
					margin-left: 0;
				}
			}
		</style>
	<?php
	}

}

new GoFetch_JR_Schedule_Meta_Boxes;
