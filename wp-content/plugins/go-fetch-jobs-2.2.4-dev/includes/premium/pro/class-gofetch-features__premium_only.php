<?php
/**
 * Loads advanced premium features not available on the free version.
 */

if ( gofj_jr_fs()->is_plan__premium_only( 'pro' ) ) {

class GoFetch_JR_Premium_Pro_Features {

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
		$this->init_hooks();
	}

	public function init_hooks() {
		add_filter( 'goft_jobs_settings', array( $this, 'pro_settings' ), 10, 2 );
		add_filter( 'goft_jobs_settings_taxonomies', array( $this, 'output_tax_settings' ) );
		add_filter( 'goft_jobs_providers', array( $this, 'providers' ) );
	}

	/**
	 * Output specific PRO settings by calling the related setting callback.
	 */
	public function pro_settings( $fields, $type = '' ) {

		if ( $type && method_exists( $this, 'output_settings_' . $type ) ) {
			$callback = 'output_settings_' . $type;
			return call_user_func( array( $this, $callback ), $fields );
		}
		return $fields;
	}

	/**
	 * Outputs the affiliate parameter.
	 */
	protected function output_settings_monetize( $fields ) {

		$fields[] = array(
			'title'  => __( 'URL Parameters', 'gofetch-jobs' ),
			'type'  => 'text',
			'name'  => 'source[args]',
			'extra' => array(
				'class'       => 'regular-text goft_jobs-monetize',
				'placeholder' => 'e.g: affID=123, publisher=123',
				'section'     => 'monetize',
			),
			'tip'   => __( "If you have a publisher/partner/affiliate ID or any other query URL argument you wish to add to external links add them to this field using the format <code>key=value [,key=value]</code>.", 'gofetch-jobs' ) .
					   '<br/><br/>' . __( "The publisher/partner/affiliate ID varies from site to site so make sure you check the site source help pages to find the correct parameter <code>(e.g: publisher=123, pshid=123, pid=123)</code>.", 'gofetch-jobs' ) .
					   '<br/><br/>' . __( "These arguments will be added to each external link.", 'gofetch-jobs' ),
			'value' => ( ! empty( $_POST['source[args]'] ) ? $_POST['source[args]'] : '' ),
			'tr'    => 'temp-tr-hide tr-monetize tr-advanced',
		);

		return $fields;
	}

	/**
	 * Outputs the additional taxonomies parameters.
	 */
	public function output_tax_settings( $fields ) {

		$fields[] = array(
			'title'    => __( 'Smart Assign?', 'gofetch-jobs' ),
			'type'     => 'select',
			'name'     => 'smart_tax_input',
			'desc'     => __( 'Enable this option to allow the import process to choose the best term(s) for each job.', 'gofetch-jobs' ),
			'tip'      => __( 'This option is very useful on RSS feeds that can contain jobs for multiple job types/categories. When enabled, the import process will analyze the content and will try to assign the best term(s) for each job.', 'gofetch-jobs' ) .
						  '<br/><br/>' . __( 'This is done by matching your existing job types/job categories terms with the content/title for each job being imported.', 'gofetch-jobs' ) .
						 ' ' . __( 'If no valid matches are found, jobs will default to the terms you\'ve specified previously.', 'gofetch-jobs' ) .
						 '<br/><br/>' . __( '<strong>Options:</strong>', 'gofetch-jobs' ) .
						 '<br/><br/>' . __( '<code>No</code> Do not use term matching. Blindly assign the previously specified terms to each imported job.', 'gofetch-jobs' ) .
						 '<br/><br/>' . __( '<code>Multiple Terms Match</code> Allow assigning multiple terms if there are multiple matches.', 'gofetch-jobs' ) .
						 '<br/><br/>' . __( 'As an example, consider you have the following job categories created: <em>Writer</em> and <em>Designer</em>. If the job being imported contains those terms, both terms will be assigned to the job.', 'gofetch-jobs' ) .
						 '<br/><br/>' . __( '<code>Single Terms Match</code> Assign only the first matched term.', 'gofetch-jobs' ) .
						 '<br/><br/>' . __( 'As an example, consider you have the following job categories created: <em>Writer</em> and <em>Designer</em>. If the job being imported contains those terms, only the first term match will be assigned to the job.', 'gofetch-jobs' ),
			'selected' => ( ! empty( $_POST['smart_tax_input'] ) ? $_POST['smart_tax_input'] : '' ),
			'choices'  => array(
				'multiple' => __( 'Yes (Multiple Terms Match)', 'gofetch-jobs' ),
				'single'   => __( 'Yes (Single Term Match)', 'gofetch-jobs' ),
				''         => __( 'No', 'gofetch-jobs' ),
			),
			'tr'      => 'temp-tr-hide tr-smart-assign',
		);

		return $fields;
	}

	/**
	 * Outputs the date interval settings.
	 */
	protected function output_settings_filter( $fields ) {

		$new_fields = array(
			array(
				'title'  => __( 'Keywords', 'gofetch-jobs' ),
				'type'  => 'text',
				'name'  => 'keywords',
				'extra' => array(
					'class'       => 'large-text',
					'placeholder' => 'e.g: design, sales, marketing',
				),
				'value' => ( ! empty( $_POST['keywords'] ) ? $_POST['keywords'] : '' ),
				'tip'   => __( 'Keywords can be very useful when importing from generic RSS feeds since the import process will only consider jobs that contain the keywords you specify here. ', 'gofetch-jobs' ),
				'desc'  => __( 'Comma separated list of keywords.', 'gofetch-jobs' ),
				'tr'    => 'temp-tr-hide tr-keywords tr-advanced',
			),
		);
		return array_merge( $fields, $new_fields );
	}

	/**
	 * Retrieves a list of providers and their details.
	 */
	public static function providers( $providers ) {

		$new_providers = array(
			'idealist.org' => array(
				'website'     => 'http://www.idealist.org/',
				'logo'        => 'http://www.idealist.org/images/logos/idealist.gif',
				'description' => 'Volunteer, work, intern, organize, hire and connect. Change the world',
				'feed' => array(
					'base_url'   => 'http://www.idealist.org/search/v2/feeds',
					'search_url' => 'http://www.idealist.org/search/v2/?qs=',
					'sample'     => array( 'http://www.idealist.org/search/v2/feeds?qs=QlpoOTFBW&[etc...]', 'http://www.idealist.org/search/v2/feeds?search_user_query=design&[etc..]' ),
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'company'  => '/Posted by.*?<.*?>(.*?)<.*?>/is',                // e.g: <strong>Posted by: </strong> Google
						'location' => '/Posted by.*?<.*?>.*?<.*?><.*?>(.*?)<.*?>/is',   // e.g: <strong>Posted by: </strong> Google</p><p> San Francisco</p>
					),
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 'search_user_query' => '' ),
						'location' => array( 'search_location_name' => '' ),
					),
					'default'    => true,
				),
				'category' => __( 'Generic', 'gofetch-jobs' ),
				'weight'   => 5,
			),
			'technojobs.co.uk' => array(
				'website'     => 'https://www.technojobs.co.uk/',
				'logo'        => 'https://www.technojobs.co.uk/images/logo.png',
				'description' => 'IT Jobs Board - Specialist Technology and IT Jobs',
				'feed'        => array(
					'base_url'   => 'https://www.technojobs.co.uk/rss.php',
					'search_url' => 'https://www.technojobs.co.uk/search.phtml/searchfield/location/radius/salary',
					'example'    => 'https://www.technojobs.co.uk/rss.php/wordpress/excludekeywords/location/radius25/termsin0/salary0/postedwithinall/jobtypepermanent/searchfieldRSearchIndex/page1',
					'sample'     => 'https://www.technojobs.co.uk/rss.php/any/excludekeywords/location/[etc...]',
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						/*'salary'   => '/Salary\/Rate.*?:(.*?)<.*?>/is', // e.g: <p>Salary/Rate: 50.000 - 80.000</p>*/
						'location' => '/Location.*?:(.*?)<.*?>/is',     // e.g: <p>Location: London</p>
					),
					// Feed URL query args. Key value pairs of valid keys => provider_key/default_key_value.
					'query_args'  => array(
						'keyword'  => array(
									'' => array(
										'default_value' => 'any',
										'is_prefix'     => 1, // means the parameter is prefixed instead of delimited with '/' or '&' (in this case with ''). e.g: ../rss/developer/locationlondon/
									),
						),
						'location' => array(
									'location' => array(
										'default_value' => 'location',
										'is_prefix'     => 1, // means the parameter is prefixed instead of delimited with '/' or '&' (in this case with 'location'). e.g: ../rss/developer/locationlondon/
									),
						),
					),
					'query_args_sep' => '/',
					'default'        => true,
				),
				'weight'      => 5,
				'category'    => __( 'IT', 'gofetch-jobs' ),
			),
			'careerbuilder.com' => array(
				'website'     => 'http://www.careerbuilder.com/',
				'logo'        => 'http://img.icbdr.com/images/UniversalHeaderFooter/logo.png',
				'description' => 'Search Jobs, Employment and Careers at CareerBuilder',
				'feed'        => array(
					'base_url'   => 'http://www.careerbuilder.com/RTQ/rss20.aspx?rssid=RSS_PD&geoip=false',
					'search_url' => 'http://www.careerbuilder.com/RSS/rss.aspx',
					'example'    => 'http://www.careerbuilder.com/RTQ/rss20.aspx?kw=wordpress&rssid=RSS_PD&num=10&geoip=false',
					'sample'     => 'http://www.careerbuilder.com/RTQ/rss20.aspx?kw=wordpress&rssid=RSS_PD&geoip=false&[etc...]',
					// Feed URL query args. Key value pairs of valid keys => provider_key/default_key_value.
					'query_args'  => array(
						'keyword'  => array( 'kw' => '' ),
						'location' => array( 'city' => '' ),
						'state'    => array( 'state' => array( 'required' => __( '<small>* (required if location is specified)</small>', 'gofetch-jobs' ) ) ),
					),
					// Fixed RSS feeds examples.
					'examples' => array(
						__( 'Latest Jobs ', 'gofetch-jobs' )           => 'http://www.careerbuilder.com/RTQ/rss20.aspx?rssid=RSS_PD&geoip=false',
						__( 'Latest IT Jobs ', 'gofetch-jobs' )        => 'http://www.careerbuilder.com/RTQ/rss20.aspx?rssid=RSS_PD&num=25&geoip=false&ddcompany=false&ddtitle=false&cat=JN008',
						__( 'Latest Jobs in Atlanta', 'gofetch-jobs' ) => 'http://www.careerbuilder.com/RTQ/rss20.aspx?RSSID=RSS_PD&city=atlanta&state=GA&country=US&geoip=false',
					),
				),
				'category' => __( 'Generic', 'gofetch-jobs' ),
				'weight'   => 8,
			),
			'dice.com' => array(
				'website'     => 'http://www.dice.com/',
				'logo'        => 'https://assets.dice.com/assets/customer/img/site/dice-logo@2x.png',
				'description' => 'Job Search for Technology Professionals',
				'feed'        => array(
					'base_url' => 'http://rss.dice.com/system/',
					// Fixed RSS feeds examples.
					'examples' => array(
						__( 'Latest Unix Jobs ', 'gofetch-jobs' ) => 'http://rss.dice.com//system/unix-jobs.xml',
						__( 'Latest Jobs in NY', 'gofetch-jobs' ) => 'http://rss.dice.com//system/new-york-jobs.xml',
					),
				),
				'category' => __( 'IT', 'gofetch-jobs' ),
				'weight'   => 7,
			),
			'sap.dice.com' => array(
				'website'     => 'http://sap.dice.com/',
				'logo'        => 'https://assets.dice.com/assets/customer/img/site/dice-logo@2x.png',
				'description' => 'Find Contract and Permanent SAP Jobs',
				'feed'        => array(
					'base_url'   => 'http://sap.dice.com/rss/all-jobs/all-locations/en/jobs-feed.xml',
					'search_url' => 'http://sap.dice.com/',
					'example'    => 'http://sap.dice.com/rss/php/all-locations/en/jobs-feed.xml?Currency=GBP&RadiusUnit=2&xc=247',
					'sample'     => 'http://sap.dice.com/rss/php/all-locations/en/jobs-feed.xml?Currency=GBP&[etc...]',
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'company'  => '/Advertiser.*?:(.*?)<.*?>/is', // e.g: Advertiser : Google <br/>
						'location' => '/Location.*?:(.*?)<.*?>/is',   // e.g: Location : San Francisco <br/>
						//'salary'   => '/Salary.*?:(.*?)$/is',         // e.g: Salary : £40,000 - £55,000 per year
					),
					// Feed URL query args. Key value pairs of valid keys => provider_key/default_key_value.
					'query_args'  => array(
						'keyword'  => array( 'SearchTerms' => '' ),
						'location' => array( 'LocationSearchTerms' => '' ),
					),
					'default'    => true,

				),
				'category' => __( 'IT', 'gofetch-jobs' ),
				'weight'   => 7,
			),
			'jobs.smashingmagazine.com' => array(
				'website'     => 'http://jobs.smashingmagazine.com/',
				'logo'        => 'https://media-mediatemple.netdna-ssl.com/wp-content/themes/smashing-magazine/assets/images/logo.png',
				'description' => 'For Professional Web Designers and Developers',
				'feed'        => array(
					'base_url'   => 'http://jobs.smashingmagazine.com/rss/all/all/',
					'search_url' => 'http://jobs.smashingmagazine.com/',
					'example'    => 'http://jobs.smashingmagazine.com/rss/all/all?search=design%2C+new+york',
					'sample'     => 'http://jobs.smashingmagazine.com/rss/all/all?search=design%2C+new+york&[etc...]',
					// Meta provided by feed.
					'meta'       => array(
						'location'
					),
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array(
							'search' => array(
								'placeholder'   => 'e.g: web design, new york',
								'default_value' => '',
							),
						)
					),
					'default'    => true,
				),
				'category' => __( 'Design', 'gofetch-jobs' ),
				'weight'   => 9,
			),
			'dribbble.com' => array(
				'website'     => 'https://dribbble.com/jobs',
				'logo'        => 'https://d13yacurqjgara.cloudfront.net/assets/dribbble-ball-dnld-132f5d763011b493e86728489c110825.png',
				'description' => 'Show and tell for designers',
				'feed'        => array(
					'base_url'   => 'https://dribbble.com/jobs.rss/',
					'search_url' => 'https://dribbble.com/jobs',
					'example'    => 'https://dribbble.com/jobs.rss?location=los%20angeles',
					'sample'     => 'https://dribbble.com/jobs.rss?location=los%20angeles&[etc...]',
					// Mappings for known/custom tags used in feed.
					'tag_mappings' => array(
						'company' => 'creator',
					),
					// Feed URL query args.
					'query_args'  => array(
						'location' => array( 'location' => '' ),
					),
					// Fixed RSS feeds.
					'fixed' => array(
						__( 'Latest Jobs', 'gofetch-jobs' ) => 'http://dribbble.com/jobs.rss',
						__( 'Team Jobs', 'gofetch-jobs' )   => 'https://dribbble.com/jobs.rss?teams_only=true',
						__( 'Remote Jobs', 'gofetch-jobs' ) => 'https://dribbble.com/jobs.rss?location=Anywhere',
					),
				),
				'category' => __( 'Design', 'gofetch-jobs' ),
				'weight'   => 9,
			),
			'totaljobs.com' => array(
				'website'     => 'http://www.totaljobs.com/',
				'logo'        => 'http://www.totaljobs.com/Content/images/165x32_tj_white_logo.gif',
				'description' => 'UK Job search - find your perfect job',
				'feed'        => array(
					'base_url'   => 'http://www.totaljobs.com/JobSearch/RSS.aspx/',
					'search_url' => 'http://www.totaljobs.com/JobSearch/Results.aspx',
					'example'    => 'http://www.totaljobs.com/JobSearch/RSS.aspx?s=header&sp=%2Fhelp-and-support&Keywords=design&Radius=10',
					'sample'     => 'http://www.totaljobs.com/JobSearch/RSS.aspx?Keywords=design&[etc...]',
					'meta'       => array(
						'logo',
					),
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 'Keywords' => '' ),
						'location' => array( 'LTxt' => '' ),
					),
					'default' => true,
				),
				'category' => __( 'Generic', 'gofetch-jobs' ),
				'weight'   => 7,
			),
			'krop.com' => array(
				'website'     => 'http://www.krop.com/',
				'logo'        => 'http://assets.krop.com.s3.amazonaws.com/logos/krop_logo_gray.png',
				'description' => 'Find Creative, Design & Tech Jobs',
				'feed'        => array(
					'base_url' => 'http://www.krop.com/services/feeds/rss/latest/',
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'location' => '/Location.*?:(.*?)Status/is',  // e.g: Location: San Francisco Status: etc...
					),
					'default' => true,
				),
				'category' => __( 'Design', 'gofetch-jobs' ),
				'single'   => true,
				'weight'   => 7,
			),
			'authenticjobs.com' => array(
				'website'     => 'https://authenticjobs.com/',
				'logo'        => 'https://authenticjobs.com/email/june2011/logo.png',
				'description' => 'Full-time and freelance job opportunities for web, design, and creative professionals',
				'feed'        => array(
					'base_url'   => 'https://authenticjobs.com/rss/custom.php',
					'search_url' => 'https://authenticjobs.com/',
					'example'    => 'https://authenticjobs.com/rss/custom.php',
					'sample'     => 'http://www.authenticjobs.com/rss/custom.php?terms=wordpress&cats=&onlyremote=&location=new+york&[etc...]',
					'meta'       => array(
						'logo',
						'location'
					),
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'location' => '/^<.*?>\((.*?)\)<.*?>/is', // e.g: <strong>(NYC)</strong>
					),
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 'terms' => '' ),
						'location' => array( 'location' => '' ),
					),
					'fixed' => array(
						__( 'All Jobs', 'gofetch-jobs' )       => 'http://www.authenticjobs.com/rss/index.xml',
						__( 'Freelance Jobs', 'gofetch-jobs' ) => 'http://www.authenticjobs.com/rss/offsite.xml',
					),
					'default' => true,
				),
				'category' => __( 'Design', 'gofetch-jobs' ),
				'weight'   => 7,
			),
			'coroflot.com' => array(
				'website'     => 'http://www.coroflot.com/jobs',
				'logo'        => 'http://www.coroflot.com/images/logo_big.png',
				'description' => 'Design Jobs & Portfolios',
				'feed'        => array(
					'base_url' => 'http://www.coroflot.com/jobs/rss',
					// Meta provided by feed.
					'meta'     => array(
						'logo',
						'location'
					),
					// Mappings for known/custom tags used in feed.
					'tag_mappings' => array(
						'location' => 'description',
					),
					'default' => true,
				),
				'single'   => true,
				'category' => __( 'Design', 'gofetch-jobs' ),
				'weight'   => 7,
			),
			'jobs.gamasutra.com' => array(
				'website'     => 'http://jobs.gamasutra.com/',
				'logo'        => 'http://jobs.gamasutra.com/themes/gamasutra/images/gama_logo.png',
				'description' => 'The Art & Business of Making Games',
				'feed' => array(
					'base_url'   => 'http://jobs.gamasutra.com/xml_feed/action/advanced_search/site/wj',
					'search_url' => 'http://jobs.gamasutra.com/search',
					'example'    => 'http://jobs.gamasutra.com/xml_feed/action/advanced_search/site/wj/keywords/programmer/',
					'sample'     => 'http://jobs.gamasutra.com/xml_feed/action/advanced_search/site/wj/keywords/programmer/city/+/state/+/[etc...]',
					// Feed URL query args.
					'query_args'  => array(
						'keyword'  => array( 'keywords' => '' ),
						'location' => array( 'city' => '' ),
					),
					'query_args_sep' => '/',
					'default'        => true,
				),
				'category' => __( 'Gaming', 'gofetch-jobs' ),
				'weight'   => 7,
			),
			'weworkremotely.com' => array(
				'website'     => 'https://weworkremotely.com/',
				'logo'        => 'https://weworkremotely.com/images/header.jpg',
				'description' => 'Remote Jobs: Design, Programming, Rails, Executive, Marketing, Copywriting, and more',
				'search_url'  => 'https://weworkremotely.com/jobs/search',
				'feed' => array(
					'base_url'   => 'https://weworkremotely.com/jobs.rss',
					'search_url' => 'https://weworkremotely.com',
					'meta'       => array(
						'logo',
					),
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'location'   => '/Headquarters.*?:<.*?>(.*?)<.*?>/is', // e.g: <strong>Headquarters:</strong> New York, NY <br />
					),
					'default' => true
				),
				'category' => __( 'Generic', 'gofetch-jobs' ),
				'weight'   => 7,
			),
			'jobs.ac.uk' => array(
				'website'     => 'http://www.jobs.ac.uk/',
				'logo'        => 'http://www.jobs.ac.uk/images/global/jobs_ac_uk-logo.gif',
				'description' => 'Great jobs for bright people',
				'feed'        => array(
					'base_url' => 'http://www.jobs.ac.uk/feeds/',
					// Regex mappings for known/custom tags used in the feed description.
					'regexp_mappings' => array(
						'company' => '/^<.*?>(.*?)\s-\s.*?<.*?>/is', // e.g: <strong>Google - Developer</strong> blah blah
					),
					// Fixed RSS feeds examples.
					'examples' => array(
						__( 'Latest IT Jobs ', 'gofetch-jobs' )    => 'http://www.jobs.ac.uk/jobs/it/?format=rss',
						__( 'Latest London Jobs', 'gofetch-jobs' ) => 'http://www.jobs.ac.uk/jobs/london/?format=rss',
						__( 'Latest PhD Jobs', 'gofetch-jobs' )    => 'http://www.jobs.ac.uk/jobs/phd/?format=rss',
					),
				),
				'category' => __( 'Generic', 'gofetch-jobs' ),
				'weight'   => 5,
			),
			'jobs.problogger.net' => array(
				'website'     => 'http://jobs.problogger.net/',
				'logo'        => 'http://www.problogger.net/wp-content/themes/problogger2/images/logo.gif',
				'description' => 'Jobs for Bloggers',
				'feed' => array(
					'base_url' => 'http://jobs.problogger.net/?format=xml',
					'fixed'    => array(
						__( 'Latest Jobs', 'gofetch-jobs' )        => 'http://jobs.problogger.net/?format=xml',
						__( 'Corporate/Business', 'gofetch-jobs' ) => 'http://jobs.problogger.net/?category=corporate&format=xml',
						__( 'Blog Networks', 'gofetch-jobs' )      => 'http://jobs.problogger.net/?category=blognetwork&format=xml',
						__( 'Co-blogging', 'gofetch-jobs' )        => 'http://jobs.problogger.net/?category=coblogging&format=xml',
						__( 'Podcasting', 'gofetch-jobs' )         => 'http://jobs.problogger.net/?category=podcasting&format=xml',
						__( 'Miscellaneous', 'gofetch-jobs' )      => 'http://jobs.problogger.net/?category=miscellaneous&format=xml',
					),
				),
				'weight'   => 6,
				'category' => __( 'Blogging', 'gofetch-jobs' ),
			)

		);
		return array_merge( $providers, $new_providers );
	}

	/**
	 * Scans content and assigns taxonomies by matching it agains a list of taxonomy terms.
	 */
	public static function smart_tax_terms_input( $tax_input, $content, $taxonomies, $match_type = 'multiple' ) {

		foreach( $taxonomies as $taxonomy ) {

			$terms = get_terms( $taxonomy->name, array( 'hide_empty' => 0 ) );

			$matched_terms = array();

			foreach( $terms as $term ) {

				$match_terms = array( $term->slug, $term->name );

				if ( GoFetch_JR_Helper::match_keywords( $content, $match_terms ) ) {
					$matched_terms[] = $term->slug;

					if ( 'single' === $match_type ) {
						break;
					}
				}

			}

			// Only assign when terms are found.
			// Defaults to user assigned taxonomies.
			if ( $matched_terms ) {
				$tax_input[ $taxonomy->name ] = $matched_terms;
			}

		}
		return $tax_input;
	}

}

function GoFetch_JR_Premium_Pro_Features() {
	return GoFetch_JR_Premium_Pro_Features::instance();
}

GoFetch_JR_Premium_Pro_Features();

}