<?php

/**
* wpv-filter-order-by-embedded.php
*
* @package Views
*
* @since unknown
*/

/**
* WPV_Sorting_Embedded
*
* @since 1.11
*/

class WPV_Sorting_Embedded {
	
	public function __construct() {
		add_action( 'init',											array( $this, 'init' ) );	
    }
	
	function init() {
		$this->register_shortcodes();
		
		// Legacy
		add_action( 'wpv_action_wpv_pagination_map_legacy_order',	array( $this, 'map_legacy_order' ) );
		
		add_filter( 'wpv_view_settings',							array( $this, 'sorting_defaults' ) );
		add_filter( 'wpv_filter_wpv_get_sorting_defaults',			array( $this, 'sorting_defaults' ) );
		
		add_filter( 'wpv_filter_query',								array( $this, 'set_post_view_sorting' ), 100, 2 );
		add_filter( 'wpv_filter_wpv_apply_post_view_sorting',		array( $this, 'set_post_view_sorting' ), 10, 2 );
		
		add_filter( 'wpv_filter_taxonomy_query',					array( $this, 'set_taxonomy_view_sorting' ), 10, 3 );
		add_filter( 'wpv_filter_wpv_apply_taxonomy_view_sorting',	array( $this, 'set_taxonomy_view_sorting' ), 10, 3 );
		add_filter( 'wpv_filter_taxonomy_post_query',				array( $this, 'set_taxonomy_view_sorting_post_query' ), 30, 4 );
		
		add_filter( 'wpv_filter_user_query',						array( $this, 'set_user_view_sorting' ), 40, 2 );
		add_filter( 'wpv_filter_wpv_apply_user_view_sorting',		array( $this, 'set_user_view_sorting' ), 10, 2 );
		
	}
	
	function register_shortcodes() {
		add_shortcode( 'wpv-sort-orderby',				array( $this, 'wpv_shortcode_wpv_sort_orderby' ) );
		add_shortcode( 'wpv-sort-order',				array( $this, 'wpv_shortcode_wpv_sort_order' ) );
		
		add_shortcode( 'wpv-sort-link',					array( $this, 'wpv_shortcode_wpv_sort_link' ) );
		
		//add_shortcode( 'wpv-orderby-second',	array( $this, 'wpv_shortcode_wpv_sort_orderby' ) );
		//add_shortcode( 'wpv-order-second',		array( $this, 'wpv_shortcode_wpv_sort_order' ) );
	}
	
	/**
	* @since 2.0
	*/
	
	function map_legacy_order() {
		if ( isset( $_GET['wpv_column_sort_id'] ) ) {
			$_GET['wpv_sort_orderby'] = $_GET['wpv_column_sort_id'];
			unset( $_GET['wpv_column_sort_id'] );
		}
		if ( isset( $_GET['wpv_column_sort_dir'] ) ) {
			$_GET['wpv_sort_order'] = $_GET['wpv_column_sort_dir'];
			unset( $_GET['wpv_column_sort_dir'] );
		}
	}
	
	function sorting_defaults( $view_settings ) {
		if ( ! isset( $view_settings['orderby'] ) ) {
			$view_settings['orderby'] = 'post_date';
		}
		if ( ! isset( $view_settings['order'] ) ) {
			$view_settings['order'] = 'DESC';
		}
		if( ! isset( $view_settings['orderby_as'] ) ) {
			$view_settings['orderby_as'] = '';
		}
		if ( ! isset( $view_settings['orderby_second'] ) ) {
			$view_settings['orderby_second'] = '';
		}
		if ( ! isset( $view_settings['order_second'] ) ) {
			$view_settings['order_second'] = 'DESC';
		}
		if ( ! isset( $view_settings['taxonomy_orderby'] ) ) {
			$view_settings['taxonomy_orderby'] = 'name';
		}
		if ( ! isset( $view_settings['taxonomy_order'] ) ) {
			$view_settings['taxonomy_order'] = 'DESC';
		}
		if( ! isset( $view_settings['taxonomy_orderby_as'] ) ) {
			$view_settings['taxonomy_orderby_as'] = '';
		}
		if ( ! isset( $view_settings['users_orderby'] ) ) {
			$view_settings['users_orderby'] = 'user_login';
		}
		if ( ! isset( $view_settings['users_order'] ) ) {
			$view_settings['users_order'] = 'DESC';
		}
		if( ! isset( $view_settings['users_orderby_as'] ) ) {
			$view_settings['users_orderby_as'] = '';
		}
		return $view_settings;
	}
	
	function set_post_view_sorting( $query, $view_settings ) {
		
		global $wp_version;
		
		$is_view_posted = false;
		if ( isset( $_GET['wpv_view_count'] ) ) {
			$view_unique_hash = apply_filters( 'wpv_filter_wpv_get_object_unique_hash', '', $view_settings );
			if ( esc_attr( $_GET['wpv_view_count'] ) == $view_unique_hash ) {
				$is_view_posted = true;
				// Map old URL parameters to new ones
				do_action( 'wpv_action_wpv_pagination_map_legacy_order' );
			}
		}
		
		$orderby	= $view_settings['orderby'];
		$order		= $view_settings['order'];
		$orderby_as	= $view_settings['orderby_as'];
		
		$orderby_second	= $view_settings['orderby_second'];
		$order_second	= $view_settings['order_second'];
		
		// Override with attributes
		$override_allowed = array(
			'orderby'			=> array(),
			'order'				=> array( 'asc', 'ASC', 'desc', 'DESC' ),
			'orderby_as'		=> array( '', 'string', 'STRING', 'numeric', 'NUMERIC' ),
			'orderby_second'	=> array( 'date', 'post_date', 'title', 'post_title', 'id', 'ID', 'author', 'post_author' ),
			'order_second'		=> array( 'asc', 'ASC', 'desc', 'DESC' ),
		);
		$override_values = wpv_override_view_orderby_order( $override_allowed );
		if ( isset( $override_values['orderby'] ) ) {
			$orderby = $override_values['orderby'];
		}
		if ( isset( $override_values['order'] ) ) {
			$order = strtoupper( $override_values['order'] );
		}
		if ( isset( $override_values['orderby_as'] ) ) {
			$orderby_as = strtoupper( $override_values['orderby_as'] );
		}
		if ( isset( $override_values['orderby_second'] ) ) {
			$orderby_second = $override_values['orderby_second'];
		}
		if ( isset( $override_values['order_second'] ) ) {
			$order_second = strtoupper( $override_values['order_second'] );
		}
		
		// Override with URL parameters
		
		// Legacy order URL override
		if ( 
			$is_view_posted
			&& isset( $_GET['wpv_order'] ) 
			&& isset( $_GET['wpv_order'][0] )
			&& in_array( $_GET['wpv_order'][0], array( 'ASC', 'DESC' ) )
		) {
			$order = esc_attr( $_GET['wpv_order'][0] );
		}
		// Modern order URL override
		if ( $is_view_posted ) {
			if (
				isset( $_GET['wpv_sort_order'] ) 
				&& in_array( strtoupper( esc_attr( $_GET['wpv_sort_order'] ) ), array( 'ASC', 'DESC' ) )
			) {
				$order = strtoupper( esc_attr( $_GET['wpv_sort_order'] ) );
			}
			if (
				isset( $_GET['wpv_sort_orderby'] ) 
				&& esc_attr( $_GET['wpv_sort_orderby'] ) != 'undefined' 
				&& esc_attr( $_GET['wpv_sort_orderby'] ) != '' 
			) {
				$orderby = esc_attr( $_GET['wpv_sort_orderby'] );
			}

			if (
				isset( $_GET['wpv_sort_orderby_as'] )
				&& in_array( strtoupper( esc_attr( $_GET['wpv_sort_orderby_as'] ) ), array( 'STRING', 'NUMERIC' ) )
			) {
				$orderby_as = strtoupper( esc_attr( $_GET['wpv_sort_orderby_as'] ) );
			}
			// Secondary sorting
			if (
				isset( $_GET['wpv_sort_order_second'] ) 
				&& in_array( strtoupper( esc_attr( $_GET['wpv_sort_order_second'] ) ), array( 'ASC', 'DESC' ) )
			) {
				$order_second = strtoupper( esc_attr( $_GET['wpv_sort_order_second'] ) );
			}
			if (
				isset( $_GET['wpv_sort_orderby_second'] ) 
				&& esc_attr( $_GET['wpv_sort_orderby_second'] ) != 'undefined' 
				&& esc_attr( $_GET['wpv_sort_orderby_second'] ) != '' 
				&& in_array( $_GET['wpv_sort_orderby_second'], array( 'post_date', 'post_title', 'ID', 'modified', 'menu_order', 'rand' ) )
			) {
				$orderby_second = esc_attr( $_GET['wpv_sort_orderby_second'] );
			}
		}
		
		// Adjust values for custom field sorting
		
		if ( strpos( $orderby, 'field-' ) === 0 ) {
			// Natural Views sorting by custom field
			$query['meta_key'] = substr( $orderby, 6 );
			$orderby = 'meta_value';
		} else if ( strpos( $orderby, 'post-field' ) === 0 ) {
			// Table sorting for custom field
			$query['meta_key'] = substr( $orderby, 11 );
			$orderby = 'meta_value';
		} else if ( strpos( $orderby, 'types-field' ) === 0 ) {
			// Table sorting for Types custom field
			$query['meta_key'] = strtolower( substr( $orderby, 12 ) );
			$orderby = 'meta_value';
		} else {
			$orderby = str_replace( '-', '_', $orderby );
		}
		
		if ( 
			'meta_value' == $orderby 
			&& isset( $query['meta_key'] )
		) {
			
			if (
				isset( $orderby_as )
				&& in_array( $orderby_as, array( 'STRING', 'NUMERIC' ) )
			) {
				switch ( $orderby_as ) {
					case "STRING":
						$orderby = 'meta_value';
						break;
					case "NUMERIC":
						$orderby = 'meta_value_num';
						break;
				}
			}
			
			$is_types_field_data = wpv_is_types_custom_field ( $query['meta_key'] );
			if (
				$is_types_field_data 
				&& isset( $is_types_field_data['meta_key'] ) 
				&& isset( $is_types_field_data['type'] )
			) {
				$query['meta_key'] = $is_types_field_data['meta_key'];
				if ( in_array( $is_types_field_data['type'], array( 'numeric', 'date' ) ) ) {	// Auto-Discover
					$orderby = 'meta_value_num';
				}
			}
			
		}
		
		// Correct orderby and orderby_second options
		switch ( $orderby ) {
			case 'post_link':
				$orderby = 'post_title';
				break;
			case 'post_body':
				$orderby = 'post_content';
				break;
			case 'post_slug':
				$orderby = 'name';
				break;
			case 'post_id':
			case 'id':
				$orderby = 'ID';
				break;
			default:
				if ( strpos( $orderby, 'post_' ) === 0 ) {
					$orderby = substr( $orderby, 5 );
				}
				break;
		}
		switch ( $orderby_second ) {
			case 'post_link':
				$orderby_second = 'post_title';
				break;
			case 'post_body':
				$orderby_second = 'post_content';
				break;
			case 'post_slug':
				$orderby_second = 'name';
				break;
			case 'post_id':
			case 'id':
				$orderby_second = 'ID';
				break;
			default:
				if ( strpos( $orderby_second, 'post_' ) === 0 ) {
					$orderby_second = substr( $orderby_second, 5 );
				}
				break;
		}
		
		$query['orderby']	= $orderby;
		$query['order']		= $order;
		
		// See if filtering by custom fields and sorting by custom field too
		if (
			isset( $query['meta_key'] ) 
			&& isset( $query['meta_query'] )
		) {
			// We only need to do something if the relation is OR
			// When the relation is AND it does not matter if we sort by one of the filtering fields, because the filter will add an existence clause anyway
			// When the relation is OR, the natural query will generate an OR clause on the sorting field existence:
			// - if it is one of the filtering fields, it will make its clause useless because just existence will make it pass
			// - if it is not one of the filtering fields it will add an OR clause on this field existence that might pass for results that do not match any of the other requirements
			// See also: https://core.trac.wordpress.org/ticket/25538
			// Since WordPress 4.1 this is indeed not needed, thanks to nested meta_query entries
			// Note that this might contain a bug, since we are removing the meta_query but keeping the sorting by meta_value/meta_value_num in some cases
			if ( 
				isset( $query['meta_query']['relation'] ) 
				&& $query['meta_query']['relation'] == 'OR' 
			) {
				if ( version_compare( $wp_version, '4.1', '<' ) ) {
					$refinedquery = $query;
					unset( $refinedquery['orderby'] );
					unset( $refinedquery['meta_key'] );
					$refinedquery['posts_per_page'] = -1; // remove the limit in the main query to get all the relevant IDs
					$refinedquery['fields'] = 'ids';
					// first query only for filtering
					$filtered_query = new WP_Query( $refinedquery );
					$filtered_ids = array();
					if ( 
						is_array( $filtered_query->posts ) 
						&& !empty( $filtered_query->posts ) 
					) {
						$filtered_ids = $filtered_query->posts;
					}
					// remove the fields filter from the original query and add the filtered IDs
					unset( $query['meta_query'] );
					// we can replace the $query['post__in'] argument because it was applied on the auxiliar query before
					if ( count( $filtered_ids ) ) {
						$query['post__in'] = $filtered_ids;
					} else {
						$query['post__in'] = array('0');
					}
				}
			}
			
		}
		
		if ( 
			! version_compare( $wp_version, '4.0', '<' ) 
			&& $orderby != 'rand' 
			&& $orderby_second != '' 
			&& $orderby != $orderby_second
		) {
			$orderby_array = array(
				$orderby		=> $order,
				$orderby_second	=> $order_second
			);
			$query['orderby']	= $orderby_array;
		}
		
		return $query;
	}
	
	function set_taxonomy_view_sorting( $tax_query_settings, $view_settings, $view_id ) {
		$is_view_posted = false;
		if ( isset( $_GET['wpv_view_count'] ) ) {
			$view_unique_hash = apply_filters( 'wpv_filter_wpv_get_object_unique_hash', '', $view_settings );
			if ( esc_attr( $_GET['wpv_view_count'] ) == $view_unique_hash ) {
				$is_view_posted = true;
				// Map old URL parameters to new ones
				do_action( 'wpv_action_wpv_pagination_map_legacy_order' );
			}
		}

		$orderby =	$view_settings['taxonomy_orderby'];
		$order =	$view_settings['taxonomy_order'];
		$orderby_as = $view_settings['taxonomy_orderby_as'];
		// Override with attributes
		$override_allowed = array(
			'orderby'		=> array(),
			'order'			=> array( 'asc', 'ASC', 'desc', 'DESC' ),
			'orderby_as'	=> array( '', 'string', 'STRING', 'numeric', 'NUMERIC' )
		);
		$override_values = wpv_override_view_orderby_order( $override_allowed );
		if ( 
			isset( $override_values['orderby'] ) 
			&& (
				in_array( $override_values['orderby'], array( 'id', 'count', 'name', 'slug' ) )
				|| strpos( $override_values['orderby'], 'taxonomy-field-') === 0
			)
		) {
			$orderby = $override_values['orderby'];
		}
		if ( isset( $override_values['order'] ) ) {
			$order = strtoupper( $override_values['order'] );
		}
		if ( isset( $override_values['orderby_as'] ) ) {
			$orderby_as = strtoupper( $override_values['orderby_as'] );
		}
		
		// Override with URL parameters
		if ( $is_view_posted ) {
			// Orderby
			if (
				isset( $_GET['wpv_sort_orderby'] ) 
				&& esc_attr( $_GET['wpv_sort_orderby'] ) != 'undefined' 
				&& esc_attr( $_GET['wpv_sort_orderby'] ) != '' 
			) {
				$orderby = esc_attr( $_GET['wpv_sort_orderby'] );
			}
			// Order
			if (
				isset( $_GET['wpv_sort_order'] ) 
				&& esc_attr( $_GET['wpv_sort_order'] ) != '' 
				&& in_array( strtoupper( esc_attr( $_GET['wpv_sort_order'] ) ), array( 'ASC', 'DESC' ) )
			) {
				$order = strtoupper( esc_attr( $_GET['wpv_sort_order'] ) );
			}

			if (
				isset( $_GET['wpv_sort_orderby_as'] )
				&& in_array( strtoupper( esc_attr( $_GET['wpv_sort_orderby_as'] ) ), array( 'STRING', 'NUMERIC' ) )
			) {
				$orderby_as = strtoupper( esc_attr( $_GET['wpv_sort_orderby_as'] ) );
			}
			
		}
		
		// Correct orderby options
		switch ( $orderby ) {
			case 'taxonomy-link':
			case 'taxonomy-title':
				$orderby = 'name';
				break;
			case 'taxonomy-post_count':
				$orderby = 'count';
				break;
			case 'taxonomy-id':
				$orderby = 'id';
				break;
			case 'taxonomy-slug':
				$orderby = 'slug';
				break;
			default:
				if ( strpos( $orderby, 'taxonomy-field-' ) === 0 ) {
					global $wp_version;
					if ( version_compare( $wp_version, '4.5', '<' ) ) {
						$orderby = 'name';
					} else {
						$tax_query_settings['meta_key'] = substr( $orderby, 15 );
						$orderby = 'meta_value';
					}
				}
				break;
		}
		
		if ( 
			'meta_value' == $orderby 
			&& isset( $tax_query_settings['meta_key'] )
		) {
			
			if (
				isset( $orderby_as )
				&& in_array( $orderby_as, array( 'STRING', 'NUMERIC' ) )
			) {
				switch ( $orderby_as ) {
					case "STRING":
						$orderby = 'meta_value';
						break;
					case "NUMERIC":
						$orderby = 'meta_value_num';
						break;
				}
			}
			
			$is_types_field_data = wpv_is_types_custom_field( $tax_query_settings['meta_key'], 'tf' );
			if (
				$is_types_field_data
				&& isset( $is_types_field_data['meta_key'] )
				&& isset( $is_types_field_data['type'] )
			) {
				$tax_query_settings['meta_key'] = $is_types_field_data['meta_key'];
				if ( in_array( $is_types_field_data['type'], array( 'numeric', 'date' ) ) ) {	// Auto-Discover
					$orderby = 'meta_value_num';
				}
			}
			
		}
		
		$tax_query_settings['orderby']	= $orderby;
		$tax_query_settings['order']	= $order;

		return $tax_query_settings;
	}
	
	function set_taxonomy_view_sorting_post_query( $items, $tax_query_settings, $view_settings, $view_id ) {
		if ( 
			$tax_query_settings['orderby'] == 'count' 
			&& $tax_query_settings['pad_counts']
		) {
			if ( $tax_query_settings['order'] == 'ASC' ) {
				usort( $items, '_wpv_taxonomy_sort_asc' );
			} else {
				usort( $items, '_wpv_taxonomy_sort_dec' );
			}
		}
		return $items;
	}
	
	function set_user_view_sorting( $args, $view_settings ) {
		
		$is_view_posted = false;
		if ( isset( $_GET['wpv_view_count'] ) ) {
			$view_unique_hash = apply_filters( 'wpv_filter_wpv_get_object_unique_hash', '', $view_settings );
			if ( esc_attr( $_GET['wpv_view_count'] ) == $view_unique_hash ) {
				$is_view_posted = true;
				// Map old URL parameters to new ones
				do_action( 'wpv_action_wpv_pagination_map_legacy_order' );
			}
		}
		
		$orderby		= '';
		$order			= '';
		$orderby_as		= '';
		// @todo check this is most likely set!! No need to pretend it might not be
		if ( isset( $view_settings['users_orderby'] ) ) {
			$orderby = $view_settings['users_orderby'];
		}
		if ( isset( $view_settings['users_order'] ) ) {
			$order = $view_settings['users_order'];
		}
		if ( isset( $view_settings['users_orderby_as'] ) ) {
			$orderby_as = $view_settings['users_orderby_as'];
		}
		// Override with attributes
		$override_allowed = array(
			'orderby'		=> array(),
			'order'			=> array( 'asc', 'ASC', 'desc', 'DESC' ),
			'orderby_as'	=> array( '', 'string', 'STRING', 'numeric', 'NUMERIC' )
		);
		$override_values = wpv_override_view_orderby_order( $override_allowed );
		if ( 
			isset( $override_values['orderby'] ) 
			&& (
				in_array( $override_values['orderby'], array( 'user_email', 'user_login', 'display_name', 'user_url', 'user_registered', 'user_nicename', 'include' ) )
				|| strpos( $override_values['orderby'], 'user-field-') === 0
			)
		) {
			$orderby = $override_values['orderby'];
		}
		if ( isset( $override_values['order'] ) ) {
			$order = strtoupper( $override_values['order'] );
		}
		if ( isset( $override_values['orderby_as'] ) ) {
			$orderby_as = strtoupper( $override_values['orderby_as'] );
		}
		// Override with URL parameters
		if ( $is_view_posted ) {
			$column_sort_id = wpv_getget( 'wpv_sort_orderby', '' );
			if ( 
				! empty( $column_sort_id ) 
				&& (
					in_array( $column_sort_id, $override_allowed['orderby'] )
					|| strpos( $column_sort_id, 'user-field-') === 0
				)
			) {
				$orderby = $column_sort_id;
			}
			if (
				isset( $_GET['wpv_sort_order'] ) 
				&& esc_attr( $_GET['wpv_sort_order'] ) != '' 
				&& in_array( strtoupper( esc_attr( $_GET['wpv_sort_order'] ) ), array( 'ASC', 'DESC' ) )
			) {
				$order = strtoupper( esc_attr( $_GET['wpv_sort_order'] ) );
			}
			
			if (
				isset( $_GET['wpv_sort_orderby_as'] )
				&& in_array( strtoupper( esc_attr( $_GET['wpv_sort_orderby_as'] ) ), array( 'STRING', 'NUMERIC' ) )
			) {
				$orderby_as = strtoupper( esc_attr( $_GET['wpv_sort_orderby_as'] ) );
			}
		}
		
		if ( strpos( $orderby, 'user-field-' ) === 0 ) {
			
			$args['meta_key'] = substr( $orderby, 11 );
			$orderby = 'meta_value';
			
			if (
				isset( $orderby_as )
				&& in_array( $orderby_as, array( 'STRING', 'NUMERIC' ) )
			) {
				switch ( $orderby_as ) {
					case "STRING":
						$orderby = 'meta_value';
						break;
					case "NUMERIC":
						$orderby = 'meta_value_num';
						break;
				}
			}

			$is_types_field_data = wpv_is_types_custom_field( $args['meta_key'], 'uf' );
			if (
				$is_types_field_data
				&& isset( $is_types_field_data['meta_key'] )
				&& isset( $is_types_field_data['type'] )
			) {
				$args['meta_key'] = $is_types_field_data['meta_key'];
				if ( in_array( $is_types_field_data['type'], array( 'numeric', 'date' ) ) ) {	// Auto-Discover
					$orderby = 'meta_value_num';
				}
			}
			
		}
		
		if ( ! empty( $orderby ) ) {
			$args['orderby'] = $orderby;
		}
		if ( ! empty( $order ) ) {
			$args['order'] = $order;
		}
		return $args;
	}
	
	

	function wpv_shortcode_wpv_sort_orderby( $atts, $content ) {
		extract(
			shortcode_atts( array(
				'type'				=> 'select',
				'force_current'		=> 'true'
			), $atts )
		);
		
		$return = '';
		
		$content		= wpv_do_shortcode( $content );
		$attributes		= json_decode( $content, true );
		
		if ( 
			is_null( $attributes )
			|| $attributes === false
		) {
			return;
		}
		
		$view_id		= apply_filters( 'wpv_filter_wpv_get_current_view', null );
		$view_settings	= apply_filters( 'wpv_filter_wpv_get_object_settings', array() );
		$view_hash		= apply_filters( 'wpv_filter_wpv_get_object_unique_hash', '', $view_settings );
		
		// Maybe not needed
		$view_settings	= apply_filters( 'wpv_filter_wpv_get_object_settings', '' );
		$view_hash		= apply_filters( 'wpv_filter_wpv_get_object_unique_hash', '', $view_settings );
		
		$defaults = array(
			'default_label'		=> '',
			'values'			=> array()
		);
		foreach ( $defaults as $def_key => $def_val ) {
			if ( ! isset( $attributes[ $def_key ] ) ) {
				$attributes[ $def_key ] = $def_val;
			}
		}
		
		$current_orderby = '';
		if ( isset( $_GET['wpv_sort_orderby'] ) ) {
			$current_orderby = esc_attr( $_GET['wpv_sort_orderby'] );
		}
		
		switch ( $type ) {
			case 'select':
				$return .= '<select name="wpv_sort_orderby" class="js-wpv-sort-control-orderby" data-viewnumber="' . esc_attr( $view_hash ) . '" data-control="orderby" autocomplete="off">';
				if ( ! empty( $attributes['default_label'] ) ) {
					$return .= '<option value="">';
					$return .= esc_html( $attributes['default_label'] );
					$return .= '</option>';
				}
				if ( 
					! empty( $current_orderby )
					&& ! isset( $attributes['values'][ $current_orderby ] ) 
					&& $force_current == 'true'
				) {
					$return .= '<option value="' . $current_orderby . '" selected="selected">';
					$return .= $current_orderby;
					$return .= '</option>';
				}
				foreach ( $attributes['values'] as $val => $label ) {
					$return .= '<option value="' . esc_attr( $val ) . '" ' . selected( $val, $current_orderby, false ) . '>';
					$return .= $label;
					$return .= '</option>';
				}
				$return .= '</select>';
				break;
			case 'radio':
				$return .= '<label>' . '<input type="radio" name="wpv_sort_orderby" class="js-wpv-sort-control-orderby" value="" ' . checked( $current_orderby, '', false ) . ' data-viewnumber="' . esc_attr( $view_hash ) . '" data-control="orderby" autocomplete="off" />';
				$return .=  esc_html( $attributes['default_label'] ) . '</label>';
				if ( 
					! empty( $current_orderby )
					&& ! isset( $attributes['values'][ $current_orderby ] ) 
					&& $force_current == 'true'
				) {
					$return .= '<label>' . '<input type="radio" name="wpv_sort_orderby" class="js-wpv-sort-control-orderby" value="' . $current_orderby . '" checked="checked" data-viewnumber="' . esc_attr( $view_hash ) . '" data-control="orderby" autocomplete="off" />';
					$return .=  $current_orderby . '</label>';
				}
				foreach ( $attributes['values'] as $val => $label ) {
					$return .= '<label>' . '<input type="radio" name="wpv_sort_orderby" class="js-wpv-sort-control-orderby" value="' . esc_attr( $val ) . '" ' . checked( $current_orderby, $val, false ) . ' data-viewnumber="' . esc_attr( $view_hash ) . '" data-control="orderby" autocomplete="off" />';
					$return .=  $label . '</label>';
				}
				break;
		}
		return $return;
	}


	function wpv_shortcode_wpv_sort_order( $atts, $content ) {
		extract(
			shortcode_atts( array(
				'type'				=> 'select',
				'force_current'		=> 'true'
			), $atts )
		);
		
		$return = '';
		
		$content		= wpv_do_shortcode( $content );
		$attributes		= json_decode( $content, true );
		
		if ( 
			is_null( $attributes )
			|| $attributes === false
		) {
			return;
		}
		
		$view_id		= apply_filters( 'wpv_filter_wpv_get_current_view', null );
		$view_settings	= apply_filters( 'wpv_filter_wpv_get_object_settings', array() );
		$view_hash		= apply_filters( 'wpv_filter_wpv_get_object_unique_hash', '', $view_settings );
		
		$defaults = array(
			'default_label'		=> '',
			'values'			=> array()
		);
		foreach ( $defaults as $def_key => $def_val ) {
			if ( ! isset( $attributes[ $def_key ] ) ) {
				$attributes[ $def_key ] = $def_val;
			}
		}
		
		$current_order = '';
		if ( isset( $_GET['wpv_sort_order'] ) ) {
			$current_order = esc_attr( $_GET['wpv_sort_order'] );
		}
		
		switch ( $type ) {
			case 'select':
				$return .= '<select name="wpv_sort_order" class="js-wpv-sort-control-order" data-viewnumber="' . esc_attr( $view_hash ) . '" data-control="order" autocomplete="off">';
				if ( ! empty( $attributes['default_label'] ) ) {
					$return .= '<option value="">';
					$return .= esc_html( $attributes['default_label'] );
					$return .= '</option>';
				}
				foreach ( $attributes['values'] as $val => $label ) {
					if ( in_array( $val, array( 'asc', 'desc' ) ) ) {
						$return .= '<option value="' . esc_attr( $val ) . '" ' . selected( $val, $current_order, false ) . '>';
						$return .= $label;
						$return .= '</option>';
					}
				}
				$return .= '</select>';
				break;
			case 'radio':
				$return .= '<label>' . '<input type="radio" name="wpv_sort_order" class="js-wpv-sort-control-order" value="" ' . checked( $current_order, '', false ) . ' data-viewnumber="' . esc_attr( $view_hash ) . '" data-control="order" autocomplete="off" />';
				$return .=  esc_html( $attributes['default_label'] ) . '</label>';
				foreach ( $attributes['values'] as $val => $label ) {
					if ( in_array( $val, array( 'asc', 'desc' ) ) ) {
						$return .= '<label>' . '<input type="radio" name="wpv_sort_order" class="js-wpv-sort-control-order" value="' . esc_attr( $val ) . '" ' . checked( $current_order, $val, false ) . ' data-viewnumber="' . esc_attr( $view_hash ) . '" data-control="order" autocomplete="off" />';
						$return .=  $label . '</label>';
					}
				}
				break;
		}
		return $return;
	}
	
	function wpv_shortcode_wpv_sort_link( $atts, $content = null ) {
		
		extract(
			shortcode_atts( array(
				'orderby'		=> '',
				'order'			=> 'desc',
				'orderbyas'		=> '',
				'class'			=> '',
				'style'			=> ''
			), $atts )
		);
		
		$view_id		= apply_filters( 'wpv_filter_wpv_get_current_view', null );
		$view_settings	= apply_filters( 'wpv_filter_wpv_get_object_settings', array() );
		$view_hash		= apply_filters( 'wpv_filter_wpv_get_object_unique_hash', '', $view_settings );
		$return			= '';
		
		$pagination_data		= apply_filters( 'wpv_filter_wpv_get_pagination_settings', array(), $view_settings );
		$pagination_permalinks	= apply_filters( 'wpv_filter_wpv_get_pagination_permalinks', array(), $view_settings, $view_id );
		$permalink = $pagination_permalinks['first'];
		
		$query_args = array(
			'wpv_sort_orderby'	=> $orderby,
			'wpv_sort_order'	=> $order
		);
		if ( ! empty ( $orderbyas ) ) {
			$query_args['wpv_sort_orderby_as'] = $orderbyas;
		}
		
		$permalink = remove_query_arg(
			array( 'wpv_sort_orderby', 'wpv_sort_order', 'wpv_sort_orderby_as' ),
			$permalink
		);
		
		$permalink = add_query_arg(
			$query_args,
			$permalink
		);
		
		if ( empty( $orderby ) ) {
			return $return;
		}
		$order		= in_array( $order, array( 'asc', 'ASC', 'desc', 'DESC' ) ) ? strtolower( $order ) : 'desc';
		$content	= wpv_do_shortcode( $content );
		if ( ! empty( $style ) ) {
			$style = ' style="'. esc_attr( $style ).'"';
		}
		if ( ! empty( $class) ) {
			$class = ' ' . esc_attr( $class );
		}
		
		$return = '<a href="' . esc_url( $permalink ) . '"'
			. $style
			. ' class="js-wpv-sort-trigger' . $class . '"'
			. ' data-viewnumber="' 	. esc_attr( $view_hash ) . '"'
			. ' data-orderby="' . esc_attr( $orderby ) . '"'
			. ' data-order="' . esc_attr( $order ) . '"'
			. ' data-orderbyas="' . esc_attr( $orderbyas ) . '"'
			. '>'
			. $content
			. '</a>';
		
		return $return;
	}
	
}

global $WPV_Sorting_Embedded;
$WPV_Sorting_Embedded = new WPV_Sorting_Embedded();

/**
* -------------------------------------------------
* Shared functions
* -------------------------------------------------
*/

/**
 * Auxiliary function that will provide limit and offset settings coming from the Views shortcode atributes, if possible
 *
 * @param array $allowed (array) Valid values that can be used to override
 *
 * @return array $return (array)
 *
 * @since 1.10
 */
function wpv_override_view_orderby_order( $allowed = array() ) {
	$defaults = array(
		'orderby'			=> array(),
		'order'				=> array(),
		'orderby_as'		=> array(),
		'orderby_second'	=> array(),
		'order_second'		=> array()
	);
	$allowed = wp_parse_args( $allowed, $defaults );
	$return = array();
	$view_attrs = apply_filters( 'wpv_filter_wpv_get_view_shortcodes_attributes', false );;
	if ( isset( $view_attrs['orderby'] ) ) {
		if ( count( $allowed['orderby'] ) > 0 ) {
			if ( in_array( $view_attrs['orderby'], $allowed['orderby'] ) ) {
				$return['orderby'] = $view_attrs['orderby'];
			}
		} else {
			$return['orderby'] = $view_attrs['orderby'];
		}
	}
	if ( isset( $view_attrs['order'] ) ) {
		if ( count( $allowed['order'] ) > 0 ) {
			if ( in_array( $view_attrs['order'], $allowed['order'] ) ) {
				$return['order'] = $view_attrs['order'];
			}
		} else {
			$return['order'] = $view_attrs['order'];
		}
	}
	if ( isset( $view_attrs['orderby_as'] ) ) {
		if ( count( $allowed['orderby_as'] ) > 0 ) {
			if ( in_array( $view_attrs['orderby_as'], $allowed['orderby_as'] ) ) {
				$return['orderby_as'] = $view_attrs['orderby_as'];
			}
		} else {
			$return['orderby_as'] = $view_attrs['orderby_as'];
		}
	}
	if ( isset( $view_attrs['orderby_second'] ) ) {
		if ( count( $allowed['orderby_second'] ) > 0 ) {
			if ( in_array( $view_attrs['orderby_second'], $allowed['orderby_second'] ) ) {
				$return['orderby_second'] = $view_attrs['orderby_second'];
			}
		} else {
			$return['orderby_second'] = $view_attrs['orderby_second'];
		}
	}
	if ( isset( $view_attrs['order_second'] ) ) {
		if ( count( $allowed['order_second'] ) > 0 ) {
			if ( in_array( $view_attrs['order_second'], $allowed['order_second'] ) ) {
				$return['order_second'] = $view_attrs['order_second'];
			}
		} else {
			$return['order_second'] = $view_attrs['order_second'];
		}
	}
	return $return;
}
