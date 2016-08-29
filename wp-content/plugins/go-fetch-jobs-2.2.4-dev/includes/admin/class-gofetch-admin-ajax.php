<?php
/**
 * Contains AJAX admin related callbacks.
 *
 * @package GoFetchJobs/Admin/Ajax
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Ajax class.
 */
class GoFetch_JR_Ajax {

	public function __construct() {
		add_action( 'wp_ajax_goft_jobs_load_template_content', array( $this, 'load_template_content' ) );
		add_action( 'wp_ajax_goft_jobs_update_templates_list', array( $this, 'update_templates_list' ) );
		add_action( 'wp_ajax_goft_jobs_load_provider_info', array( $this, 'load_provider_info' ) );
		add_action( 'wp_ajax_goft_jobs_import_feed', array( $this, 'import_feed' )) ;
		add_action( 'wp_ajax_goft_jobs_toggle_settings', array( $this, 'toggle_settings' )) ;
	}

	/**
	 * Dynamically outputs the pre-saved fields settings for a given template.
	 */
	public function load_template_content() {
		global $goft_jobs_options;

		if ( ! wp_verify_nonce( $_POST['_ajax_nonce'], 'goft_jobs_nonce' ) ) {
			die(0);
		}

		// User selected 'none' on the template list dropdown.
		if ( empty( $_POST['template'] ) ) {

			die(0);

		} else {

			// User has selected a template.

			$template_name = sanitize_text_field( $_POST['template'] );
			$template_name = GoFetch_JR_Helper::remove_slashes( $template_name );

			$templates = GoFetch_JR_Helper::get_sanitized_templates();

			$template_settings = $templates[ $template_name ];

			$query_args = apply_filters( 'goft_jobs_template_setup', $template_settings, $template_name );

		}

		echo json_encode( $query_args );
		die(1);
	}

	/**
	 * Dynamically update the templates list.
	 */
	public function update_templates_list() {
		global $goft_jobs_options;

		if (  ! wp_verify_nonce( $_POST['_ajax_nonce'], 'goft_jobs_nonce' ) ) {
			die(0);
		}

		$templates = GoFetch_JR_Helper::get_sanitized_templates();

		echo json_encode( array(
			'templates' => array_keys( $templates ),
		) );

		die(1);
	}

	/**
	 * Load a given providers info.
	 */
	public function load_provider_info() {

		if ( ! wp_verify_nonce( $_POST['_ajax_nonce'], 'goft_jobs_nonce' ) ) {
			die(0);
		}

		// User didn't select a provider
		if ( empty( $_POST['provider'] ) ) {

			die(0);

		} else {

			// User has selected a provider.

			$provider = sanitize_text_field( $_POST['provider'] );

			$data = array(
				'provider' => GoFetch_JR_RSS_Providers::get_providers( $provider ),
				'setup'    => GoFetch_JR_RSS_Providers::setup_instructions_for( $provider )
			);

		}

		echo json_encode( $data );
		die(1);
	}

	/**
	 * Dynamically import an RSS feed.
	 */
	public function import_feed() {

		if ( ! wp_verify_nonce( $_POST['_ajax_nonce'], 'goft_jobs_nonce' ) ) {
			die(0);
		}

		$url         = wp_strip_all_tags( $_POST['url'] );
		$load_images = ( 'false' !== $_POST['load_images'] );

		$items = GoFetch_JR_Importer::import_feed( $url, $load_images, $cache = true );

		if ( ! $items || is_wp_error( $items ) ) {
			echo json_encode( array(
				'error' => is_wp_error( $items ) ? $items->get_error_message() : __( 'This feed does not appear to be valid.', 'gofetch-jobs' ),
			) );
			die(0);
		}

		if ( ! is_array( $items ) ) {
			echo json_encode( array(
				'error' => __( 'Unknown error.', 'gofetch-jobs' ),
			) );
			die(0);
		}

		extract( $items );

		$args = array(
			'content_type' => GoFetch_JR()->parent_post_type,
		);

		$total_items = count( $items );

		unset( $items );

		if ( ! empty( $sample_item['logo_html'] ) ) {
			$sample_item['logo'] = $sample_item['logo_html'];
			unset( $sample_item['logo_html'] );
		}

		echo json_encode( array(
			'provider'    => $provider,
			'sample_item' => $total_items ? GoFetch_JR_Sample_Table::display( $args, $sample_item ) : '',
			'total_items' => $total_items,
		) );

		die(1);
	}

	/**
	 * Dynamically update toggle settings.
	 */
	public function toggle_settings() {

		if ( ! wp_verify_nonce( $_POST['_ajax_nonce'], 'goft_jobs_nonce' ) ) {
			die(0);
		}

		$toggle = wp_strip_all_tags( $_POST['toggle'] );

		$user_id = get_current_user_id();

		$value = get_user_meta( $user_id, 'bc_screen_options', true );
		$value['goft-settings-type'] = $toggle;

		update_user_meta( $user_id, 'bc_screen_options', $value, true );

		die(1);
	}

}

new GoFetch_JR_Ajax;
