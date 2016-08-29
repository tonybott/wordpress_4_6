<?php
/**
 * Loads advanced premium features not available on the free version.
 */

class GoFetch_JR_Premium_Features {

	function __construct() {
		$this->init_premium();
	}

	/**
	 * Initialized the scheduler.
	 */
	private function init_premium() {

		if ( gofj_jr_fs()->is_plan__premium_only( 'pro' ) ) {
			require_once 'pro/class-gofetch-features__premium_only.php';
		}

	    if ( gofj_jr_fs()->is_plan__premium_only( 'proplus', true ) ) {
	    	require_once 'pro+/class-gofetch-features__premium_only.php';
		}

		add_action( 'admin_menu', array( $this, 'plugin_browser' ), 15 );
	}

    /**
     * Setup the plugin browser.
     */
    public function plugin_browser() {

        $args = array(
            'page_title' => __( 'Browse Plugins', 'gofetch-jobs' ),
            'remote_info' => 'http://bruno-carreco.com/wpuno/fetch-products.php?username=sebet&type=products-info',
            'tabs'        => array(
                'all'  =>  array(
                    'name' => __( 'All', 'gofetch-jobs' ),
                    'url' => "http://bruno-carreco.com/wpuno/fetch-products.php?username=sebet&type=products-new",
                ),
            ),
            'wp_hosted_args' => array(
                'author' => 'SebeT',
            ),
            'menu' => array(
                'menu_title' => __( 'Other Plugins', 'gofetch-jobs' ),
                'parent'     => 'go-fetch-jobs'
            )
        );

        wp_product_showcase_register( 'go-fetch-jobs-plugins-browser', $args );
    }

}

new GoFetch_JR_Premium_Features;
