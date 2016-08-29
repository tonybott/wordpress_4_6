<?php
/**
 * Provides additional meta capabilities for the custom post type.
 *
 * @package GoFetch/JobRoller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Capabilities class.
 */
class GoFetch_Jobs_Capabilites {

	function __construct() {
		add_filter( 'map_meta_cap', array( $this, 'map_post_capabilities' ), 99, 4 );
	}

	/**
	 * Additional meta capabilities.
	 */
	public function map_post_capabilities( $caps, $cap, $user_id, $args ) {

		switch( $cap ) {

			case 'apply_to_job':
				$post = get_post( $args[0] );

				$is_external = get_post_meta( $post->ID, '_goft_jobs_is_external', true );

				// Disable 'Apply Online' on external jobs.
				if ( $is_external ) {
					$caps[] = 'do_not_allow';
					break;
				}
				break;

			}

		return $caps;
	}

}

new GoFetch_Jobs_Capabilites;
