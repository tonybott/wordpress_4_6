<?php

/**
 * Provides the info to use on the guided tour and help pages.
 */
class GoFetch_WPJM_Guided_Tutorial extends BC_Framework_Pointers_Tour
{
    public  $plugin_name ;
    public function __construct()
    {
        $this->plugin_name = 'Go Fetch Jobs';
        parent::__construct( 'toplevel_page_go-fetch-jobs-wp-job-manager', array(
            'version'     => '1.0',
            'prefix'      => 'gofetch-wpjm-tour',
            'text_domain' => 'gofetch-wpjm',
            'help'        => true,
        ) );
    }
    
    /**
     * The guided tour steps.
     */
    protected function pointers()
    {
        $pointers['step1'] = array(
            'title'     => html( 'h3', sprintf( __( 'Welcome to <em>%s</em>!', $this->text_domain ), $this->plugin_name ) ),
            'content'   => html( 'p', __( 'This is the main screen for controlling operations and where the magic of instant jobs happens.', $this->text_domain ) ) . html( 'p', __( 'Here you can also create automatic import templates for regular manual imports.', $this->text_domain ) ) . html( 'p', sprintf( __( 'Templates can also be used on scheduled imports to keep your jobs database healthy with fresh jobs%s.', $this->text_domain ), $this->premium_only( 'refer', 'proplus' ) ) ) . html( 'p', __( 'There are plenty of options in this screen for granular control over the jobs being imported but if you prefer to keep it simple, just toggle the <em>Basic</em> option in the <em>Screen Options</em> tab on the top right.', $this->text_domain ) ) . html( 'p class="hide-in-help-tabs"', html( 'span class="dashicons-before dashicons-info"', '&nbsp;' ) . ' ' . __( 'If you need to revisit the guided tour later or just disable it use the - <em>Screen Options</em> - tab on top of the page. If you need more help click the - <em>Help</em> - tab, also on top of the page.', $this->text_domain ) ) . html( 'p', $this->premium_only( 'link', 'proplus' ) ),
            'anchor_id' => 'h2:first',
            'edge'      => 'top',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step2'] = array(
            'title'     => html( 'h3', __( 'Load Saved Template', $this->text_domain ) ),
            'content'   => html( 'p', __( 'Templates you have previous saved will be displayed on the templates list. Saved templates contain all your import settings.', $this->text_domain ) ) . html( 'p', __( 'To load a saved template simply choose a template from the list.', $this->text_domain ) ) . html( 'p', __( 'Use the <em>Refresh</em> button after you\'ve saved a new template to update the templates list.', $this->text_domain ) ) . html( 'p', __( 'To remove a template just click <em>Delete</em>.', $this->text_domain ) ),
            'anchor_id' => '.tr-templates span.description',
            'edge'      => 'left',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step3'] = array(
            'title'     => html( 'h3', __( 'RSS Providers', $this->text_domain ) ),
            'content'   => html( 'p', __( 'The RSS Providers dropdown provides a list of known job sites that provide RSS feeds and it also offers some tips on how you can find RSS feeds on other job sites (choose <em>Other</em> from the list).', $this->text_domain ) ) . html( 'p', sprintf( __( 'You\'ll also find several pre-set RSS feeds ready to use, and a custom RSS feed builder for some providers%s.', $this->text_domain ), $this->premium_only( 'refer', 'proplus' ) ) ) . html( 'p', __( 'The list is grouped by category and provides a short description for each of the job providers.', $this->text_domain ) ) . html( 'p', __( 'Depending on your version (<em>Free</em>, <em>Premium</em>), this list will contain a different shorter/bigger set of known providers.', $this->text_domain ) ) . html( 'p', $this->premium_only( 'link' ) ),
            'anchor_id' => '.tr-providers .select2-container',
            'edge'      => 'left',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step4'] = array(
            'title'     => html( 'h3', __( 'RSS URL - Input', $this->text_domain ) ),
            'content'   => html( 'p', __( 'The RSS feed URL that contains the jobs you want to import goes here.', $this->text_domain ) ) . html( 'p', __( 'Pick one from the providers list or use one from your favorite job site, if available.', $this->text_domain ) ),
            'anchor_id' => '#rss_feed_import',
            'edge'      => 'bottom',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step4_1'] = array(
            'title'     => html( 'h3', __( 'RSS URL - Fetch Job Companies Logos', $this->text_domain ) ),
            'content'   => html( 'p', __( 'Check this option to let the feed importer search for company logos inside the RSS feed.', $this->text_domain ) ) . html( 'p', __( 'Note that with this option checked the RSS feed scan will take more time.', $this->text_domain ) ) . html( 'p', __( 'Also note that only some job providers provide logos on their feeds.', $this->text_domain ) ),
            'anchor_id' => '.rss-load-images',
            'edge'      => 'top',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step4_2'] = array(
            'title'     => html( 'h3', __( 'RSS URL - Load', $this->text_domain ) ),
            'content'   => html( 'p', __( 'After pasting a feed here, click <em>Load</em> to have it scanned and get a sample of the content', $this->text_domain ) ) . html( 'p class="hide-in-help-tabs"', __( 'For this tutorial, click <em>Next</em> to automatically load an RSS feed example.', $this->text_domain ) ) . html( 'p class="hide-in-help-tabs"', __( 'The tutorial will continue when the RSS feed example finishes loading.', $this->text_domain ) ),
            'anchor_id' => '.tr-rss-url .import-feed',
            'edge'      => 'left',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step5'] = array(
            'title'     => html( 'h3', __( 'Content Sample', $this->text_domain ) ),
            'content'   => html( 'p', __( 'Every time you load an RSS feed, a sample of the feed content will be displayed.', $this->text_domain ) ) . html( 'p', __( 'This information is useful to see what data is provided in the RSS feed since some feeds provide more info then others.', $this->text_domain ) ) . html( 'p', __( 'The sample table shows the fields provided by the feed, their respective content and the total jobs it contains.', $this->text_domain ) ),
            'anchor_id' => '.tr-sample td:last-of-type',
            'edge'      => 'bottom',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
            'bind'      => 'goftj_rss_content_loaded',
        );
        $pointers['step6'] = array(
            'title'     => html( 'h3', __( 'Provider Details', $this->text_domain ) ),
            'content'   => html( 'p', __( 'Here you can fill in all the information for the current jobs provider.', $this->text_domain ) ) . html( 'p', __( 'If you have chosen a job provider from the list this information should be filled automatically, otherwise you need to fill it.', $this->text_domain ) ) . html( 'p', __( 'Either way, you can click <em>Edit</em> to change any values.', $this->text_domain ) ) . html( 'p', __( 'This information is displayed in each job page below the job description to credit the job provider.', $this->text_domain ) ),
            'anchor_id' => '.tr-provider-details',
            'edge'      => 'bottom',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step8'] = array(
            'title'     => html( 'h3', __( 'Jobs Setup - Terms', $this->text_domain ) ),
            'content'   => html( 'p', __( 'In this section you can specify the default terms that should be assigned to the jobs being imported.', $this->text_domain ) ) . html( 'p', __( 'Click <em>Edit</em> to change the terms to the ones that best fit the jobs you are importing.', $this->text_domain ) ),
            'anchor_id' => '.tr-taxonomies',
            'edge'      => 'bottom',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step10'] = array(
            'title'     => html( 'h3', __( 'Jobs Setup - Details', $this->text_domain ) ),
            'content'   => html( 'p', sprintf( __( 'Here you can further customize the custom fields default data for the jobs being imported, including featuring%s jobs.', $this->text_domain ), $this->premium_only( 'refer', 'proplus' ) ) ) . html( 'p', __( 'Click <em>Edit</em> to change the default custom fields data.', $this->text_domain ) ) . html( 'p', $this->premium_only( 'link', 'proplus' ) ),
            'anchor_id' => '.tr-meta',
            'edge'      => 'bottom',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step11'] = array(
            'title'     => html( 'h3', __( 'Jobs Setup - Posted By', $this->text_domain ) ),
            'content'   => html( 'p', __( 'Choose the user that should be assigned to all the jobs being imported.', $this->text_domain ) ),
            'anchor_id' => '#job_lister',
            'edge'      => 'left',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step14'] = array(
            'title'     => html( 'h3', __( 'Filter - Limit', $this->text_domain ) ),
            'content'   => html( 'p', __( 'Use this field to specify a limit for the jobs being imported.', $this->text_domain ) ),
            'anchor_id' => '.tr-limit input',
            'edge'      => 'left',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step15'] = array(
            'title'     => html( 'h3', __( 'Template Name', $this->text_domain ) ),
            'content'   => html( 'p', sprintf( __( 'If you intend to replicate this import at a later date or use this template in a scheduled import%s, fill in a meaningful template name here, and click <em>Save</em>.', $this->text_domain ), $this->premium_only( 'refer', 'proplus' ) ) ) . html( 'p', __( 'All import settings (except <em>Posted by</em> and <em>Filters</em>) will be saved in the template.', $this->text_domain ) ) . html( 'p', $this->premium_only( 'link', 'proplus' ) ),
            'anchor_id' => '.tr-template-name span.description',
            'edge'      => 'left',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['step16'] = array(
            'title'     => html( 'h3', __( 'Go Fetch Jobs!', $this->text_domain ) ),
            'content'   => html( 'p', __( 'When you are ready, click this button to start the import process and see the magic happens!', $this->text_domain ) ),
            'anchor_id' => '.import-posts',
            'edge'      => 'left',
            'align'     => 'left',
            'where'     => array( $this->screen_id ),
        );
        $pointers['help'] = array(
            'title'     => html( 'h3', sprintf( __( 'Thanks for using <em>%s</em>!', $this->text_domain ), $this->plugin_name ) ),
            'content'   => html( 'p', __( 'If you need to revisit this guided tour later or need specific help on an option use the - <em>Screen Options</em> - or - <em>Help</em> - tabs.', $this->text_domain ) ),
            'anchor_id' => 'h2:first',
            'edge'      => 'top',
            'align'     => 'right',
            'where'     => array( $this->screen_id ),
        );
        return $pointers;
    }
    
    /**
     * Custom CSS styles to be added on the page header.
     */
    public function css_styles()
    {
        ?>
	<style type="text/css">
		.contextual-help-tabs-wrap .hide-in-help-tabs {
			display: none;
		}

		.gofetch-wpjm-tour1_0_help .wp-pointer-arrow {
			left: 150px;
		}
	</style>
<?php 
    }
    
    /**
     * Helper for outputting premium plan only notes.
     */
    protected function premium_only( $part = 'refer', $plan = '' )
    {
        if ( !$plan && gfjwjm_fs()->is__premium_only() || $plan && gfjwjm_fs()->is_plan__premium_only( $plan, true ) ) {
            return '';
        }
        switch ( $plan ) {
            case 'proplus':
                $plan_desc = 'Pro+';
                break;
            case 'pro':
                $plan_desc = 'Pro';
                break;
            default:
                $plan_desc = 'Premium';
        }
        
        if ( 'refer' === $part ) {
            return ' (<span class="dashicons dashicons-lock"></span>)';
        } else {
            return sprintf( __( '(<span class="dashicons dashicons-lock"></span>) <a href="%1$s">%2$s plans only</a>', $this->text_domain ), admin_url( 'admin.php?page=go-fetch-jobs-wp-job-manager-pricing' ), $plan_desc );
        }
    
    }

}