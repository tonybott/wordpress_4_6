<?php
/**
 * Sets up the write panels used by the schedules (custom post types).
 *
 * @package GoFetch/Admin/Providers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Schedules meta boxes base class.
 */
class GoFetch_JR_RSS_Providers{

	public static function valid_item_tags() {

		$fields = array(
			'location',
			'geolocation',
			'latitude',
			'longitude',
			'company',
			'logo',
			//'salary',
			//'type',
			//'category',
		);
		return apply_filters( 'goft_jobs_providers_valid_item_tags', $fields );
	}

	public static function valid_regexp_tags() {

		$patterns = array(
			'location' => array(
				// e.g: <p>Location: London</p> OR Location : London <br/>
				'/Location.*?:(.*?)<.*?>/is',
				// e.g: <strong>Headquarters:</strong> New York, NY <br />
				'/Headquarters.*?:<.*?>(.*?)<.*?>/is',
				// e.g: <b>Location: </b><br/> San Francisco <br/>
				'/Location.*?<.*?>(.*?)<.*?>/is'
			),
			'company' => array(
				// e.g: Advertiser : Google <br/>
				'/Advertiser.*?:(.*?)<.*?>/is',
				// e.g: <b>Company: </b><br/> Google <br/>
				'/Company.*?<.*?>(.*?)<.*?>/is',
				// e.g: <p>Location: London</p> OR Location : London <br/>
				'/Company.*?:(.*?)<.*?>/is',
				// e.g: <b>Posted by: </b> Google </p>
				'/Posted by.*?<.*?>(.*?)<.*?>/is',
			),
			'salary' => array(
				// e.g: Salary : £40,000 - £55,000 per year
				'/Salary.*?:(.*?)$/is',
				// e.g: <p>Salary/Rate: 50.000 - 80.000</p>
				'/Salary\/Rate.*?:(.*?)<.*?>/is',
			),
		);
		return apply_filters( 'goft_jobs_providers_valid_regexp_tags', $patterns );
	}

	/**
	 * Retrieves a list of providers and their details.
	 *
	 * Weight: Higher is better.
	 */
	public static function get_providers( $provider = '' ) {

		$providers = array(
			'freelancewritinggigs.com' => array(
				'website'     => 'http://www.freelancewritinggigs.com/',
				'logo'        => 'https://jobmob.co.il/images/articles/freelance-marketplaces/freelancewritinggigs-freelance-marketplace-logo.png',
				'description' => 'Freelance Writing Job Board',
				'feed'        => array(
					'base_url'   => 'http://www.freelancewritinggigs.com/?feed=job_feed',
					'search_url' => 'http://www.freelancewritinggigs.com/freelance-writing-job-ads/',
					'example'    => 'http://www.freelancewritinggigs.com/?feed=job_feed&job_categories=blogging-jobs',
					'sample'     => 'http://www.freelancewritinggigs.com/?feed=job_feed&job_types=contract%2Cfreelance&[etc...]',
					// Meta provided by feed.
					'meta'       => array(
						'company',
						'location'
					),
					// Mappings for known/custom tags used in feed.
					'tag_mappings' => array(
						'type' => 'job_type',
					),
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 'search_keywords' => '' ),
						'location' => array( 'search_location' => '' ),
						//'category' => 'job_categories',
						//'type'     => 'job_types',
					),
					'default' => true,
				),
				'category' => __( 'Blogging', 'gofetch-jobs' ),
				'weight'   => 7,
			),
			'jobs.wordpress.net' => array(
				'website'     => 'http://jobs.wordpress.net/',
				'logo'        => 'https://s.w.org/about/images/logos/wordpress-logo-notext-rgb.png',
				'description' => 'WordPress related Job Postings',
				'feed' => array(
					'base_url'   => 'http://jobs.wordpress.net/feed/',
					'search_url' => 'http://jobs.wordpress.net/?s=',
					'example'    => 'http://jobs.wordpress.net/search/design/feed/rss2/',
					'sample'     => 'http://jobs.wordpress.net/search/design/feed/rss2/',
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 's' => '' ),
					),
					// Fixed RSS feeds examples.
					'examples' => array(
						__( 'Latest Jobs', 'gofetch-jobs' )                    => 'http://jobs.wordpress.net/feed/',
						__( 'Latest Design Jobs', 'gofetch-jobs' )             => 'http://jobs.wordpress.net/job_category/design/feed/',
						__( 'Latest Plugin Development Jobs', 'gofetch-jobs' ) => 'http://jobs.wordpress.net/job_category/plugin-development/feed/',
					),
				),
				'weight'   => 8,
				'category' => __( 'WordPress', 'gofetch-jobs' ),
			),
			'jobs.theguardian.com' => array(
				'website'     => 'https://jobs.theguardian.com/',
				'logo'        => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/The_Guardian.svg/2000px-The_Guardian.svg.png',
				'description' => 'Great jobs on the Guardian Jobs site',
				'feed' => array(
					'base_url'   => 'https://jobs.theguardian.com/jobsrss/',
					'search_url' => 'https://jobs.theguardian.com/jobs/',
					'example'    => 'https://jobs.theguardian.com/jobsrss/?keywords=arts&countrycode=GB',
					'sample'     => 'https://jobs.theguardian.com/jobsrss/?JobLevel=19&keywords=arts&countrycode=GB&[etc...]',
					'meta'       => array(
						'logo',
					),
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'company'  => '/(?(?!.*?:\s.*?:\s.*?)(.*?)-|.*?:(.*?):\s)/is',    // e.g: £27,499 - £33,569: Google:
						'location' => '/\.([^.]+)$/is',                                   // e.g: £27,499 - £33,569: Google: blah blah. San Francisco
					),
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 'keywords' => '' ),
						'location' => array( 'radialtown' => '' ),
					),
					'default' => true,
				),
				'weight'   => 9,
				'category' => __( 'Generic', 'gofetch-jobs' ),
			),
			'us.jobs.com' => array(
				'website'     => 'http://us.jobs/',
				'logo'        => 'http://images.us.jobs/usdj/logos/usjobslogo.png',
				'description' => 'US.jobs - National Labor Exchange.',
				'feed' => array(
					'base_url'   => 'http://us.jobs/rss.asp?si=1310658541&so=relevance',
					'search_url' => 'http://us.jobs/results.asp',
					'example'    => 'http://us.jobs/rss.asp?si=1310632245&so=relevance',
					'sample'     => 'http://us.jobs/rss.asp?si=1310646251&so=relevance&kw=design[etc...]',
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 'kw' => '' ),
						'location' => array( 'zc' => '' ),
					),
					'base-data-less' => array(
						'description',
					),
				),
				'weight'      => 6,
				'category'    => __( 'Generic', 'gofetch-jobs' ),
			),
			'trabajos.com' => array(
				'website'     => 'https://www.trabajos.com',
				'logo'        => 'https://trabajos.hvimg.com/img/logo_trabajos.gif',
				'description' => 'Ofertas de Trabajo, Bolsa de Empleo',
				'feed' => array(
					'base_url' => 'https://www.trabajos.com/rss',
					'example'  => 'https://www.trabajos.com/rss/busquedas/2-0-0-0/',
					'examples' => array(
						__( 'Ventas - Comercial, Europa', 'gofetch-jobs' ) => 'https://www.trabajos.com/rss/busquedas/90-0-110-0/',
						__( 'Ingenierías, España', 'gofetch-jobs' )        => 'https://www.trabajos.com/rss/busquedas/2-0-0-0/',
						__( 'Diseño y Artes Gráficas', 'gofetch-jobs' )    => 'https://www.trabajos.com/rss/busquedas/40-0-100-0/'
					),
				),
				'weight'   => 1,
				'category' => __( 'Generic', 'gofetch-jobs' ),
			),
			'expressoemprego.pt' => array(
				'website'     => 'http://expressoemprego.pt/',
				'logo'        => 'https://pbs.twimg.com/profile_images/3116193991/c5507f53c7632991b9b163b39bb3d3f3_400x400.png',
				'description' => 'A sua Carreira é o nosso Trabalho',
				'feed' => array(
					'base_url' => 'http://expressoemprego.pt/rss',
					'meta'     => array(
						'logo',
					),
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'company'  => '/^(.*?)\|/is',  // e.g: Amazon | Lisbon
						'location' => '/\|(.*?)\|/is', // e.g: Amazon | Lisbon
					),
					'examples'    => array(
						__( 'Latest 50 Jobs', 'gofetch-jobs' )        => 'http://www.expressoemprego.pt/rss/ultimas-ofertas',
						__( 'Latest Internet Jobs', 'gofetch-jobs' )  => 'http://expressoemprego.pt/rss/internet',
						__( 'Latest Jobs in Lisbon', 'gofetch-jobs' ) => 'http://expressoemprego.pt/rss/lisboa',
					),
				),
				'weight'   => 1,
				'category' => __( 'Generic', 'gofetch-jobs' ),
			),
			'cargadetrabalhos.net' => array(
				'website'     => 'http://www.cargadetrabalhos.net/',
				'logo'        => 'http://www.cargadetrabalhos.net/wp-content/themes/bluekino%28en%29/images/cdtlogosombratop.jpg',
				'description' => 'Emprego na área da comunicação',
				'feed' => array(
					'base_url' => 'http://www.cargadetrabalhos.net/feed/',
					'default'  => true,
				),
				'weight'   => 6,
				'category' => __( 'Marketing', 'gofetch-jobs' ),
			),
			'jobs.marketinghire.com' => array(
				'website'     => 'http://jobs.marketinghire.com/',
				'logo'        => 'http://www.marketinghire.com/templates/ja_university/images/logo.png',
				'description' => 'All Marketing jobs',
				'feed' => array(
					'base_url'   => 'http://jobs.marketinghire.com/jobs/?display=rss',
					'search_url' => 'http://jobs.marketinghire.com/jobs',
					'example'    => 'http://jobs.marketinghire.com/jobs/?display=rss&keywords=specialist&resultsPerPage=25',
					'sample'     => 'http://jobs.marketinghire.com/jobs/?display=rss&keywords=specialist&filter=&[etc...]',
					'meta'       => array(
						'logo',
					),
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'location' => '/^(.*?),/is', // e.g: Lisbon, etc...
					),
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 'keywords' => '' ),
						'location' => array( 'place' => '' ),
					),
					'default' => true,
				),
				'weight'   => 7,
				'category' => __( 'Marketing', 'gofetch-jobs' ),
			),
			__( 'Other', 'gofetch-jobs' ) => array(
				'website'     => '#',
				'logo'        => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/46/Generic_Feed-icon.svg/500px-Generic_Feed-icon.svg.png',
				'description' => 'Use RSS feed from other provider',
				'feed'        => array(
					'base_url'    => '#',
				),
				'category'    => __( 'Other', 'gofetch-jobs' ),
				'weight'   => 99,
			),

		);
		$providers = apply_filters( 'goft_jobs_providers', $providers );

		if ( $provider ) {

			if ( empty( $providers[ $provider ] ) ) {

				if ( $parent = self::get_provider_parent( $provider ) ) {
					return $parent;
				}

				return array();
			} else {
				return $providers[ $provider ];
			}

		}
		return $providers;
	}

	/**
	 * Retrieves the RSS feed setup instructions for a given provider.
	 */
	public static function setup_instructions_for( $provider ) {

		$setup = $header = $multi_region = $steps_li = $skip_copy_url = '' ;

		$steps = 0;

		$data = self::get_providers( $provider );

		if ( empty( $data['feed']['search_url'] ) ) {
			$data['feed']['search_url'] = $data['feed']['base_url'];
		}

		// __Header.

		if ( ! empty( $data['feed']['base_url'] ) ) {
			$header  = html( 'a', array( 'href' => esc_url( $data['website'] ), 'target' => '_blank' ), html( 'img', array( 'src' => esc_url( $data['logo'] ), 'class' => 'provider-logo-orig' ) ) );
			$header .= html( 'p', html( 'em', $data['description'] ) ) ;
		}

		$header = html( 'div class="provider-header"', $header );

		// __Meta.

		$base_data = array(
			'title'       => __( 'Title', 'gofetch-jobs' ),
			'description' => __( 'Description', 'gofetch-jobs' ),
			'date'        => __( 'Date', 'gofetch-jobs' )
		);

		$meta_data = array();

		if ( ! empty( $data['feed']['base-data-less'] ) ) {
			$base_data = array_diff( array_keys( $base_data ), $data['feed']['base-data-less'] );
		}

		if ( ! empty( $data['feed']['meta'] ) ) {
			$meta_data = array_merge( $meta_data, $data['feed']['meta'] );
		}

		if ( ! empty( $data['feed']['meta-less'] ) ) {
			$meta_data = array_diff( $meta_data, $data['feed']['meta-less'] );
		}

		if ( ! empty( $data['feed']['tag_mappings'] ) ) {
			$meta_data = array_merge( $meta_data, array_keys( $data['feed']['tag_mappings'] ) );
		}

		if ( ! empty( $data['feed']['regexp_mappings'] ) ) {
			$meta_data = array_merge( $meta_data, array_keys( $data['feed']['regexp_mappings'] ) );
		}

		$meta_li = '';

		foreach( $base_data as $field ) {
			$meta_li .= html( 'li', ucfirst( $field ) );
		}

		$data_info = html( 'p', sprintf( __( 'Data provided in RSS feeds: %s', 'gofetch-jobs' ), html( 'ul', $meta_li ) ) );

		if ( ! empty( $meta_data ) ) {
			$meta_data = array_intersect( self::valid_item_tags(), $meta_data );

			$meta_li = '';

			foreach( $meta_data as $field ) {
				$meta_li .= html( 'li', ucfirst( $field ) );
			}

			if ( $meta_li ) {
				$data_info .= html( 'p', sprintf( __( 'Other data <small>(not always available)</small>: %1$s %2$s', 'gofetch-jobs' ), GoFetch_JR_Admin::limited_plan_warn(), html( 'ul', $meta_li ) ) );
			}
		}

		$data_info = html( 'div class="provider-data secondary-container"', $data_info );

		// __Multi Region.

		$multi_region = '';

		if ( isset( $data['multi-region'] ) ) {

			$multi_region = '<br/>' . __( 'This is a multi region jobs site. These instructions are meant for a specific country site but they should also work with any of the other available country sites.', 'gofetch-jobs' );

			if ( ! empty( $data['multi-region'] )  ) {
				$multi_region .=  '   ' . html( 'span', html( 'a', array( 'href' => '#', 'class' => 'provider-expand-multi-region-details', 'data-child' => 'multi-region-details', 'data-default' => __( 'Info', 'gofetch-jobs' ) ), __( 'Info', 'gofetch-jobs' ) ) );
				$multi_region .= sprintf( '<p class="multi-region-details secondary-container">%s</p>', $data['multi-region'] );
			}

		}

		switch ( $provider ) {

			case 'idealist.org':
			case 'technojobs.co.uk':
			case 'freelancewritinggigs.com':
			case 'jobs.smashingmagazine.com':
			case 'dribbble.com':
			case 'mediabistro.com':
			case 'totaljobs.com':
			case 'authenticjobs.com':
			case 'jobs.gamasutra.com':
			case 'craigslist.org':
			case 'uk.dice.com':
			case 'sap.dice.com':
			case 'indeed.com':
			case 'careerjet.com':
			case 'jobs.marketinghire.com':
			case 'reed.co.uk':
			case 'us.jobs.com':

				$steps_li .= html( 'li', sprintf( __( 'Visit the provider job search page by clicking <a href="%2$s" target="_blank">here</a>.</p>', 'gofetch-jobs' ), ++$steps, esc_url( $data['feed']['search_url'] ) ) );
				$steps_li .= html( 'li', sprintf( __( 'Setup your jobs criteria and click the search button or \'Enter\'.</p>', 'gofetch-jobs' ), ++$steps ) );
				$steps_li .= html( 'li', sprintf( __( '<strong>OR</strong> ... click on any existing pre-set filters (if available) like <code>Jobs by Location</code>, <code>Jobs by Title</code>, etc...</p>', 'gofetch-jobs' ), ++$steps ) );

				switch ( $provider ) {

					case 'idealist.org':
					case 'freelancewritinggigs.com':
						$steps_li .= html( 'li', sprintf( __( 'After you get the results click the <code>RSS</code> link/button on top of the search results.</p>', 'gofetch-jobs' ), ++$steps ) );
						break;

					case 'jobs.gamasutra.com':
					case 'jobs.theguardian.com':
						$steps_li .= html( 'li', sprintf( __( 'After you get the results scroll to the bottom of the page and click the <code>Subscribe/Subscribe to RSS</code> link.</p>', 'gofetch-jobs' ), ++$steps ) );
						break;

					case 'technojobs.co.uk':
						$steps_li .= html( 'li', sprintf( __( 'After you get the results click the <code>%2$s</code> link.</p>', 'gofetch-jobs' ), ++$steps, __( 'Subscribe to an RSS Feed for this search', 'gofetch-jobs' ) ) );
						$steps_li .= html( 'li', sprintf( __( 'You will be taken to a new page that contains the URL of the generated RSS feed. Look for it under the <code>%2$s</code> text.</p>', 'gofetch-jobs' ), ++$steps, __( 'The address for your RSS feed is here:', 'gofetch-jobs' ) ) );

						$skip_copy_url = true;
						break;

					case 'authenticjobs.com':
						$steps_li .= html( 'li', sprintf( __( 'After you get the results click the <code>Email/RSS Notifications</code> button on top of the search results.</p>', 'gofetch-jobs' ), ++$steps ) );
						$steps_li .= html( 'li', sprintf( __( 'Click the <code>RSS</code> option and copy & paste the URL to your browser address bar.</p>', 'gofetch-jobs' ), ++$steps ) );
						break;

					case 'reed.co.uk':
						$steps_li .= html( 'li', sprintf( __( 'After you get the results replace everything in your URL before the <code>?</code> with the RSS feed URL <code>%2$s</code>.</p>', 'gofetch-jobs' ), ++$steps, $data['feed']['base_url'] . '?' ) );
						$steps_li .= html( 'li', sprintf( __( 'If you specified <code>location</code> as a criteria you need to manually add it to the URL parameters <code>%2$s</code>.</p>', 'gofetch-jobs' ), ++$steps, add_query_arg( 'location', 'london', $data['feed']['base_url'] ) ) );
						break;

					case 'us.jobs.com':
						$steps_li .= html( 'li', sprintf( __( 'After you get the results, on the right sidebar, click <code>%2$s</code>.</p>', 'gofetch-jobs' ), ++$steps, __( 'Save as RSS feed', 'gofetch-jobs' ) ) );
						$steps_li .= html( 'li', sprintf( __( 'Below, you will find a <code>%2$s</code> that you can click.', 'gofetch-jobs' ), ++$steps, 'RSS 2.0 feed' ) );
						break;

					case 'jobs.smashingmagazine.com':
					case 'mediabistro.com':
					case 'dribbble.com':
					case 'totaljobs.com':
					case 'indeed.com':
					case 'careerjet.com':
						$steps_li .= html( 'li', sprintf( __( 'After you get the results replace everything in your URL before the <code>?</code> with the RSS feed URL <code>%2$s</code>.</p>', 'gofetch-jobs' ), ++$steps, $data['feed']['base_url'] . '?' ) );
						break;

					default:
						$steps_li .= html( 'li', sprintf( __( 'After you get the results click the <code>RSS</code> (<span class="dashicons dashicons-rss"></span>) link/button/icon on the bottom.</p>', 'gofetch-jobs' ), ++$steps ) );
						break;

				}
				break;

			case 'krop.com':
			case 'coroflot.com':
			case 'jobs.problogger.net':
			case 'cargadetrabalhos.net':
				$setup .= __( '<p>Unfortunately this provider does not allow customizing the RSS feed.</p><br/>', 'gofetch-jobs' );
				break;

			case 'jobs.wordpress.net':
				$setup .= __( '<p>Unfortunately this provider does not allow customizing the RSS feed but provides keywords and categorized feeds.</p><br/>', 'gofetch-jobs' );
				$steps_li .= html( 'li', sprintf( __( 'Visit the provider jobs page by clicking <a href="%2$s" target="_blank">here</a>.</p>', 'gofetch-jobs' ), ++$steps, esc_url( $data['feed']['base_url'] ) ) );
				$steps_li .= html( 'li', sprintf( __( 'Click on any of the RSS icons (<span class="dashicons dashicons-rss"></span>) over each of the job results groups.</p>', 'gofetch-jobs' ), ++$steps ) );
				$steps_li .= html( 'li', sprintf( __( '<strong>OR</strong> ... do a search for the keywords you want to use and click on the RSS icon (<span class="dashicons dashicons-rss"></span>) on top of the search results.</p>', 'gofetch-jobs' ), ++$steps ) );
				break;

			case 'weworkremotely.com':
				$setup .= __( '<p>Unfortunately this provider does not allow customizing the RSS feed but provides categorized feeds.</p><br/>', 'gofetch-jobs' );
				$steps_li .= html( 'li', sprintf( __( 'Visit the provider jobs page by clicking <a href="%2$s" target="_blank">here</a>.</p>', 'gofetch-jobs' ), ++$steps, esc_url( $data['feed']['search_url'] ) ) );
				$steps_li .= html( 'li', sprintf( __( 'Click on any of the RSS icons (<span class="dashicons dashicons-rss"></span>) over each of the job results groups.</p>', 'gofetch-jobs' ), ++$steps ) );
				break;

			case 'expressoemprego.pt':
			case 'monster.com.hk':
			case 'dice.com':
				$setup .= __( '<p>Unfortunately this provider does not allow customizing the RSS feed but provides categorized feeds.</p><br/>', 'gofetch-jobs' );
				$steps_li .= html( 'li', sprintf( __( 'Visit the provider RSS feeds page by clicking <a href="%2$s" target="_blank">here</a>.</p>', 'gofetch-jobs' ), ++$steps, esc_url( $data['feed']['search_url'] ) ) );
				$steps_li .= html( 'li', sprintf( __( 'Click on any of the feeds from the RSS feed list.</p>', 'gofetch-jobs' ), ++$steps ) );
				break;

			case 'jobs.ac.uk':
				$setup .= __( '<p>Unfortunately this provider does not allow customizing the RSS feed but provides categorized feeds.</p><br/>', 'gofetch-jobs' );
				$steps_li .= html( 'li', sprintf( __( 'Visit the provider RSS feeds page by clicking <a href="%2$s" target="_blank">here</a>.</p>', 'gofetch-jobs' ), ++$steps, esc_url( $data['feed']['base_url'] ) ) );
				$steps_li .= html( 'li', sprintf( __( 'Click on any of the feed categories from the list.</p>', 'gofetch-jobs' ), ++$steps ) );
				$steps_li .= html( 'li', sprintf( __( 'You will be taken to a new page that contains all the available RSS feeds.</p>', 'gofetch-jobs' ), ++$steps ) );
				$steps_li .= html( 'li', sprintf( __( 'Click on any of the feeds from the RSS feed list.</p>', 'gofetch-jobs' ), ++$steps ) );
				break;

			case 'trabajos.com':
				$setup .= __( '<p>Unfortunately this provider does not allow customizing the RSS feed directly but provides their own custom builder on site.</p><br/>', 'gofetch-jobs' );
				$steps_li .= html( 'li', sprintf( __( 'Visit the provider RSS feeds page by clicking <a href="%2$s" target="_blank">here</a>.</p>', 'gofetch-jobs' ), ++$steps, esc_url( $data['feed']['base_url'] ) ) );
				$steps_li .= html( 'li', sprintf( __( 'From the dropdown lists choose the options that best fit the jobs you want to import and click the button.</p>', 'gofetch-jobs' ), ++$steps ) );
				$steps_li .= html( 'li', sprintf( __( 'You will be taken to a new page that contains your custom RSS feed.</p>', 'gofetch-jobs' ), ++$steps ) );
				break;

			case 'careerbuilder.com':
				$steps_li .= html( 'li', sprintf( __( 'Visit the provider RSS page by clicking <a href="%2$s" target="_blank">here</a>.</p>', 'gofetch-jobs' ), ++$steps, esc_url( $data['feed']['search_url'] ) ) );
				$steps_li .= html( 'li', sprintf( __( 'Scroll to the bottom of the page, setup your RSS Feed using the RSS builder<strong>***</strong> and click <code>%2$s</code>.</p>', 'gofetch-jobs' ), ++$steps, __( 'Get Jobs via RSS', 'gofetch-jobs' ) ) );
				$steps_li .= html( 'li', sprintf( __( 'Optionally, you can just click your preferred RSS Feed from the pre-set list.</p>', 'gofetch-jobs' ), ++$steps ) );

				$notes = sprintf( __( '<p><strong>*** Note:</strong> At the time these instructions were written, the RSS builder was generating RSS feeds with an invalid domain name. If you don\'t get a valid RSS feed URL similar to the example, please replace the invalid domain <code>%1$s</code> with this one <code>%2$s</code>.</p>', 'gofetch-jobs' ), esc_url( 'http://www.careerbuilder.se/RTQ/rss20.aspx?' ), esc_url( 'http://www.careerbuilder.com/RTQ/rss20.aspx?geoip=false&' ) );
				break;

			case __( 'Other', 'gofetch-jobs' ):
				$other_li  = html( 'li', __( 'Visit any job site jobs search page, click \'View Source\' on your browser and search for the <code>RSS</code> word. In case you find matches look for any RSS related links near it.</p>', 'gofetch-jobs' ) );
				$other_li .= html( 'li', __( 'Google directly for <code>my job site provider + RSS feeds</code></p>', 'gofetch-jobs' ) );
				$other_li .= html( 'li', sprintf( __( 'Search for job sites directly from an RSS Reader like <a href="%1$s" target="_blank">Feedly</a>.</p>', 'gofetch-jobs' ), 'https://feedly.com' ) );

				$setup .= html( 'p', sprintf( __( 'To use other job feed providers outside the providers list try the following:', 'gofetch-jobs' ) ), html( 'ul', $other_li ) );

				$setup .= '<br/>' . html( 'p', __( 'In any case, most job sites usually offer a pre-set list of RSS feeds or a custom RSS builder based on a job search.', 'gofetch-jobs' ) .
				          ' ' . __( 'Just follow the instructions for similar providers and you should be ready to go.', 'gofetch-jobs' ) );
				break;

			default:

				$steps_li .= html( 'li', sprintf( __( 'Visit the provider job search page by clicking <a href="%2$s" target="_blank">here</a>.</p>', 'gofetch-jobs' ), ++$steps, esc_url( $data['feed']['search_url'] ) ) );
				$steps_li .= html( 'li', sprintf( __( 'Setup your jobs criteria and click the search button.</p>', 'gofetch-jobs' ), ++$steps ) );

		}

		if ( $steps ) {

			if ( ! $skip_copy_url ) {
				$steps_li .= html( 'li', sprintf( __( 'Copy the RSS feed URL from your browser address bar.</p>', 'gofetch-jobs' ), ++$steps ) );
			}

			if ( ! empty( $data['feed']['sample'] ) ) {

				$samples = '';

				foreach( (array) $data['feed']['sample'] as $sample ) {
					$samples .= $samples ? ' ' . __( 'OR', 'gofetch-jobs' ) . ' ' : '';
					$samples .= sprintf( '<a href="%1$s" target="_blank">%1$s</a>', $sample );
				}

				$steps_li .= html( 'li', sprintf( __( 'You should have an URL similar to this %2$s (depending on your criteria).</p>', 'gofetch-jobs' ), ++$steps, $samples ) );
			}

			$steps_li .= html( 'li', sprintf( __( 'Paste your new RSS feed URL on the <code>URL</code> input field below.', 'gofetch-jobs' ), ++$steps ) );

			$setup .= html( 'ol', $steps_li );
		}

		// Display single example.
		if ( ! empty( $data['feed']['example'] ) ) {
			$setup .= '<br/>';
			$setup .= html( 'p', html( 'strong', __( 'Example', 'gofetch-jobs' ) ) . self::copy_paste() );
			$setup .= html( 'p class="provider-other-feeds"', sprintf( '<span class="dashicons dashicons-rss"></span> <a href="%1$s" class="provider-rss" target="_blank">%1$s</a></p>', esc_url( $data['feed']['example'] ) ) );
		}

		if ( ! empty( $data['feed']['default'] ) ) {
			$data['feed']['fixed'] = array( __( 'Latest Jobs', 'gofetch-jobs' ) => $data['feed']['base_url'] );
		}

		// Display the fixed RSS feeds.
		if ( ! empty( $data['feed']['fixed'] ) ) {

			$setup .= '<br/>';

			if ( count( $data['feed']['fixed'] ) === 1 ) {

				if ( $steps ) {
					$setup .= sprintf( __( '<p><strong>OR</strong> ... use the default RSS feed from the provider %1$s:</p>', 'gofetch-jobs' ), self::copy_paste(), ++$steps );
				} else {
					$setup .= sprintf( __( 'Click on your preferred RSS feed from the provider pre-set list:</p>', 'gofetch-jobs' ), ++$steps );
				}

			} else {
				$setup .= sprintf( __( 'Choose your preferred RSS feed from the provider list %1$s:</p>', 'gofetch-jobs' ), self::copy_paste(), ++$steps );
			}

			$setup .= '<br/>';

			foreach( $data['feed']['fixed'] as $desc => $url ) {
				$setup .= html( 'p class="provider-other-feeds"', sprintf( '<span class="dashicons dashicons-rss"></span> <a href="%2$s" class="provider-rss" target="_blank">%1$s</a></p>', $desc, esc_url( $url ) ) );
			}

			$setup .= '<br/>';

		}

		// Display examples.
		if ( ! empty( $data['feed']['examples'] ) ) {

			$setup .= '<br/><br/>';

			$setup .= html( 'p', sprintf( __( 'Here are some quick ready to use examples: %1$s', 'gofetch-jobs' ), self::copy_paste(), ++$steps ) );

			$setup .= '<br/>';

			foreach( $data['feed']['examples'] as $desc => $url ) {
				$setup .= html( 'p class="provider-other-feeds"', sprintf( '<span class="dashicons dashicons-rss"></span> <a href="%2$s" class="provider-rss" target="_blank">%1$s</a></p>', $desc, esc_url( $url ) ) );
			}

			$setup .= '<br/>';

		}

		if ( ! empty( $notes ) ) {
			$setup .= '<br/>' . $notes;
		}

		// Wrap the manual setup.
		$manual_setup = html( 'p', html( 'a', array( 'href' => '#', 'class' => 'provider-expand-feed-manual-setup', 'data-child' => 'feed-manual-setup', 'data-default' => __( 'Manual Setup Instructions', 'gofetch-jobs' ) ), __( 'Manual Setup Instructions', 'gofetch-jobs' ) ) );

		$setup = $manual_setup . html( 'div class="feed-manual-setup"', $setup );
		$setup = $header . $multi_region . $data_info . $setup;

		// Wrap the builder, if available.
		if ( ! empty( $data['feed']['query_args'] ) ) {

			if ( ! gofj_jr_fs()->is_not_paying() ) {
				$feed_builder = html( 'p', html( 'a', array( 'href' => '#', 'class' => 'provider-expand-feed-builder', 'data-child' => 'feed-builder', 'data-default' => __( 'Use RSS Feed Builder', 'gofetch-jobs' ) ), __( 'Use RSS Feed Builder', 'gofetch-jobs' ) ) );
				$setup .= $feed_builder . html( 'div class="feed-builder"', self::output_rss_feed_builder() );
			} else {
				$setup .= html( 'p', html( 'a', array( 'href' => '#', 'class' => '' ), __( 'Use RSS Feed Builder', 'gofetch-jobs' ) ), ' ' . GoFetch_JR_Admin::limited_plan_warn() );
			}

		}

		return apply_filters( 'goft_jobs_setup_instructions_for', $setup, $provider );
	}

	/**
	 * Outputs a dropdown will all the available RSS providers.
	 */
	public static function output_providers_dropdown( $atts = array() ) {

		$choices = $choices_n = array();

		foreach( self::get_providers() as $name => $data ) {

			$weight = ! empty( $data['weight'] ) ? $data['weight'] : 1;

			$choices_n[ $weight ][] = $name;
		}

		krsort( $choices_n );

		// Retrieve choices sorted by provider weight.
		foreach( $choices_n as $providers ) {

			foreach( $providers as $provider ) {
				$data = self::get_providers( $provider );

				$defaults = array(
					'description' => '',
					'category'    => __( 'Other', 'gofetch-jobs' ),
				);
				$data = wp_parse_args( $data, $defaults );

				$categories[ $data['category'] ][ $provider ] = sprintf( '%1$s %2$s %3$s', $provider, ( isset( $data['multi-region'] ) ? __( '<em>[Multi-Region]</em>', 'gofetch-jobs' ) : '' ), ( ! empty( $data['description'] ) ? sprintf( '<em>(%s)</em>', $data['description'] ) : '' ) );
			}

		}

		ksort( $categories );

		$options = $optgroup = '';

		// Iterate through the categories.
		foreach( $categories as $category => $providers ) {

			foreach( $providers as $provider => $desc ) {
				$options .= html( 'option', array( 'value' => esc_attr( $provider ), 'data-desc' => esc_attr( $desc ) ), $provider . ' ' . $desc );
			}

			$optgroup .= html( 'optgroup', array( 'label' => esc_attr( ucwords( $category) ) ), $options );
			$options  = '';

		}

		$optgroup = html( 'option', array( 'value' => '' ), __( 'Choose an RSS Provider . . .', 'gofetch-jobs' ) ) . $optgroup;

		$defaults = array(
			'name' => 'goftj_rss_providers',
			'size' => '20'
		);
		$atts = wp_parse_args( $atts, $defaults );

		return html( 'select', $atts, $optgroup );
	}

	public static function output_rss_feed_builder() {

		// @todo: maybe allow passing a label from the 'query_args' param.

		$output = '';

		if ( gofj_jr_fs()->is_plan__premium_only('pro') ):

			ob_start();
?>
		 	<p><?php echo __( 'This is a basic builder to help you setup a customized RSS feed. If you need to further refine your RSS feed please read the manual setup instructions.', 'gofetch-wphm' ); ?></p>

		 	<br/>

			<p>
				<label for="feed-url"><strong><?php _e( 'RSS Feed Base URL', 'gofetch-jobs' ); ?></strong></label>
				<input type="text" class="regular-text2" name="feed-url"><a href="#" class="button secondary reset-feed-url" title="<?php echo __( 'Reset to the original Feed URL.', 'gofetch-jobs' ) ?>"><?php echo __( 'Reset', 'gofetch-jobs' ) ?></a>
			</p>

			<br/>

			<p class="params opt-param-keyword">
				<label for="feed-keyword"><strong><?php _e( 'Keyword', 'gofetch-jobs' ); ?></strong></label><span class="feed-param-keyword"></span>
				<input type="text" class="regular-text" name="feed-keyword" data-qarg="feed-param-keyword" placeholder="<?php echo __( 'eg: design, writer, doctor, etc', 'gofetch-jobs' ); ?>">
			</p>

			<p class="params opt-param-location">
				<label for="feed-location"><strong><?php _e( 'Location', 'gofetch-jobs' ); ?></strong></label><span class="feed-param-location"></span>
				<input type="text" class="regular-text" name="feed-location" data-qarg="feed-param-location" placeholder="<?php echo __( 'eg: new york, lisbon, london, etc', 'gofetch-jobs' ); ?>">
			</p>

			<p class="params opt-param-state">
				<label for="feed-state"><strong><?php _e( 'State', 'gofetch-jobs' ); ?></strong></label><span class="feed-param-state"></span>
				<input type="text" class="regular-text" name="feed-state" data-qarg="feed-param-state" placeholder="<?php echo __( 'eg: new york, florida, etc', 'gofetch-jobs' ); ?>">
			</p>

			<div class="clear"></div>

			<p class="params opt-param-type">
				<label for="feed-type"><strong><?php _e( 'Type', 'gofetch-jobs' ); ?></strong></label> <span class="feed-param-type"></span>
				<input type="text" class="regular-text" name="feed-type" data-qarg="feed-param-type" placeholder="<?php echo __( 'eg: fulltime, freelance', 'gofetch-jobs' ); ?>">
			</p>

			<p class="params opt-param-category">
				<label for="feed-category"><strong><?php _e( 'Category', 'gofetch-jobs' ); ?></strong></label> <span class="feed-param-category"></span>
				<input type="text" class="regular-text" name="feed-category" data-qarg="feed-param-category" placeholder="<?php echo __( 'eg: writer, design', 'gofetch-jobs' ); ?>">
			</p>

			<div class="clear"></div><br/>

			<p>
				<label for="provider-rss-custom-placeholder">
					<strong><?php _e( 'Your Custom RSS Feed', 'gofetch-jobs' ); ?></strong>
					<?php echo self::copy_paste(); ?>
				</label>
				 <div class="provider-rss-custom-placeholder"><span class="dashicons dashicons-rss"></span> <a class="provider-rss-custom" href="#" target="_blank"></a></div>
			 </p>

			<input type="hidden" name="feed-param-keyword">
			<input type="hidden" name="feed-param-location">
			<input type="hidden" name="feed-param-state">
			<input type="hidden" name="feed-param-type">
			<input type="hidden" name="feed-param-category">
			<input type="hidden" name="feed-params-sep" value="&amp;">
<?php
			$output = ob_get_clean();

		endif;

		return $output;
	}

	/**
	 * Retrieves a copy&paste HTML message.
	 */
	public static function copy_paste() {
		return '<code class="copy-paste-info"><span class="icon icon-paste"></span> click the link(s) to copy&paste</code>';
	}

	/**
	 * Check for a provider
	 */
	public static function get_provider_parent( $provider_id ) {

		// Check for a parent provider if this is a multi-region provider.
		foreach( self::get_providers() as $id => $data ) {

			if ( ! empty( $data['multi_region_match'] ) && FALSE !== strpos( $provider_id, $data['multi_region_match'] ) ) {
				$provider = self::get_providers( $id );
				$provider['inherit'] = true;

				return $provider;
			}

		}
		return false;
	}

}
