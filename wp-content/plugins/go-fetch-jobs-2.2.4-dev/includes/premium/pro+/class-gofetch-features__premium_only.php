<?php
/**
 * Loads advanced premium features not available on the free version.
 */

if ( gofj_jr_fs()->is_plan__premium_only( 'proplus', true ) ) {

class GoFetch_JR_Premium_ProPlus_Features {

	/**
	 * @var The single instance of the class.
	 */
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	function __construct() {
        require_once 'class-gofetch-admin-meta-boxes__premium_only.php';
		require_once 'class-gofetch-scheduler__premium_only.php';

		$this->init_hooks();
	}

	/**
	 * Pro+ hooks.
	 */
	function init_hooks() {
		add_filter( 'goft_jobs_settings', array( $this, 'proplus_settings' ), 11, 2 );
		add_filter( 'goft_jobs_providers', array( $this, 'providers' ) );

		add_action( 'goft_jobs_scheduler', array( 'GoFetch_JR_Scheduler', 'scheduler_manager' ) );
	}

	/**
	 * Output specific PRO+ settings by calling the related setting callback.
	 */
	public function proplus_settings( $fields, $type = '' ) {

		if ( $type && method_exists( $this, 'output_settings_' . $type ) ) {
			$callback = 'output_settings_' . $type;
			return call_user_func( array( $this, $callback ), $fields );
		}
		return $fields;
	}

	/**
	 * Outputs the date interval settings.
	 */
	protected function output_settings_filter( $fields ) {

		$last_field[] = array_pop( $fields );
		$last_field[] = array_pop( $fields );

		$new_fields = array(
			array(
				'title'  => __( 'Date Span', 'gofetch-jobs' ),
				'name'   => '_blank',
				'type'   => 'custom',
				'render' => array( $this, 'output_date_span' ),
				'tip'    => __( 'Choose a date interval to filter the job feed results. The date internal is only applied to the jobs your are currently importing. It is not saved in templates.', 'gofetch-jobs' ),
				'tr'     => 'temp-tr-hide tr-date-span tr-advanced',
			),
		);
		return array_merge( $fields, $new_fields, $last_field );
	}

	/**
	 * Outputs the date interval settings.
	 */
	public function output_date_span( $output ) {

		$atts = array(
			'type'        => 'text',
			'id'          => 'from_date',
			'name'        => 'from_date',
			'class'       => 'span_date field_dependent date-intervals',
			'style'       => 'width: 120px;',
			'placeholder' => __( 'click to choose...', 'gofetch-jobs' ),
			'readonly'    => true,
		);

		if ( ! empty( $_POST['from_date'] ) ) {
			$atts['value'] = wp_strip_all_tags( $_POST['from_date'] );
		}

		$output = __( 'From:', 'gofetch-jobs' ) . ' ' . html( 'input', $atts );

		unset( $atts['value'] );
		$atts['id'] = 'to_date';
		$atts['name'] = 'to_date';

		$atts['placeholder'] = __( 'click to choose...', 'gofetch-jobs' );

		if ( ! empty( $_POST['to_date'] ) ) {
			$atts['value'] = wp_strip_all_tags( $_POST['to_date'] );
		}

		$output .= ' ' . __( 'To:', 'gofetch-jobs' ) . ' ' . html( 'input', $atts );

		$output .= html( 'a', array( 'class' => 'button clear_span_dates', 'data-goft_parent' => 'date-intervals' ), __( 'Clear', 'gofetch-jobs' ) );

		return $output;
	}

	/**
	 * Retrieves a list of providers and their details.
	 */
	public static function providers( $providers ) {

		$new_providers = array(
			'indeed.com' => array(
				'website'     => 'http://www.indeed.com/',
				'logo'        => 'https://upload.wikimedia.org/wikipedia/en/b/b1/Indeedlogo.png',
				'description' => 'One search. All jobs',
				'feed'        => array(
					'base_url'   => 'http://www.indeed.com/rss',
					'search_url' => 'http://www.indeed.com/advanced_search',
					'example'    => 'http://www.indeed.com/rss?q=design&l=',
					'sample'     => 'http://www.indeed.com/rss?q=design&l=&radius=25&[etc...]',
					// Mappings for known/custom tags used in feed.
					'tag_mappings' => array(
						'geolocation' => 'point',
						'company'     => 'source',
					),
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'title' => array(
							'company'  => '/.*\\s-\\s(.*?)\\s-\\s.*/is', // e.g: Developer - Google - San Francisco
						),
					),
					// Feed URL query args. Key value pairs of valid keys => provider_key/default_key_value.
					'query_args'  => array(
						'keyword'  => array( 'q' => '' ),
						'location' => array( 'l' => '' ),
					),
					'default' => true,
				),
				'multi_region_match' => 'indeed',
				'multi-region' => sprintf( __( 'To see all available countries visit <a href="%s">Indeed\'s countries directory</a>.', 'gofetch-jobs' ), 'http://www.indeed.com/worldwide' ) .
								 '<br/><br/>' . sprintf( __( 'To apply these instructions to other country you can usually replace replace this URL part <code>%1$s</code> with the respective country URL you\'re interested with.', 'gofetch-jobs' ), 'http://www.indeed.com/rss/' ) .
							     '<br/><br/>' . sprintf( __( '<strong>e.g:</strong> For <em>Portugal</em> you would use <code>%1$s</code>[...]', 'gofetch-jobs' ), 'https://www.indeed.pt/rss/' ) .
 								 '<br><br/>'  . __( '<em>Note:</em> If replacing the domain part does not work for a specific country please refer to the provider site to check the exact domain used for their RSS feeds.', 'gofetch-jobs' ),
				'category' => __( 'Generic', 'gofetch-jobs' ),
				'weight'   => 10,
			),
			'careerjet.com' => array(
				'website'     => 'http://www.careerjet.com/',
				'logo'        => 'http://static.careerjet.net/images/logo_ws_home_us.png',
				'description' => 'Careerjet is an employment search engine.',
				'feed'        => array(
					'base_url'   => 'http://rss.careerjet.com/rss',
					'search_url' => 'http://www.careerjet.com/search/advanced.html',
					'example'    => 'http://rss.careerjet.com/rss?s=design&l=',
					'sample'     => 'http://rss.careerjet.com/rss?s=design&l=&[etc...]',
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'company'  => '/(.*?)-.*?-\s/is',                                   // e.g: Google - San Francisco -
						'location' => '/(?(?!.*?\s-\s.*?\s-\s.*?)(.*?)-|.*?-(.*?)-\s)/is',  // e.g: Google - San Francisco - OR San Francisco -
					),
					// Feed URL query args. Key value pairs of valid keys => provider_key/default_key_value.
					'query_args'  => array(
						'keyword'  => array( 's' => '' ),
						'location' => array( 'l' => '' ),
					),
					'default'    => true,
				),
				'multi_region_match' => 'careerjet',
				'multi-region' => '' .
								 sprintf( __( 'To apply these instructions to other country you can usually replace replace the domain part <code>%1$s</code> with the country domain name you\'re interested with.', 'gofetch-jobs' ), 'http://rss.careerjet.com/' ) .
							     '<br/><br/>' . sprintf( __( '<strong>e.g:</strong> For <em>Portugal</em> you would use <code>%1$s</code>[...]', 'gofetch-jobs' ), 'http://rss.careerjet.pt/' ) .
 								 '<br><br/>'  . __( '<em>Note:</em> If replacing the domain part does not work for a specific country please refer to the provider site to check the exact domain used for their RSS feeds.', 'gofetch-jobs' ),
				'category'     => __( 'Generic', 'gofetch-jobs' ),
				'weight'       => 9,
			),
			'uk.dice.com' => array(
				'website'     => 'http://uk.dice.com/',
				'logo'        => 'https://assets.dice.com/assets/customer/img/site/dice-logo@2x.png',
				'description' => 'IT Jobs: UK Contract and Permanent IT Jobs',
				'feed'        => array(
					'base_url'   => 'http://uk.dice.com/rss/all-jobs/all-locations/en/jobs-feed.xml',
					'search_url' => 'http://uk.dice.com/',
					'example'    => 'http://uk.dice.com/rss/php/all-locations/en/jobs-feed.xml?Currency=GBP&RadiusUnit=2&xc=247',
					'sample'     => 'http://uk.dice.com/rss/php/all-locations/en/jobs-feed.xml?Currency=GBP&[etc...]',
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'company'  => '/Advertiser.*?:(.*?)<.*?>/is', // e.g: Advertiser : Google <br/>
						'location' => '/Location.*?:(.*?)<.*?>/is',   // e.g: Location : San Francisco <br/>
						//'salary'   => '/Salary.*?:(.*?)$/is',       // e.g: Salary : £40,000 - £55,000 per year
					),
					// Feed URL query args. Key value pairs of valid keys => provider_key/default_key_value.
					'query_args'  => array(
						'keyword'  => array( 'SearchTerms' => '' ),
						'location' => array( 'LocationSearchTerms' => '' ),
					),
					'default' => true,
				),
				'multi_region_match' => 'dice',
				'multi-region' => sprintf( __( 'Other countries: %s.', 'gofetch-jobs' ), '<a href="http://be.dice.com/" target="_blank">Belgium</a>, <a href="http://de.dice.com/" target="_blank">Germany</a>, <a href="http://nl.dice.com/" target="_blank">Netherlands</a>' ) .
								 '<br/><br/>' . sprintf( __( 'To apply these instructions to other country you can usually replace replace this URL part <code>%1$s</code> with the respective country URL you\'re interested with.', 'gofetch-jobs' ), 'http://uk.dice.com/rss/' ) .
							     '<br/><br/>' . sprintf( __( '<strong>e.g:</strong> For <em>Belgium</em> you would use <code>%1$s</code>[...]', 'gofetch-jobs' ), 'http://be.dice.com/rss/' ) .
 								 '<br><br/>'  . __( '<em>Note:</em> If replacing the domain part does not work for a specific country please refer to the provider site to check the exact domain used for their RSS feeds.', 'gofetch-jobs' ),
				'category'     => __( 'IT', 'gofetch-jobs' ),
				'weight'       => 7,
			),
			'rss.jobsearch.monster.com' => array(
				'website'     => 'http://www.monster.com/jobs',
				'logo'        => 'https://securemedia.newjobs.com/global/img/header-m.png',
				'description' => 'Jobs in US, Canada, Europe',
				'feed' => array(
					'base_url'   => 'http://rss.jobsearch.monster.com/rssquery.ashx',
					'search_url' => 'http://rss.jobsearch.monster.com',
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'location' => '/(.*?),/is',  // e.g: NY-New York, blah blah
					),
					// Feed URL query args. Key value pairs of valid keys => provider_key/default_key_value.
					'query_args'  => array(
						'keyword'  => array( 'q' => '' ),
						'location' => array(
							'cy' => array(
								'placeholder'   => 'e.g: us, ca, uk, fr or nl',
								'default_value' => 'us',
							),
						),
					),
					// Fixed RSS feeds examples.
					'examples' => array(
						__( 'Latest Jobs ', 'gofetch-jobs' )               => 'http://rss.jobsearch.monster.com/rssquery.ashx',
						__( 'Latest Design Jobs', 'gofetch-jobs' )         => 'http://rss.jobsearch.monster.com/rssquery.ashx?brd=1&q=design',
						__( 'Latest Teachers Jobs in US', 'gofetch-jobs' ) => 'http://rss.jobsearch.monster.com/rssquery.ashx?brd=1&q=teacher&cy=us'
					),
				),
				'multi-region' => sprintf( __( 'Other countries: %s', 'gofetch-jobs' ), '<a href="http://www.monster.ca/jobs" target="_blank">Canada</a>, <a href="http://www.monster.co.uk/jobs" target="_blank">UK</a>, <a href="http://www.monster.fr/emploi" target="_blank">France</a>, <a href="http://jobs.monster.de" target="_blank">Germany</a>, <a href="http://www.monsterboard.nl/vacatures" target="_blank">Netherlands</a>.') .
								 '<br/><br/>' . __( 'To apply these instructions to other countries specify the country code you\'re interested in the location field.', 'gofetch-jobs' ) .
							     '<br/><br/>' . sprintf( __( '<strong>e.g:</strong> For jobs in <em>UK</em> fill in the location with the country code <code>%1$s</code>', 'gofetch-jobs' ), 'uk' ),
				'weight'       => 9,
				'category'     => __( 'Generic', 'gofetch-jobs' ),
			),
			'monster.com.hk' => array(
				'website'     => 'http://www.monster.com.hk/',
				'logo'        => 'https://upload.wikimedia.org/wikipedia/commons/a/ae/Monster_new_logo_july_2014.png',
				'description' => 'Asia - IT Jobs, Sales Jobs',
				'feed' => array(
					'base_url'   => 'http://jobsearch.monster.com.hk/rss_jobs.html',
					'search_url' => 'http://jobsearch.monster.com.hk/rss_index.html',
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'company'  => '/Company.*?<.*?>(.*?)<.*?>/is',  // e.g: <b>Company: </b><br/> Google <br/>
						'location' => '/Location.*?<.*?>(.*?)<.*?>/is', // e.g: <b>Location: </b><br/> San Francisco <br/>
					),
					// Fixed RSS feeds examples.
					'examples' => array(
						__( 'Latest Jobs ', 'gofetch-jobs' )            => 'http://jobsearch.monster.com.hk/rss_jobs.html',
						__( 'Latest IT Jobs', 'gofetch-jobs' )          => 'http://jobsearch.monster.com.hk/rss_jobs.html?cat=22',
						__( 'Latest Health Care Jobs', 'gofetch-jobs' ) => 'http://jobsearch.monster.com.hk/rss_jobs.html?cat=9',
					),
				),
				'multi_region_match' => 'monster.hk',
				'multi-region' => sprintf( __( 'Other countries: %s', 'gofetch-jobs' ), '<a href="http://www.monster.com.hk/destination_china.html" target="_blank">China</a>, <a href="http://www.monster.com.sg/" target="_blank">Singapore</a>, <a href="http://www.monster.com.ph/" target="_blank">Philipines</a>, <a href="http://www.monster.co.th/" target="_blank">Thailand</a>, <a href="http://www.monster.com.vn/" target="_blank">Vietnam</a>, ' .
				 				 '<a href="http://www.monster.co.id/" target="_blank">Indonesia</a>, <a href="http://www.monster.com.my/" target="_blank">Malaysia</a>, <a href="http://www.monsterindia.com/" target="_blank">India</a>, <a href="http://www.monstergulf.com/" target="_blank">Gulf</a>.') .
								 '<br/><br/>' . sprintf( __( 'To apply these instructions to other country you can usually replace replace the domain part <code>%1$s</code> with the country domain name you\'re interested with.', 'gofetch-jobs' ), 'http://jobsearch.monster.com.hk' ) .
							     '<br/><br/>' . sprintf( __( '<strong>e.g:</strong> For jobs in <em>Singapure</em> you would use <code>%1$s</code>[...]', 'gofetch-jobs' ), 'http://jobsearch.monster.com.sg/' ) .
 								 '<br><br/>'  . __( '<em>Note:</em> If replacing the domain part does not work for a specific country please refer to the provider site to check the exact domain used for their RSS feeds.', 'gofetch-jobs' ),
				'weight'       => 8,
				'category'     => __( 'Generic', 'gofetch-jobs' ),
			),
			'craigslist.org' => array(
				'website'     => 'https://www.craigslist.org/about/sites',
				'logo'        => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c6/Craigslist.svg/200px-Craigslist.svg.png',
				'description' => 'Craigslist is a classified advertisements website with sections devoted to jobs',
				'feed' => array(
					'base_url'   => 'http://london.craigslist.co.uk/search/jjj?format=rss',
					'search_url' => 'http://london.craigslist.co.uk/search/jjj',
					'example'    => 'http://london.craigslist.co.uk/search/jjj?is_paid=all&query=driver',
					'sample'     => 'http://london.craigslist.co.uk/search/jjj?is_paid=all&query=driver&[etc...]',
					'meta'       => array(
						'logo',
					),
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 'query' => '' ),
						'location' => array( 'domain_l' => array( 'regex' => '\/\/(.*?)(?=\.)' ) ),
					),
					'default' => true,
				),
				'multi_region_match' => 'craigslist',
				'multi-region' => sprintf( __( 'Choose any of the countries from the <a href="%s" target="_blank">regions directory page</a>.', 'gofetch-jobs' ), 'https://www.craigslist.org/about/sites' ) .
								 '<br/><br/>' . sprintf( __( 'To apply these instructions to other country you can usually replace replace the domain part <code>%1$s</code> with the country domain name you\'re interested with.', 'gofetch-jobs' ), 'http://london.craigslist.co.uk' ) .
							     '<br/><br/>' . sprintf( __( '<strong>e.g:</strong> For <em>US</em> jobs in <em>New York</em> you would use <code>%1$s</code>[...]', 'gofetch-jobs' ), 'https://newyork.craigslist.org' ) .
 								 '<br><br/>'  . __( '<em>Note:</em> If replacing the domain part does not work for a specific country please refer to the provider site to check the exact domain used for their RSS feeds.', 'gofetch-jobs' ),

				'weight'      => 8,
				'category'    => __( 'Generic', 'gofetch-jobs' ),
			),
			'reed.co.uk' => array(
				'website'     => 'http://www.reed.co.uk/',
				'logo'        => 'http://www.reed.co.uk/resources/images/header-logo-signed-out-homepage.png',
				'description' => 'Jobs and Recruitment on reed.co.uk, the UK\'s #1 job site',
				'feed' => array(
					'base_url'   => 'http://www.reed.co.uk/jobs/rss',
					'search_url' => 'http://www.reed.co.uk/jobs',
					'example'    => 'http://www.reed.co.uk/jobs/rss?keywords=arts',
					'sample'    => 'http://www.reed.co.uk/jobs/rss?keywords=arts&location=london&[etc...]',
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'location' => '/Location.*?:(.*?)<.*?>/is', // e.g: Location: San Francisco <br/>
					),
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 'keywords' => '' ),
						'location' => array( 'location' => '' ),
					),
					'default' => true,
				),
				'category' => __( 'Generic', 'gofetch-jobs' ),
				'weight'   => 8,
			),

		);
		return array_merge( $providers, $new_providers );
	}

}

function GoFetch_JR_Premium_ProPlus_Features() {
	return GoFetch_JR_Premium_ProPlus_Features::instance();
}

GoFetch_JR_Premium_ProPlus_Features();

}
