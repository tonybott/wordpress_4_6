<?php
/**
 * Provides public helper methods.
 *
 * @package GoFetch/Helper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Helper class.
 */
class GoFetch_JR_Helper {

	/**
	 * Retrieves a given field content type if recognized. Defaults to 'text' if unknown.
	 */
	public static function get_field_type( $field, $content_type = 'post' ) {

		$type = 'text';

		$fields = self::get_known_field_types();

		$fields = array_merge( $fields, self::get_field_types( $content_type ) );

		if ( $field && isset( $fields[ $field ] ) ) {
			$type = $field;
		}
		return $type;
	}

	/**
	 * Retrieve known field types for a know list of core fields.
	 *
	 * @uses apply_filters() Calls 'goft_jobs_known_field_types'
	 */
	public static function get_known_field_types() {

		$fields = array(
			'post_author' => array(
				'user' => __( 'User', 'gofetch-jobs' ),
				'text' => __( 'Text', 'gofetch-jobs' ),
			),
			'post_status' => array(
				'post_status' => __( 'Post Status', 'gofetch-jobs' ),
				'text'        => __( 'Text', 'gofetch-jobs' )
			),
		);
		return apply_filters( 'goft_jobs_known_field_types', $fields );
	}

	/**
	 * Retrieve all possible field types.
	 *
	 * @uses apply_filters() Calls 'goft_jobs_field_types'
	 *
	 */
	public static function get_field_types( $content_type = 'post' ) {

		$types = array(
			'text' => __( 'Text', 'gofetch-jobs' ),
			'date' => __( 'Date', 'gofetch-jobs' ),
			'user' => __( 'User', 'gofetch-jobs' ),
		);

		if ( 'user' != $content_type ) {
			$types['post_status'] = __( 'Post Status', 'gofetch-jobs' );
		}

		// get existing taxonomies
		$taxonomies = get_object_taxonomies( $content_type, 'objects' );

		// unset the 'post_status' taxonomy since it's empty
		unset( $taxonomies['post_status'] );

		foreach( $taxonomies as $tax ) {
			$types[ $tax->name ] = sprintf( __( "Taxonomy :: %s", 'gofetch-jobs' ), $tax->label );
		}

		return apply_filters( 'goft_jobs_field_types', $types, $content_type );
	}

	/**
	 * Matches a list of keywords against a string.
	 *
	 * @since 2.2
	 */
	public static function match_keywords( $text, $keywords ) {

		foreach( (array) $keywords as $keyword ) {

			if ( false !== stripos( $text, $keyword ) ) {
				return true;
			}

		}
		return false;
	}

	/**
	 * Removes extra slashes from a string.
	 *
	 * @since 2.2.3.
	 */
	public static function remove_slashes( $string ) {
	    $string = implode( '',explode( '\\',$string ) );
	    return stripslashes( trim( $string ) );
	}

	/**
	 * Retrieves the sanitized list of saved templates.
	 *
 	 * @since 2.2.3.
	 */
	public static function get_sanitized_templates() {
		global $goft_jobs_options;

		$templates = array();

		foreach( $goft_jobs_options->templates as $template => $data ) {
			$template = GoFetch_JR_Helper::remove_slashes( $template );
			$templates[ $template ] = $data ;
		}
		return $templates;
	}

	/**
	 * Retrieves the latitude and longitude for a given a location.
	 *
	 * Mirrors JobRoller 'jr_get_coordinates_by_location' but fixes a couple of issues.
	 *
	 * @since 2.2.4.
	 */
	public static function get_coordinates_by_location( $location ) {

		if ( ! function_exists('json_decode') ) {
			return false;
		}

		$address = _jr_get_geolocation_url( $location );

		// Make sure we do the call to the google API over 'https' - fix.
		$address = str_replace( 'http', 'https', $address );

		// __LOG.

		// Maybe log.
		$vars = array(
			'context'  => 'STARTING GEOCODING LOCATION',
			'location' => $location,
			'URL'      => $address,
		);
		bc_framework_simple_log( $vars, ( defined('WP_GOFT_DEBUG_LOG') && WP_GOFT_DEBUG_LOG ) );

		// __END LOG.

		$cached = get_transient( 'jr_geo_'.sanitize_title( $location ) );

		// Check for a empty location results in the cache var - fix.
		if ( empty( $cached['results'] ) ) {
			$cached = '';
		}

		if ( $cached ) {
			$address = $cached;
		} else {

			$address = json_decode( wp_remote_retrieve_body( wp_remote_get( $address ) ), true );
			if ( is_array( $address ) ) {
				set_transient( 'jr_geo_'.sanitize_title($location), $address, 60*60*24*7 ); // Cache for a week
			}

		}

		// __LOG.

		// Maybe log.
		$vars = array(
			'context'    => 'GEOCODING LOCATION',
			'result'     => $address,
			'from_cache' => $cached ? 'Yes' : 'No',
		);
		bc_framework_simple_log( $vars, ( defined('WP_GOFT_DEBUG_LOG') && WP_GOFT_DEBUG_LOG ) );

		// __END LOG.

		if ( ! empty( $address['results'][0] ) ) {
			// Put address info into a nice array format
			$address_array = array(
				'longitude' => $address['results'][0]['geometry']['location']['lng'],
				'latitude' 	=> $address['results'][0]['geometry']['location']['lat']
			);

			$address_array['full_address'] = $address['results'][0]['formatted_address'];

			return $address_array;
		}

		return $address;
	}

}
