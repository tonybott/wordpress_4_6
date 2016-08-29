=== Go Fetch Jobs (for WP Job Manager) ===
Author: SebeT
Contributors: SebeT, freemius
Tags: import, rss, feed, jobs, WP Job Manager, automated imports, scheduled imports, Jobify, Babysitter, Jobseek, WorkScout, Prime Jobs, JobHaus, JobFinder
Requires at least: 3.5
Tested up to: 4.5
Stable tag: 1.2.3

Instantly populate your WP Job Manager database using RSS job feeds from the most popular job sites.

== Description ==

> [Official site](http://gofetchjobs.com) / [DEMO site](http://gofetchjobs.com/wp-job-manager/wp-admin?demo=1)

Instantly populate your [WP Job Manager](https://wpjobmanager.com/) site with jobs from the most popular job sites and/or job aggregators. This handy plugin fetches jobs from RSS feeds and imports them to your jobs database. Pick your favorite job site, look for the jobs RSS feed, paste it directly to *Go Fetch Jobs* and instantly get fresh jobs in your database!

To help you quickly getting fresh jobs from external sites, *Go Fetch Jobs* comes bundled with ready to use RSS feeds and detailed instructions on how to setup RSS feeds for job sites that provide them, including [jobs.theguardian.com](jobs.theguardian.com).

Easily categorize bulk job imports with job categories, job types, default custom fields values and expiry dates.

Besides the usual *Title* + *Description* + *Date* usually provided by RSS feeds, *Go Fetch Jobs* can also (optionally) try to extract and auto fill job companies logos, company names and locations, if that information is provided by the RSS feed.

It also comes with the ability to save import rules as templates so you can later recycle/re-use them for new imports.

Upgrade to a *Pro+* plan to keep it automatically updated with fresh jobs added every day! (*)

> Features include:
>
> * Import jobs from any valid RSS feed
> * Seamless integration with WPJM jobs
> * Assign job expiry date
> * Save import rules as templates
> * Company logos on select providers
> * Company names and job locations on select providers
> * Ready to use RSS Feeds, including big sites like [jobs.theguardian.com](jobs.theguardian.com), with detailed setup instructions
>
> * And more...

> #### Additional features, exclusive to premium plans include:
> * Ready to use RSS feeds from popular job sites including: [monster.com](monster.com), [indeed.com](indeed.com), [careerjet.com](careerjet.com) and [craigslist](cragislist.org)
> * Custom RSS builder for select providers that allows creating custom RSS feeds with specific keywords/location, without leaving your site
> * Extract and auto-fill job company names and locations on select providers
> * **NEW** Auto assign job types and job categories based on each job content
> * **NEW** Filter imports using keywords
> * Automated scheduled imports

Visit the [official website](http://gofetchjobs.com) for more details on features and available plans.

(*) You can upgrade to any plan right directly from the plugin.

== Installation ==

1. Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from the Plugins page.
2. A new Menu named 'Go Fetch Jobs' will be available on the left sidebar.

== Frequently Asked Questions ==

= Why are jobs not showing full job descriptions ? =
Some sites don't provide full job descriptions on their RSS feeds. This is usually intended to have users still visit the original site to read the full description.

= Why don't all jobs show a logo after importing ? =
Logos are not available in all RSS feeds and unfortunately, RSS feeds that provide them are still the exception.

= Why do I sometimes see logo thumbnails from imported jobs in the backend but not in frontend ? =
Since some of the logos pulled from RSS feeds are from images stored on CDN's they cannot be stored as native WordPress featured images in the media library.
This means that if your theme does not use the default WPJM `the_company_logo()` function to display jobs on the frontend these logos will not be displayed. You'll need some custom
changes to be able to do that. I can help you on this if you need it.

= How do I import jobs so that they are all automatically assigned to the matching category ? =
Only the *Premium* plans provide this feature through the *Smart Assign* option. In the *Free* version you need to choose one category that will be assigned to all jobs on each bulk import.

= How do I activate my premium plan after purchase ? =
After your purchase, deactivate the Free version and download and activate the premium version. Under *Go Fetch Jobs > Account*, click 'Sync' or the *Activate Pro/Pro+ Plan* button.
If the premium plan is not activated immediately please try again later since it can take a few minutes until the server is updated.


== Screenshots ==

1. Existing RSS Providers List
2. Load Saved Import Templates
3. RSS Feed Setup Detailed Instructions
4. Custom RSS Feed Builder
5. Fetch Job Companies Logos
6. Set Job Providers Information / Optional URL Parameters
7. Set Job Details for Imported Jobs
8. Filters / Save Templates
9. Job Listings for Imported Jobs (Frontend)
10. Single Job Page for Imported Jobs (Frontend)

== Changelog ==

= 1.2.3 =
* Fixed: Existing location info for providers using geolocation tags (like Indeed) sometimes not recognized and displayed as 'Anywhere'
* Fixed: Schedules authors not saving correctly
* Fixed: W3 Total Cache plugin sometimes causing import process to fail
* Fixed: Template names using quotes/double-quotes causing javascript issues
* Fixed: Multi-country providers not inheriting main provider settings (ignoring location and other meta)
* Changed: Added 30 seconds time interval between schedule runs (interval time can be customized using the filter - 'goft_wpjm_schedules_interval_sleep')
* Changed: Added multi-country provider 'monster.com' (US, Canada, UK, France, Germany and Netherlands) with location meta (Pro+ plan only)

= 1.2.2 =
* Fixed: Fatal error on plugin install screen (Premium plans only)

= 1.2.1 =
* Fixed: Job categories/types editable fields showing below the details group (Free version only)
* Fixed: Fatal errors when viewing the plugins page
* Fixed: Conflicts with WooCommerce 'select2' libraries causing weird visual issues in import jobs page

= 1.2 =
* Fixed: Users dropdown only showing admin users
* Changes: Tweaked UI
* Changes: Replaced simple providers dropdown with fancier 'Select2' dropdown
* Changes: Added taxonomy terms 'Smart Assign' option (automatically assigns the best term(s) to each imported job considering jobs content - for Pro and Pro+ plans)
* Changes: Added 'Keyword' filtering option to filter imported jobs using keywords (for Pro and Pro+ plans)
* Changes: Added log meta box to schedule pages (provides stats info on imported, duplicates, and total excluded jobs - pro and pro+ plans)
* Changes: Added 'Advanced/Basic' toggle under the 'Screen Options' for toggling between basic/advanced import settings
* Changes: Added guided help tour
* Changes: Added 'Screen Options'
* Changes: Added 'Help' tab
* Changes: Added new generic RSS providers: trabajos.com, us.jobs
* Changes: Highlight job expiry date if older then today

= 1.1.2 =
* Fixed: Invalid 'Create Template' link under schedule pages
* Fixed: Use 'the_content' filter for feeds description (fixes HTML entities not properly converted on some RSS feeds)
* Fixed: Strip tags in titles (fixes HTML tags showing in post titles on some RSS feeds)
* Changes: Added Option to force load a feed (for feeds that although valid may fail to load)

= 1.1.1 =
* Fixed: feeds not loading in Firefox

= 1.1 =
* Fixed: feeds from known providers not returning any data


== Upgrade Notice ==
This is the first stable version.
