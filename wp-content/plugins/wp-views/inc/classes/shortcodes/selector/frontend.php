<?php

if ( ! class_exists( 'Toolset_Shortcode_Generator_Frontend' ) ) {

	abstract class Toolset_Shortcode_Generator_Frontend {

		private static $registered_admin_bar_items	= array();
		private static $can_show_admin_bar_item		= false;
		private static $target_dialog_added			= false;

		function __construct() {

			add_action( 'init',		array( $this, 'register_shortcodes_admin_bar_items' ), 99 );
			add_action( 'admin_bar_menu',	array( $this, 'display_shortcodes_admin_bar_items' ), 99 );
			add_action( 'wp_footer',		array( $this, 'display_shortcodes_target_dialog' ) );
		}

		public function register_shortcodes_admin_bar_items() {
			$registered_items = self::$registered_admin_bar_items;
			$registered_items = apply_filters( 'Toolset_Shortcode_Generator_Frontend_register_item', $registered_items );
			self::$registered_admin_bar_items = $registered_items;
		}

		/*
		 * Add admin bar main item for shortcodes
		 */
		public function display_shortcodes_admin_bar_items( $wp_admin_bar ) {
			if ( ! is_admin() ) {
				return;
			}
			$registered_items = self::$registered_admin_bar_items;
			if ( empty( $registered_items ) ) {
				return;
			}
			self::$can_show_admin_bar_item = true;
			$this->create_admin_bar_item( $wp_admin_bar, 'toolset-shortcodes', __( 'Toolset shortcodes', 'wpv-views' ), '#', false );
			foreach ( $registered_items as $item_key => $item_args ) {
				$this->create_admin_bar_item( $wp_admin_bar, $item_args['id'], $item_args['title'], $item_args['href'], $item_args['parent'], $item_args['meta'] );
			}
		}

		/*
		 * General function for creating admin bar menu items
		 * 
		 */
		public static function create_admin_bar_item( $wp_admin_bar, $id, $name, $href, $parent, $classes = null ) {
			$args = array(
				'id'		=> $id,
				'title'		=> $name,
				'href'		=> $href,
				'parent'	=> $parent,
				'meta' 		=> array( 'class' => $id . '-shortcode-menu ' . $classes )
			);
			$wp_admin_bar->add_node( $args );
		}

		/*
		 * Dialog Template HTML code
		 */
		public function display_shortcodes_target_dialog() {
			if (
				self::$can_show_admin_bar_item
				&& self::$target_dialog_added === false
			) {
				?>
				<div class="toolset-dialog-container" style="display:none">
					<div id="toolset-shortcode-generator-target-dialog" class="toolset-shortcode-gui-dialog-container js-toolset-shortcode-generator-target-dialog">
						<textarea id="toolset-shortcode-generator-target" class="textarea" rows="4" style="width:100%;"></textarea>
					</div>
				</div>
				<?php
				self::$target_dialog_added = true;
			}

		}



	}

}

if ( ! class_exists( 'WPV_Shortcode_Generator_Frontend' ) ) {

	class WPV_Shortcode_Generator_Frontend extends Toolset_Shortcode_Generator_Frontend {

		function __construct() {
			parent::__construct();
			$this->is_registered		= false;
			$this->wpv_editor_addon		= null;
			// Register the Fields and Views item
			add_filter( 'Toolset_Shortcode_Generator_Frontend_register_item', array( $this, 'register_fields_and_views_shortcode_generator' ) );
			// Make sure the Fields and Views dialog is added, even on pages without editors
			add_action( 'wp_footer', array( $this, 'force_fields_and_views_dialog_shortcode_generator' ), 1 );
		}

		public function register_fields_and_views_shortcode_generator( $registered_sections ) {
			$toolset_options = get_option( 'toolset_options', array() );
			$toolset_shortcodes_generator = ( isset( $toolset_options['shortcodes_generator'] ) && in_array( $toolset_options['shortcodes_generator'], array( 'unset', 'disable', 'editor', 'always' ) ) ) ? $toolset_options['shortcodes_generator'] : 'unset';
			if ( $toolset_shortcodes_generator == 'unset' ) {
				$toolset_shortcodes_generator = apply_filters( 'toolset_filter_force_unset_shortcode_generator_option', $toolset_shortcodes_generator );
			}



			// Register the section and make sure the right assets are also included
			// Not the best solution, but the one we have :-(
			if ( ! wp_script_is( 'views-shortcodes-gui-script' ) ) {
				wp_enqueue_script( 'views-shortcodes-gui-script' );
			}

			if ( ! wp_style_is( 'views-admin-css' ) ) {
				wp_enqueue_style( 'views-admin-css' );
			}
			// Force Types assets for Types shortcodes
			if (
				wp_script_is( 'types', 'registered' )
				&& ! wp_script_is( 'types', 'enqueued' )
			) {
				wp_enqueue_script( 'types' );
			}
			if (
				wp_script_is( 'types-wp-views', 'registered' )
				&& ! wp_script_is( 'types-wp-views', 'enqueued' )
			) {
				wp_enqueue_script( 'types-wp-views' );
			}
			if (
				wp_script_is( 'toolset-colorbox', 'registered' )
				&& ! wp_script_is( 'toolset-colorbox', 'enqueued' )
			) {
				wp_enqueue_script( 'toolset-colorbox' );
			}
			if (
				wp_style_is( 'toolset-colorbox', 'registered' )
				&& ! wp_style_is( 'toolset-colorbox', 'enqueued' )
			) {
				wp_enqueue_style( 'toolset-colorbox' );
			}
			$this->is_registered = true;
			$this->wpv_editor_addon = new WPV_Editor_addon(
				'wpv-views',
				__('Insert Views Shortcodes', 'wpv-views'),
				WPV_URL . '/res/js/views_editor_plugin.js',
				'',
				false
			);

			// add all shortcodes to dialog
			add_short_codes_to_js(
				array(
					'post', // wpv-post-** shortcodes plus non-Types custom fields
					'post-extended', // generic shortcodes extended in the Basic section
					'post-fields-placeholder', // non-Types fields on demand
					'types-post', // Types custom fields
					'types-post-usermeta', // Types usermeta fields
					'user', // basic user data
					'post-view', // all available Views listing posts
					'taxonomy-view', // all available Views listing terms
					'user-view', // all available Views listing users
					'body-view-templates', // all available CT
					'wpml'      // WPML-related shortcodes
				),
				$this->wpv_editor_addon
			);
			$registered_sections[ 'fields_and_views' ] = array(
				'id'		=> 'fields-and-views',
				'title'		=> __( 'Fields and Views', 'wpv-views' ),
				'href'		=> '#fields_and_views_shortcodes',
				'parent'	=> 'toolset-shortcodes',
				'meta'		=> 'js-wpv-shortcode-generator-node'
			);
			return $registered_sections;
		}

		public function force_fields_and_views_dialog_shortcode_generator() {
			if ( $this->is_registered ) {
				// If we got to the footer without an editor that generates the Fields and Views dialog
				// It means we are on a page that might as well show all the Types shortcodes too
				// Since there is no active post to restrict to
				do_action( 'wpv_action_wpv_add_types_postmeta_usermeta_to_editor_menus' );
				$this->wpv_editor_addon->render_shortcodes_wrapper_dialogs();
			}
		}

		public function display_shortcodes_target_dialog() {
			parent::display_shortcodes_target_dialog();
			if ( $this->is_registered ) {
				?>
				<div class="toolset-dialog-container">
					<div id="wpv-shortcode-generator-target-dialog" class="toolset-shortcode-gui-dialog-container js-wpv-shortcode-generator-target-dialog">
						<div class="wpv-dialog">
							<p>
								<?php echo __( 'This is the generated shortcode, based on the settings that you have selected:', 'wpv-views' ); ?>
							</p>
							<span id="wpv-shortcode-generator-target" style="font-family:monospace;display:block;padding:5px;background-color:#ededed"></span>
							<p>
								<?php echo __( 'You can now copy and paste this shortcode anywhere you want.', 'wpv-views' ); ?>
							</p>
						</div>
					</div>
				</div>
				<?php
			}
		}
	}

}

new WPV_Shortcode_Generator_Frontend();