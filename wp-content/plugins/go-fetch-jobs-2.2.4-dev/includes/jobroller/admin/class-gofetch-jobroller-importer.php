<?php
/**
 * Specific import code for JobRoller.
 *
 * @package GoFetch/JobRoller
 */

class GoFetch_JR_Specific_Import extends GoFetch_JR_Importer {

	public function __construct() {
		add_filter( 'goft_jobs_item_meta_value', array( $this, 'replace_item_meta_placeholders' ), 10, 5 );
		add_filter( 'goft_jobs_item_meta_value', array( $this, 'replace_item_meta' ), 11, 5 );
		add_filter( 'get_post_metadata', array( $this, 'maybe_clear_meta_value' ), 15, 4 );
	}

	/**
	 * Retrieves the custom meta fields/known fields key/value pair mappings.
	 */
	public static function meta_mappings() {

		$mappings = array(
			'_jr_address'       => 'location',
			'_Company'          => 'company',
			'_company_logo'     => 'logo',
			'_jr_geo_latitude'  => 'latitude',
			'_jr_geo_longitude' => 'longitude',
		);

		return $mappings;
	}

	/**
	 * Replaces string placeholders with valid data on a given meta key.
	 */
	public function replace_item_meta_placeholders( $meta_value, $meta_key, $item, $post_id, $params ) {

		switch ( $meta_key ) {

			case '_how_to_apply':

				// Placeholder variables that can be used to dynamically fill in the 'Apply To' meta value.
				$find = array( '/%external_apply_to_url%/i' );

				// Replace the placeholder link with the final link.
				$replace = self::add_query_args( $params, $item['link'] );

				$meta_value = preg_replace( $find, (array) $replace, $meta_value );

				break;

		}

		return $meta_value;
	}

	/**
	 * Replaces string placeholders with valid data on a given meta key.
	 */
	public function replace_item_meta( $meta_value, $meta_key, $item, $post_id, $params ) {

		switch ( $meta_key ) {

			case 'geo_short_address';
			case 'geo_address';
			case 'geo_country';
			case 'geo_short_address_country';
			case '_jr_geo_latitude';
			case '_jr_geo_longitude':

				// Avoid deleting existing geo data set through the '_jr_address' meta key.
				if ( ! $meta_value ) {
					$meta_value = get_post_meta( $post_id, $meta_key, true );
				}
				break;

			case '_jr_address':

				if ( ! $meta_value) {
					break;
				}

				// Only for JobRoller 1.8.x.
				if ( function_exists('jr_update_post_geo_metadata') ) {
					$coord = GoFetch_JR_Helper::get_coordinates_by_location( $meta_value );

					$geocoded = false;

					if ( ! empty( $coord['latitude'] ) && ! empty( $coord['longitude'] ) ) {

						$geocoded = true;

						jr_update_post_geo_metadata( $post_id, $data = array(), $coord['latitude'], $coord['longitude'] );

					}

					// __LOG.

					// Maybe log location geocoding.
					$vars = array(
						'context'  => 'UPDATING LOCATION META',
						'location' => $meta_value,
						'coord'    => $coord,
						'geocoded' => $geocoded ? 'Yes' : 'No',
					);
					bc_framework_simple_log( $vars, ( defined('WP_GOFT_DEBUG_LOG') && WP_GOFT_DEBUG_LOG ) );

					// __END LOG.

				} else {
					update_post_meta( $post_id, 'geo_short_address', $meta_value );
				}
				break;

			case '_company_logo_id':

				// Attach a thumbnail only if the current item does not already provide a company URL.
				if ( empty( $item['logo'] ) && is_numeric( $meta_value ) ) {
					set_post_thumbnail( $post_id, (int) $meta_value );
				}
				break;

		}
		return $meta_value;
	}

	/**
	 * Clears some meta field values based on specific conditions.
	 *
	 * @todo: remove when JobRoller adds this option.
	 */
	public function maybe_clear_meta_value( $meta_value, $object_id, $meta_key, $single ) {
		global $jr_options;

		switch ( $meta_key ) {

			case '_how_to_apply':
				$is_external = get_post_meta( $object_id, '_goft_jobs_is_external', true );

				// Don't display the 'How To Apply' instructions if user is not logged in.
				if ( $is_external && $jr_options->apply_reg_users_only && ! is_user_logged_in() ) {
					$meta_value = '';
				}

				break;

		}

		return $meta_value;
	}

}

new GoFetch_JR_Specific_Import;
