<?php
/**
 * Specific admin settings for JobRoller.
 *
 * @package GoFetch/JobRoller
 */

class GoFetch_JR_Specific_Settings extends GoFetch_JR_Admin_Settings {

	public function __construct() {
		add_filter( 'goft_jobs_meta_fields', array( $this, 'meta_fields' ) );
		add_filter( 'goft_jobs_geocomplete_hidden_fields', array( $this, 'geocomplete_hidden_fields' ) );
		add_filter( 'goft_jobs_form_extra_content', array( $this, 'other_hidden_fields' ) );
		add_filter( 'goft_jobs_default_value_for_field', array( $this, 'default_value_for_field' ), 10, 2 );
		add_filter( 'goft_jobs_default_value_for_taxonomy', array( $this, 'default_value_for_tax' ), 10, 3 );
		add_filter( 'goft_jobs_template_setup', array( $this, 'backwards_compat_tax_input' ), 10, 3 );
	}

	/**
	 * JobRoller Meta Fields.
	 */
	public function meta_fields( $fields ) {

		$fields = array(
			array(
				'title'  => __( 'Featured in Listings', 'gofetch-jobs' ),
				'name'   => '_blank',
				'type'   => 'custom',
				'tip'    => __( 'Check this option to feature all jobs being imported.', 'gofetch-jobs' ),
				'render' => array( $this, 'output_featured_listing_meta_field' ),
			),
			array(
				'title'  => __( 'Featured in Category', 'gofetch-jobs' ),
				'name'   => '_blank',
				'type'   => 'custom',
				'tip'    => __( 'Check this option to feature all jobs being imported only in category pages.', 'gofetch-jobs' ),
				'render' => array( $this, 'output_featured_category_meta_field' ),
			),
			array(
				'title' => __( 'Job Duration', APP_TD ),
				'name'  => 'meta[_jr_job_duration]',
				'extra' => array(
					'class'     => 'small-text',
					'maxlength' => 3,
					'section'   => 'meta',
					'default'   => $this->get_default_value_for_meta( '_jr_job_duration' ),
				),
				'desc'  => __( 'Day(s)', 'gofetch-jobs' ),
				'tip'   => __( 'Choose the job duration for all the imported jobs.', 'gofetch-jobs' ),
				'value' => $this->get_default_value_for_meta( '_jr_job_duration' ),
			),
			array(
				'title' => __( 'Location', 'gofetch-jobs' ),
				'name'  => 'meta[_jr_address]',
				'extra' => array(
					'class'          => 'geocomplete regular-text',
					'placeholder'    => __( 'e.g: Lisbon', 'gofetch-jobs' ),
					'data-default'   => __( 'Anywhere', 'gofetch-jobs' ),
					'data-core-name' => 'location',
				),
				'tip'   => __( 'The location for the jobs being imported.', 'gofetch-jobs' ),
				'desc'  => '<br/><img class="goft-powered-by-google" src="'. esc_url( GoFetch_JR()->plugin_url() . '/includes/admin/assets/images/powered_by_google_on_white_hdpi.png' ) . '"">',
				'value' => $this->get_default_value_for_meta( '_jr_address' ),
			),
			array(
				'title' => __( 'Company', APP_TD ),
				'name'  => 'meta[_Company]',
				'value' => $this->get_default_value_for_meta( '_Company' ),
			),
			array(
				'title' => __( 'Company URL', APP_TD ),
				'name'  => 'meta[_CompanyURL]',
				'value' => $this->get_default_value_for_meta( '_CompanyURL' ),
			),
			array(
				'title'  => __( 'Logo', 'gofetch-jobs' ),
				'name'   => 'meta[_company_logo]',
				'type'   => 'custom',
				'value'  => $this->get_default_value_for_meta( '_company_logo' ),
				'extra' => array(
					'data-core-name' => 'logo',
				),
				'render' => array( $this, 'logo_uploader' ),
				'tip' => __( 'Company logo for the jobs being imported.', 'gofetch-jobs' ),
			),
			array(
				'title' => __( 'How To Apply', APP_TD ),
				'name'  => 'meta[_how_to_apply]',
				'type'  => 'textarea',
				'extra' => array(
					'class'   => 'large-text',
					'rows'    =>  5,
					'cols'    => 10,
					'section' => 'meta',
				),
				'value' => $this->get_default_value_for_meta( '_how_to_apply' ),
				'desc' => __( 'HTML is allowed.', 'gofetch-jobs' ) .
						  ' ' . __( 'You may use the following placeholder variable within this field. It MUST have the percentage signs wrapped around it with no spaces.', 'gofetch-jobs' ) .
					  	  '<br/><br/>' . sprintf( __( '%s This placeholder will be replaced by the respective external job URL.', 'gofetch-jobs' ), '<code>%external_apply_to_url%</code>' ),
			),
		);

		$defaults = array(
			'title' => '',
			'name'  => '',
			'type'  => 'text',
			'extra' => array(
				'class'   => 'regular-text',
				'section' => 'meta',
				'default' => '',
			),
		);

		$final_fields = array();

		foreach ( $fields as $field ) {
			$final_fields[] = wp_parse_args( $field, $defaults );
		}

		return $final_fields;
	}

	/**
	 * Outputs the featured in listing meta field.
	 */
	public function output_featured_listing_meta_field( $output ) {

		$atts = array(
			'type'  => 'checkbox',
			'name'  => 'meta[_jr_featured-listings]',
			'extra' => array(
				'section'      => 'meta',
				'data-default' => '1'
			),
			'value' => '1',
		);

		return scbForms::input( $atts );
	}

	/**
	 * Outputs the featured in category meta field.
	 */
	public function output_featured_category_meta_field( $output ) {

		$atts = array(
			'type'  => 'checkbox',
			'name'  => 'meta[_jr_featured-cat]',
			'extra' => array(
				'section'      => 'meta',
				'data-default' => '1'
			),
			'value' => '1',
		);

		return scbForms::input( $atts );
	}

	/**
	 * Geolocation meta fields.
	 */
	public function geocomplete_hidden_fields( $fields ) {

		return array(
			'geo_short_address'         => 'formatted_address',
			'geo_address'               => 'formatted_address',
			'geo_country'               => 'country',
			'geo_short_address_country' => 'country_short',
			'_jr_geo_latitude'          => 'lat',
			'_jr_geo_longitude'         => 'lng',
			'_jr_address'               => 'formatted_address',
		);

	}

	/**
	 * Output additional form hidden fields.
	 */
	public function other_hidden_fields( $content ) {

		$fields = array(
			'_company_logo_id' => array(
				'name'    => 'meta[_company_logo_id]',
				'type'    => 'hidden',
    		),
			'_jr_featured-listings_start_date' => array(
				'name'  => 'meta[_jr_featured-listings_start_date]',
				'type'  => 'hidden',
				'value' => $this->get_default_value_for_meta( '_jr_featured-listings_start_date' ),
    		),
			'_jr_featured-listings_duration' => array(
				'name'    => 'meta[_jr_featured-listings_duration]',
				'type'    => 'hidden',
				'value' => $this->get_default_value_for_meta( '_jr_featured-listings_duration' ),
    		),
			'_jr_featured-cat_start_date' => array(
				'name'    => 'meta[_jr_featured-cat_start_date]',
				'type'    => 'hidden',
				'value' => $this->get_default_value_for_meta( '_jr_featured-cat_start_date' ),
    		),
			'_jr_featured-cat_duration' => array(
				'name'    => 'meta[_jr_featured-cat_duration]',
				'type'    => 'hidden',
				'value' => $this->get_default_value_for_meta( '_jr_featured-cat_duration' ),
    		),
		);

    	foreach( $fields as $field => $atts ) {
    		$content .= $this->input( $atts );
    	}

    	return $content;
	}

	/**
	 * Renders the provider logo uploader field.
	 */
	public function logo_uploader() {

		$field = array(
			'name'  => 'meta[_company_logo]',
			'type'  => 'text',
			'extra' => array(
				'class'              => 'goft-company-logo goft-image regular-text',
				'placeholder'        => 'e.g: google.png',
				'section'            => 'meta',
				'data-image-id-name' => 'meta[_company_logo_id]',
			),
			'tip'   => __( 'Company logo for the jobs being imported', 'gofetch-jobs' ),
			'value' => ( ! empty( $_POST['meta[_company_logo]'] ) ? $_POST['meta[_company_logo]'] : '' ),
			'desc'  => html( 'input', array( 'type' => 'button', 'name' => 'upload_company_logo', 'class' => 'goft-company-logo goft-upload button-secondary', 'value' => __( 'Browse...', 'gofetch-jobs' ) ) ),
		);

		return $this->image_uploader( $field, 'goft-company-logo' );
	}

	/**
	 * The default value to use on a given meta field.
	 */
	public function default_value_for_field( $value, $field ) {
		global $jr_options;

		switch ( $field ) {

			case '_jr_job_duration':
			case '_jr_featured-listings_duration':
			case '_jr_featured-cat_duration':
				$value = $jr_options->jr_jobs_default_expires;
				break;

			case '_jr_featured-listings_start_date':
			case '_jr_featured-cat_start_date':
				$value = current_time('mysql');
				break;

			case '_how_to_apply':
				$value = sprintf( '<a href="%1$s" class="" rel="nofollow" target="_blank">%2$s</a>', '%external_apply_to_url%', __( 'Click Here to Apply', 'gofetch-jobs' ) );
				break;

		}
		return $value;
	}

	/**
	 * Default to use on a given taxonomy.
	 */
	public function default_value_for_tax( $value, $tax, $slug = '' ) {

		switch ( $tax ) {

			case APP_TAX_TYPE:
			case APP_TAX_CAT:

				$args = array(
					'number'     => 1,
					'fields'     => 'id=>slug',
					'hide_empty' => false
				);

				if ( $slug ) {
					$args['slug'] = $slug;
				}

				$terms = get_terms( $tax, $args );

				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$value = reset( $terms );
				}
				break;

		}
		return $value;
	}

	/**
	 * Outputs the date interval settings.
	 */
	public function output_expiry_field() {

		$atts = array(
			'type'  => 'text',
			'name'  => 'meta[_jr_job_duration]',
			'extra' => array(
				'section'      => 'meta',
				'class'        => 'span_date meta-job-expires',
				'style'        => 'width: 120px;',
				'placeholder'  => __( 'click to choose...', 'gofetch-jobs' ),
				'readonly'     => true,
				'data-default' => $this->get_default_value_for_meta( '_job_expires' ),
				),
			'desc'  => html( 'a', array( 'class' => 'button clear_span_dates', 'data-goft_parent' => 'meta-job-expires' ), __( 'Clear', 'gofetch-jobs' ) ),
			'value' => $this->get_default_value_for_meta( '_job_expires' ),
		);
?>
		<script>
			jQuery(document).ready(function($) {

				// Date picker.
				$('.meta-job-expires').datepicker({
					dateFormat: 'yy-mm-dd',
					changeMonth: true,
				});

			});
		</script>
<?php
		return $this->input( $atts );
	}

	/**
	 * Replace template taxonomy settings term ID's with term slugs for older 'Go Fetch Jobs' versions.
	 *
	 * @since 2.0.
	 */
	public function backwards_compat_tax_input( $settings ) {

		if ( empty( $settings['tax_input'] ) ) {
			return $settings;
		}

		foreach( $settings['tax_input'] as $tax => $term_id ) {

			if ( ! is_numeric( $term_id ) ) {
				continue;
			}

			$term = get_term_by( 'id', $term_id, $tax );
			$tax_input[ $tax ] = $term->slug;
		}

		if ( ! empty( $tax_input) ) {
			$settings['tax_input'] = $tax_input;
		}
		return $settings;
	}

}

new GoFetch_JR_Specific_Settings;
