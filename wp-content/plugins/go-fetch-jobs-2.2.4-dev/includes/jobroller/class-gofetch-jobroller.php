<?php
/**
 * Specific frontend code for JobRoller.
 *
 * @package GoFetch/JobRoller
 */

global $gofetch_jr_frontend;

class GoFetch_JR_Frontend {

	public function __construct() {

		if ( is_admin() ) {
			return;
		}

		add_filter( 'post_thumbnail_html', array( $this, 'maybe_use_external_thumb' ), 10, 5 );
		add_filter( "get_post_metadata", array( $this, 'has_post_thumbnail' ), 10, 4 );
		add_filter( 'the_content', array( $this, 'append_external_url' ), 10 );
		add_filter( 'the_content', array( $this, 'append_source' ), 11 );
	}

	/**
	 * Use image URL's instead of uploaded images on posts since images are usually protected from direct download.
	 */
	public function maybe_use_external_thumb( $html, $post_id, $post_thumbnail_id, $size, $attr ) {

 		if ( ! ( $is_external = get_post_meta( $post_id, '_goft_jobs_is_external', true ) ) ) {
 			return $html;
 		}

		if ( ! apply_filters( 'goft_jobs_use_company_url', GoFetch_JR()->parent_post_type === get_post_type( $post_id ) && has_post_thumbnail( $post_id ) ) ) {
			return $html;
		}

		$url = get_post_meta( $post_id, '_company_logo', true );

		if ( ! $url ) {
			return $html;
		}

		if ( false !== strpos( $size, 'thumbnail' ) ) {
			$attr['max-height'] = 55;
		}

		return $this->get_attachment_image_url( $url, $post_id, $size, $icon = false, $attr );
	}

	/**
	 * Used to foul WP into think he post has a thumb attachment if there's an external URL available and no attached thumbnail already.
	 */
	public function has_post_thumbnail( $value, $object_id, $meta_key, $single ) {

		if ( '_thumbnail_id' !== $meta_key || GoFetch_JR()->parent_post_type !== get_post_type( $object_id ) ) {
			return $value;
		}

		if ( ! apply_filters( 'goft_jobs_use_company_url', get_post_meta( $object_id, '_company_logo', true ) ) ) {
			return $value;
		}

 		if ( ! ( $is_external = get_post_meta( $object_id, '_goft_jobs_is_external', true ) ) ) {
 			return $value;
 		}

		return true;
	}

	/**
	 * Append the job details external URL to the post content.
	 */
	public function append_external_url( $content ) {
		global $post;

		if ( ! $post || ! is_single() ) {
			return $content;
		}

		$append_external_url = is_user_logged_in();

		if ( ! apply_filters( 'goft_jobs_append_external_url', $append_external_url ) || ! ( $is_external = get_post_meta( $post->ID, '_goft_jobs_is_external', true ) ) ) {
			return $content;
		}

		// Skip earlier if the content already contains the '[...]' with the external URL.
		if ( false !== stripos( $content, '[&#8230;]' ) ) {
			return $content;
		}

		$source_data = get_post_meta( $post->ID, '_goft_source_data', true );

		$link = get_post_meta( $post->ID, '_goft_external_link', true );
		$link = GoFetch_JR_Importer::add_query_args( $source_data, $link );

		$read_more = apply_filters( 'goft_jobs_read_more', '[...]', $content, $link );

		// If the content is wrapped in <p> tags make sure the <a> is added inside it.
		$content_inline = '/p>' === trim( substr( $content, -4 ) ) ? substr( $content, 0, -5 ) : $content;

		$content = sprintf( '%1$s <a class="goftj-exernal-link" href="%2$s" rel="nofollow" target="_blank">' . $read_more . '</a>', $content_inline, esc_url( $link ) ) . '</p>';

		return $content;
	}

	/**
	 * Append the source job URL to the post content.
	 */
	public function append_source( $content ) {
		global $post;

		if ( ! $post || ! is_single() ) {
			return $content;
		}

		if ( ! apply_filters( 'goft_jobs_append_source_data', true ) || ! ( $is_external = get_post_meta( $post->ID, '_goft_jobs_is_external', true ) ) ) {
			return $content;
		}

		$source_data = get_post_meta( $post->ID, '_goft_source_data', true );

		if ( empty( $source_data['name'] ) && empty( $source_data['website'] ) ) {
			return $content;
		}

		$defaults = array(
			'name' => '',
		);
		$source_data = wp_parse_args( $source_data, $defaults );

		if ( empty( $source_data['website'] ) ) {
			$source_data['website'] = get_post_meta( $post->ID, '_goft_external_link', true );
		}

		?><style type="text/css">
			.entry-content a.goftj-logo-exernal-link {
				box-shadow: none;
			}
			.goftj-source-logo {
				height: 32px;
			}
			.goftj-logo-exernal-link {
				display: block;
			}
		</style><?php


		$link = GoFetch_JR_Importer::add_query_args( $source_data, $source_data['website'] );

		if ( ! empty( $source_data['logo'] ) ) {
			$source = html( 'img class="goftj-source-logo"', array( 'src' => esc_url( $source_data['logo'] ), 'rel' => 'nofollow', 'title' => esc_attr( $source_data['name'] ) ), '&nbsp;' );
		} else {
			$source = html( 'span class="goftj-source"', $source_data['name'] );
		}

		if ( $link ) {
			$source = html( 'a', array( 'class' => 'goftj-logo-exernal-link', 'rel' => 'nofollow', 'target' => 'blank', 'href' => esc_url( $link ), 'title' => esc_attr( $source_data['name'] ) ), $source );
		}

		$source = sprintf( html( 'p', sprintf( __( '<em class="goft-source">%1$s</em> %2$s', 'gofetch-jobs' ), __( 'Source:', 'gofetch-jobs' ), $source ) ) );

		return $content . $source;
	}


	// __HELPERS.

	/**
	 * Simpler version of 'wp_get_attachment_image()' to output external images.
	 */
	private function get_attachment_image_url( $src, $post_id, $size = 'thumbnail', $icon = false, $attr = '' ) {

		$html = '';

		$size_class = $size;
		if ( is_array( $size_class ) ) {
			$size_class = join( 'x', $size_class );
		}

		$sizes = $this->get_image_size( $size );

		$width  = $sizes['width'];
		$height = $sizes['height'];


		if ( ! empty( $attr['max-height'] ) && $width > $attr['max-height'] ) {
			$height = $attr['max-height'];
			$width  = '';
		}

		$image_meta = array(
			'file'   => $src,
			'width'  => $width,
			'height' => $height,
		);

		$hwstring = image_hwstring( $width, $height );

		$default_attr = array(
			'src'	=> $src,
			'class'	=> "attachment-$size_class size-$size_class",
			'alt'	=> trim(strip_tags( get_post_field( 'post_title', $post_id ) . ' ' .  __( 'thumbnail', 'gofetch-jobs' ) ) ), // Use Alt field first
		);
		$attr = wp_parse_args( $attr, $default_attr );

		$attr['class'] .= ' wp-post-image';

		// Generate 'srcset' and 'sizes' if not already present.
		if ( empty( $attr['srcset'] ) ) {

			if ( is_array( $image_meta ) ) {
				$size_array = array( absint( $width ), absint( $height ) );
				$srcset     = wp_calculate_image_srcset( $size_array, $src, $image_meta );
				$sizes      = wp_calculate_image_sizes( $size_array, $src, $image_meta );

				if ( $srcset && ( $sizes || ! empty( $attr['sizes'] ) ) ) {
					$attr['srcset'] = $srcset;

					if ( empty( $attr['sizes'] ) ) {
						$attr['sizes'] = $sizes;
					}
				}
			}
		}

		$attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, null, $size );
		$attr = array_map( 'esc_attr', $attr );
		$html = rtrim("<img $hwstring");

		foreach ( $attr as $name => $value ) {
			$html .= " $name=" . '"' . esc_attr( $value ) . '"';
		}
		$html .= ' />';

		return $html;
	}

	/**
	 * Get size information for all currently-registered image sizes.
	 *
	 * @global $_wp_additional_image_sizes
	 * @uses   get_intermediate_image_sizes()
	 * @return array $sizes Data for all currently-registered image sizes.
	 */
	function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}

		return $sizes;
	}

	/**
	 * Get size information for a specific image size.
	 *
	 * @uses   get_image_sizes()
	 * @param  string $size The image size for which to retrieve data.
	 * @return bool|array $size Size data about an image size or false if the size doesn't exist.
	 */
	function get_image_size( $size ) {
		$sizes = $this->get_image_sizes();

		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		}

		return false;
	}

}

$gofetch_jr_frontend = new GoFetch_JR_Frontend;
