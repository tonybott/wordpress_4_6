<?php

/**
 * Contains the all the core import functionality.
 *
 * @package GoFetchJobs/Importer
 */
if ( !defined( 'ABSPATH' ) ) {
    die;
}
/**
 * The core importer class.
 */
class GoFetch_WPJM_Importer
{
    /**
     * List of used taxonomies objects.
     *
     * @var object
     */
    protected static  $used_taxonomies ;
    public function __construct()
    {
        add_action(
            'wp_feed_options',
            array( __CLASS__, 'set_feed_options' ),
            10,
            2
        );
    }
    
    /**
     * RSS and Atom feeds are supposed to have certain mime types associated with them so
     * that software knows what type of data it is. Some feeds don't follow these rules,
     * and serve feeds with invalid mime types (e.g. text/plain).
     */
    public static function set_feed_options( $feed, $url )
    {
        if ( empty($_POST['_ajax_nonce']) || !wp_verify_nonce( $_POST['_ajax_nonce'], 'goft_wpjm_nonce' ) ) {
            return $feed;
        }
        if ( empty($_REQUEST['action']) || 'goft_wpjm_import_feed' !== $_REQUEST['action'] ) {
            return $feed;
        }
        $feed->set_timeout( 60 );
        // Force feed if the user asks it.
        if ( 'false' !== $_REQUEST['force_feed'] ) {
            $feed->force_feed( true );
        }
        return $feed;
    }
    
    /**
     * Imports RSS feed items from a given URL.
     */
    public static function import_feed( $url, $fetch_images = false, $cache = true )
    {
        $provider = $sample_item = $new_items = array();
        // Fix URL's with double forward slashes like http://somedomain.com//some_page.
        $url = preg_replace( '/(?<!:)\\/\\//', '/', $url );
        // Remove last '&' from the URL.
        $url = preg_replace( '/(&)$/is', '', $url );
        $url = esc_url_raw( trim( $url ) );
        $feed = fetch_feed( wp_specialchars_decode( $url ) );
        if ( is_wp_error( $feed ) ) {
            return $feed;
        }
        $parsed_url = parse_url( $feed->get_permalink() );
        
        if ( !empty($parsed_url['host']) ) {
            $provider_id = str_replace( 'www.', '', $parsed_url['host'] );
            $host = $parsed_url['host'];
        } else {
            $provider_id = 'unknown';
            $host = __( 'Unknown', 'gofetch-wpjm' );
        }
        
        $provider = GoFetch_WPJM_RSS_Providers::get_providers( $provider_id );
        // Set provider data.
        $defaults = array(
            'id'          => $provider_id,
            'title'       => $feed->get_title(),
            'website'     => $host,
            'description' => $feed->get_description(),
            'logo'        => '',
        );
        $provider = wp_parse_args( (array) $provider, $defaults );
        // If this is a multi-site child provider skip part of the base data from the parent and use the defaults.
        
        if ( !empty($provider['inherit']) ) {
            $provider['title'] = $defaults['title'];
            $provider['website'] = $defaults['website'];
            $provider['description'] = $defaults['description'];
        }
        
        // Get all the valid item tags for the providers.
        $valid_item_tags = GoFetch_WPJM_RSS_Providers::valid_item_tags();
        $valid_regexp_tags = GoFetch_WPJM_RSS_Providers::valid_regexp_tags();
        // Always set a default 'base' namespace with the valid item tags.
        $namespaces = self::get_namespaces_for_feed( $url );
        $namespaces['base'] = 'base';
        // Try to get the provider logo through the feed or using Open Graph.
        
        if ( empty($provider['logo']) && ($logo = $feed->get_image_url()) ) {
            $graph = self::load_open_graph( $provider['website'] );
            if ( $graph && ($logo = $graph->image) ) {
            }
            // Check if the provider logo is valid. Skip it if we get a 404.
            
            if ( !empty($logo) ) {
                $response = wp_remote_get( $logo );
                $http_code = wp_remote_retrieve_response_code( $response );
                if ( 404 !== $http_code ) {
                    $provider['logo'] = $logo;
                }
            }
        
        }
        
        $items = $feed->get_items();
        $custom_tags = array();
        foreach ( $items as $item ) {
            // Get the XML main meta data.
            $new_item = array();
            $image = '';
            $new_item['title'] = wp_strip_all_tags( $item->get_title() );
            $new_item['link'] = wp_strip_all_tags( $item->get_permalink() );
            $new_item['date'] = wp_strip_all_tags( $item->get_date( 'Y-m-d' ) );
            $new_item['description'] = apply_filters( 'the_content', $item->get_description() );
            
            if ( $fetch_images && empty($new_item['logo']) ) {
                if ( $enclosure = $item->get_enclosure() ) {
                    $image = $enclosure->get_link();
                }
                
                if ( !$image ) {
                    $og = self::load_open_graph( $item->get_permalink() );
                    if ( !empty($og) ) {
                        $image = $og->image;
                    }
                }
                
                
                if ( $image ) {
                    $new_item['logo'] = $image;
                    $new_item['logo_html'] = html( 'img', array(
                        'src'   => $image,
                        'class' => 'goft-og-image',
                    ) );
                }
            
            }
            
            //
            if ( !empty($og->site_name) && empty($provider['name']) ) {
                $provider['name'] = $og->site_name;
            }
            // Find the item with the most attributes to use as sample.
            if ( count( array_keys( $new_item ) ) > count( array_keys( $sample_item ) ) ) {
                $sample_item = $new_item;
            }
            $new_items[] = apply_filters( 'goft_wpjm_rss_item', $new_item );
        }
        // Additional provider attributes.
        if ( empty($provider['name']) && !empty($provider['title']) ) {
            $provider['name'] = $provider['title'];
        }
        if ( empty($custom_tags) ) {
            $provider['custom_tags'] = $custom_tags;
        }
        // If we're caching the items split the list in chunks to avoid DB errors.
        
        if ( $cache && !empty($new_items) ) {
            $chunked_items = self::maybe_chunkify_list( $new_items );
            self::cache_feed_items( $chunked_items );
        }
        
        unset( $items );
        unset( $namespaces );
        return array(
            'provider'    => $provider,
            'items'       => $new_items,
            'sample_item' => $sample_item,
        );
    }
    
    /**
     * The public wrapper method for the import process.
     */
    public static function import( $items, $params )
    {
        $imported = 0;
        
        if ( empty($items) ) {
            trigger_error( __( 'No items found!', 'gofetch-wpjm' ) );
            return array(
                'imported'    => 0,
                'duplicates'  => 0,
                'in_rss_feed' => 0,
                'limit'       => 0,
            );
        }
        
        self::$used_taxonomies = get_object_taxonomies( GoFetch_WPJM()->parent_post_type, 'objects' );
        $items = apply_filters( 'goft_wpjm_import_items_before_filter', $items );
        $defaults = array(
            'post_type'       => GoFetch_WPJM()->parent_post_type,
            'post_author'     => 1,
            'tax_input'       => array(),
            'smart_tax_input' => '',
            'meta'            => array(),
            'from_date'       => '',
            'to_date'         => '',
            'limit'           => '',
            'keywords'        => '',
            'import_images'   => true,
        );
        $params = wp_parse_args( $params, $defaults );
        $results = self::filter_items(
            $items,
            array(
            'post_type' => $params['post_type'],
        ),
            $params['keywords'],
            $params['limit']
        );
        extract( $results );
        $unique_items = apply_filters( 'goft_wpjm_import_items_after_filter', $unique_items );
        // Iterate through all the unique items in the list.
        foreach ( $unique_items as $item ) {
            if ( !empty($params['from_date']) ) {
                if ( strtotime( $item['date'] ) < strtotime( $params['from_date'] ) ) {
                    continue;
                }
            }
            if ( !empty($params['to_date']) ) {
                if ( strtotime( $item['date'] ) > strtotime( $params['to_date'] ) ) {
                    continue;
                }
            }
            if ( !apply_filters(
                'goft_wpjm_import_rss_item',
                true,
                $item,
                $params
            ) ) {
                continue;
            }
            $imported += (int) self::_import( $item, $params );
        }
        $results = array(
            'in_rss_feed' => count( $items ),
            'imported'    => $imported,
            'limit'       => abs( count( $items ) - $params['limit'] ),
            'duplicates'  => count( $duplicate_items ),
            'excluded'    => count( $excluded_items ),
        );
        return $results;
    }
    
    // __Private.
    /**
     * The main import method.
     * Creates a new post for each imported job and adds any related meta data.
     */
    private static function _import( $item, $params )
    {
        do_action( 'goft_wpjm_before_insert_job', $item, $params );
        // Insert the main post with the core data taken from the feed item.
        
        if ( $post_id = self::_insert_post( $item, $params ) ) {
            // Prepare meta before adding it to the post.
            $meta = self::_prepare_item_meta(
                $item,
                $params['meta'],
                $post_id,
                $params
            );
            // Add any existing meta to the new post.
            self::_add_meta( $post_id, $meta );
            do_action(
                'goft_wpjm_after_insert_job',
                $post_id,
                $item,
                $params
            );
            return true;
        }
        
        do_action( 'goft_wpjm_insert_job_failed', $item, $params );
        return false;
    }
    
    /**
     * Insert a post given an item and related parameters.
     */
    private static function _insert_post( $item, $params )
    {
        $post_content = apply_filters(
            'goft_wpjm_post_content',
            wp_kses_post( $item['description'] ),
            $item,
            $params
        );
        // Use smart taxonomies terms assignment.
        
        if ( !empty($params['smart_tax_input']) ) {
            $content = $item['title'] . ' ' . $post_content;
            if ( method_exists( 'GoFetch_WPJM_Premium_Pro_Features', 'smart_tax_terms_input' ) ) {
                $params['tax_input'] = GoFetch_WPJM_Premium_Pro_Features::smart_tax_terms_input(
                    $params['tax_input'],
                    $content,
                    self::$used_taxonomies,
                    $params['smart_tax_input']
                );
            }
        }
        
        $post_arr = array(
            'post_title'   => $item['title'],
            'post_content' => $post_content,
            'post_status'  => 'publish',
            'post_type'    => $params['post_type'],
            'post_author'  => (int) $params['post_author'],
        );
        $post_id = wp_insert_post( $post_arr );
        if ( $post_id && !empty($params['tax_input']) ) {
            self::_add_taxonomies( $post_id, $params['tax_input'] );
        }
        return $post_id;
    }
    
    /**
     * Adds meta data to a given post ID.
     */
    private static function _add_taxonomies( $post_id, $tax_input )
    {
        foreach ( $tax_input as $tax => $terms ) {
            wp_set_object_terms( $post_id, $terms, $tax );
        }
    }
    
    /**
     * Adds meta data to a given post ID.
     */
    private static function _add_meta( $post_id, $meta )
    {
        foreach ( $meta as $meta_key => $meta_value ) {
            update_post_meta( $post_id, $meta_key, $meta_value );
        }
    }
    
    /**
     * Prepares all the meta for a given item before it is saved in the database.
     *
     * @uses apply_filters() Calls 'goft_wpjm_item_meta_value'.
     */
    private static function _prepare_item_meta(
        $item,
        $meta,
        $post_id,
        $params
    )
    {
        $meta['_goft_wpjm_is_external'] = 1;
        $meta['_goft_external_link'] = $item['link'];
        $meta['_goft_source_data'] = $params['source'];
        // Get the custom field mappings.
        $cust_field_mappings = GoFetch_WPJM_Specific_Import::meta_mappings();
        $meta = wp_parse_args( $meta, $cust_field_mappings );
        foreach ( $meta as $meta_key => $meta_value ) {
            // If any of the custom fields is found on items being imported get the value and override the defaults.
            
            if ( isset( $cust_field_mappings[$meta_key] ) ) {
                $known_field = $cust_field_mappings[$meta_key];
                if ( !empty($item[$known_field]) ) {
                    $meta_value = sanitize_text_field( $item[$known_field] );
                }
            }
            
            /**
             * @todo: maybe use a placeholder featured image and use a filter to override the featured image SRC image.
             */
            if ( !apply_filters(
                'goft_wpjm_item_skip_meta',
                false,
                $meta_value,
                $meta_key,
                $item,
                $post_id,
                $params
            ) ) {
                $meta[$meta_key] = apply_filters(
                    'goft_wpjm_item_meta_value',
                    $meta_value,
                    $meta_key,
                    $item,
                    $post_id,
                    $params
                );
            }
        }
        return $meta;
    }
    
    /**
     * Filter the items and return a list of results.
     */
    private static function filter_items(
        $items,
        $args = array(),
        $keywords = '',
        $limit = 0
    )
    {
        $defaults = array(
            'post_type'   => 'post',
            'post_status' => 'any',
            'meta_key'    => '_goft_wpjm_is_external',
        );
        $args = apply_filters( 'goft_wpjm_filter_items_query_args', wp_parse_args( $args, $defaults ) );
        $results = new WP_Query( $args );
        if ( $results->post_count ) {
            // Get existing external posts.
            foreach ( $results->posts as $post ) {
                $external_link = get_post_meta( $post->ID, '_goft_external_link', true );
                $external_posts[$external_link] = $post->post_name;
            }
        }
        $unique_items = $duplicate_items = $excluded_items = array();
        
        if ( $keywords ) {
            $keywords = explode( ',', $keywords );
            $keywords = stripslashes_deep( $keywords );
        }
        
        foreach ( $items as $item ) {
            $content = $item['description'];
            $content .= $item['title'];
            // Check for unique external posts by comparing the external link.
            
            if ( empty($external_posts) || !isset( $external_posts[$item['link']] ) ) {
                // Match keywords.
                
                if ( $keywords && $content ) {
                    
                    if ( !GoFetch_WPJM_Helper::match_keywords( $content, $keywords ) ) {
                        $excluded_items[] = $item;
                        continue;
                    } else {
                        $unique_items[] = $item;
                    }
                
                } else {
                    $unique_items[] = $item;
                }
                
                // Limit the results if requested by the user.
                if ( $limit && count( $unique_items ) >= $limit ) {
                    break;
                }
            } else {
                $duplicate_items[] = $items;
            }
        
        }
        $results = array(
            'unique_items'    => $unique_items,
            'excluded_items'  => $excluded_items,
            'duplicate_items' => $duplicate_items,
        );
        return $results;
    }
    
    /**
     * Split the list in chunks for a given array count.
     */
    private static function maybe_chunkify_list( $list, $max = 10 )
    {
        if ( count( $list ) <= $max ) {
            return $list;
        }
        // Separate the items list in chunks to avoid DB errors with big RSS feeds.
        return array_chunk( $list, $max );
    }
    
    /**
     * Cache the RSS feed in the database.
     */
    private static function cache_feed_items( $items, $expiration = 3600 )
    {
        global  $_wp_using_ext_object_cache ;
        // Temporarily turn off the object cache while we deal with transients since
        // some caching plugins like W3 Total Cache conflicts with our work.
        $_wp_using_ext_object_cache_previous = $_wp_using_ext_object_cache;
        $_wp_using_ext_object_cache = false;
        // If items are not separated in chunks make sure we have an array of arrays.
        
        if ( empty($items[0][0]) ) {
            $chunks[] = $items;
        } else {
            $chunks = $items;
        }
        
        foreach ( $chunks as $key => $chunk ) {
            set_transient( "_goft-rss-feed-{$key}", $chunk, $expiration );
        }
        set_transient( '_goft-rss-feed-chunks', count( $chunks ), $expiration );
        // Restore the caching values.
        $_wp_using_ext_object_cache = $_wp_using_ext_object_cache_previous;
    }
    
    // __Helpers.
    /**
     * Retrieves external links considering custom user arguments.
     */
    public static function add_query_args( $params, $link )
    {
        if ( empty($params['source']['args']) && empty($params['args']) ) {
            return $link;
        }
        $args = ( !empty($params['source']['args']) ? $params['source']['args'] : $params['args'] );
        $qargs = array();
        $query_args = explode( ',', $args );
        foreach ( $query_args as $arg ) {
            $temp_qargs = explode( '=', $arg );
            $qargs = array_merge( $qargs, array(
                trim( $temp_qargs[0] ) => trim( $temp_qargs[1] ),
            ) );
        }
        return add_query_arg( $qargs, $link );
    }
    
    /**
     * Retrieves a pre-set list of key/value Open Graph tags/values from a given URL.
     */
    private static function load_open_graph( $url )
    {
        return OpenGraph::fetch( $url );
    }
    
    /**
     * Does a simple reverse geocode using lat and long and retrieves the result address, or false, on error.
     */
    private static function simple_reverse_geocode( $lat, $lng )
    {
        $geo_api = 'https://maps.googleapis.com/maps/api/geocode/json?sensor=false';
        $geo_api = add_query_arg( 'latlng', $lat . ',' . $lng, $geo_api );
        $response = wp_remote_get( $geo_api, array(
            'timeout'     => 5,
            'redirection' => 1,
            'sslverify'   => false,
        ) );
        if ( is_wp_error( $response ) ) {
            return false;
        }
        $response = wp_remote_retrieve_body( $response );
        $geocoded_address = json_decode( $response );
        if ( empty($geocoded_address->results[0]->formatted_address) ) {
            return false;
        }
        $location = sanitize_text_field( $geocoded_address->results[0]->formatted_address );
        return $location;
    }
    
    /**
     * Retrieves parts from an item text using regex patterns.
     */
    private static function get_item_regex_parts( $text, $patterns )
    {
        $parts = array();
        foreach ( (array) $patterns as $key => $pattern ) {
            preg_match( $pattern, html_entity_decode( $text ), $matches );
            end( $matches );
            $last_index = key( $matches );
            // Skip anything longer then 50 chars as it's probably fetching wrong data.
            if ( !empty($matches[$last_index]) && strlen( trim( $matches[$last_index] ) ) < 50 ) {
                $parts[$key] = trim( $matches[$last_index] );
            }
        }
        return $parts;
    }
    
    /**
     * Try to retrieve all the namespaces within an RSS feed.
     */
    private static function get_namespaces_for_feed( $url, $convert = false )
    {
        $namespaces = array();
        $feed = @file_get_contents( $url );
        if ( $convert ) {
            // Ignore errors with some UTF-8 feed.
            $feed = iconv( 'UTF-8', 'UTF-8//IGNORE', $feed );
        }
        try {
            libxml_use_internal_errors( true );
            $xml = new SimpleXmlElement( $feed );
            if ( empty($xml->channel->item) ) {
                return $namespaces;
            }
            foreach ( $xml->channel->item as $entry ) {
                $curr_namespaces = $entry->getNameSpaces( true );
                $namespaces = array_merge( $namespaces, $curr_namespaces );
            }
            libxml_clear_errors();
        } catch ( Exception $e ) {
            if ( !$convert ) {
                self::get_namespaces_for_feed( $url, true );
            }
        }
        return $namespaces;
    }
    
    /**
     * Imports an image to the DB.
     */
    private static function sideload_import_image( $url, $post_id )
    {
        $image = media_sideload_image( $url, $post_id );
        return $image;
    }

}
new GoFetch_WPJM_Importer();