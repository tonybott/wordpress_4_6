<<<<<<< HEAD
/**
 * views_shortcode_gui.js
 *
 * Contains helper functions for the popup GUI used to set Views shortcode attributes
 *
 * @since 1.7
 * @package Views
 */

var WPViews = WPViews || {};

WPViews.ShortcodesGUI = function( $ ) {
	var self = this;

	// Parametric search
	self.ps_view_id = 0;
	self.ps_orig_id = 0;

	self.dialog_insert_view						= null;
	self.dialog_insert_shortcode				= null;
	self.dialog_insert_views_conditional		= null;
	self.shortcodes_wrapper_dialogs				= {};
	
	self.dialog_minWidth						= 870;
	
	self.calculate_dialog_maxWidth = function() {
		return ( $( window ).width() - 100 );
	};
	
	self.calculate_dialog_maxHeight = function() {
		return ( $( window ).height() - 100 );
	};

	self.dialog_insert_view_locked				= false;

	self.suggest_cache							= {};
	self.shortcode_gui_insert					= 'insert';// @note This is also used in Types as a dependency!!
	self.shortcodes_set							= 'posts';
	self.shortcode_to_insert_on_target_dialog	= '';
	self.shortcode_gui_insert_count 			= 0;

	self.shortcode_gui_computed_attribute_pairs_filters = {};

	self.views_conditional_qtags_opened			= false;

	self.post_field_section						= false;

	self.views_conditional_use_gui				= true;

	self.numeric_natural_pattern				= /^[0-9]+$/;
	self.numeric_natural_list_pattern			= /^\d+(?:,\d+)*$/;
	self.numeric_natural_extended_pattern		= /^(-1|[0-9]+)$/;
	self.year_pattern							= /^([0-9]{4})$/;
	self.month_pattern							= /^([1-9]|1[0-2])$/;
	self.week_pattern							= /^([1-9]|[1234][0-9]|5[0-3])$/;
	self.day_pattern							= /^([1-9]|[12][0-9]|3[0-1])$/;
	self.hour_pattern							= /^([0-9]|[1][0-9]|2[0-3])$/;
	self.minute_pattern							= /^([0-9]|[1234][0-9]|5[0-9])$/;
	self.second_pattern							= /^([0-9]|[1234][0-9]|5[0-9])$/;
	self.dayofyear_pattern						= /^([1-9]|[1-9][0-9]|[12][0-9][0-9]|3[0-6][0-6])$/;
	self.dayofweek_pattern						= /^[1-7]+$/;
	self.url_patern								= /^(https?):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
	self.orderby_postfield_pattern				= /^field-/;
	self.orderby_termmeta_field_pattern			= /^taxonomy-field-/;
	self.orderby_usermeta_field_pattern			= /^user-field-/;

	/**
	 * Temporary dialog content to be displayed while the actual content is loading.
	 *
	 * It contains a simple spinner in the centre. I decided to implement styling directly, it will not be reused and
	 * it would only bloat views-admin.css (jan).
	 *
	 * @type {HTMLElement}
	 * @since 1.9
	 */
	self.shortcodeDialogSpinnerContent = $(
		'<div style="min-height: 150px;">' +
		'<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; ">' +
		'<div class="wpv-spinner ajax-loader"></div>' +
		'<p>' + wpv_shortcodes_gui_texts.loading_options + '</p>' +
		'</div>' +
		'</div>'
	);

	self.init = function() {
		self.init_admin_bar_button();
		self.init_dialogs();
	};

	self.init_dialogs = function() {
		// Initialize dialogs
		if ( ! $('#js-wpv-shortcode-gui-dialog-container').length ) {
			$( 'body' ).append( '<div id="js-wpv-shortcode-gui-dialog-container" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-shortcode-gui-dialog-container"></div>' );
			self.dialog_insert_shortcode = $( "#js-wpv-shortcode-gui-dialog-container" ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass( 'modal-open' );
					$( '.js-wpv-shortcode-gui-insert' )
						.addClass( 'button-secondary' )
						.removeClass( 'button-primary ui-button-disabled ui-state-disabled' )
						.prop( 'disabled', true );
				},
				close: function( event, ui ) {
					$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_closed' );
					$( 'body' ).removeClass( 'modal-open' );
				},
				buttons:[
					{
						class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-wpv-shortcode-gui-button-close js-wpv-shortcode-gui-close',
						text: wpv_shortcodes_gui_texts.wpv_close,
						click: function() {
							$( this ).dialog( "close" );
						}
					},
					{
						class: 'button-secondary js-wpv-shortcode-gui-button-insert js-wpv-shortcode-gui-insert',
						text: wpv_shortcodes_gui_texts.wpv_insert_shortcode,
						disabled: 'disabled',
						click: function() {
							self.wpv_insert_shortcode();
						}
					}
				]
			});

			$( 'body' ).append( '<div id="js-wpv-view-shortcode-gui-dialog-container" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-shortcode-gui-dialog-container"></div>' );
			self.dialog_insert_view = $( "#js-wpv-view-shortcode-gui-dialog-container" ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass( 'modal-open' );
					$( '.js-wpv-insert-view-form-action' )
						.addClass( 'button-secondary' )
						.removeClass( 'button-primary ui-button-disabled ui-state-disabled' )
						.prop( 'disabled', true );
				},
				close: function( event, ui ) {
					$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_closed' );
					$( 'body' ).removeClass( 'modal-open' );
				},
				buttons:[
					{
						class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-wpv-shortcode-gui-button-close',
						text: wpv_shortcodes_gui_texts.wpv_close,
						click: function() {
							$( this ).dialog( "close" );
						}
					},
					{
						class: 'button-secondary js-wpv-shortcode-gui-button-insert js-wpv-insert-view-form-action',
						text: wpv_shortcodes_gui_texts.wpv_insert_shortcode,
						disabled: 'disabled',
						click: function() {
							self.wpv_insert_view_shortcode_to_editor();
						}
					}
				]
			});

			$( 'body' ).append( '<div id="js-wpv-views-conditional-shortcode-gui-dialog-container" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-shortcode-gui-dialog-container"></div>' );
			self.dialog_insert_views_conditional = $( "#js-wpv-views-conditional-shortcode-gui-dialog-container" ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass( 'modal-open' );
					$( ".ui-dialog-titlebar-close" ).hide();
					self.views_conditional_use_gui = true;
					$( '.js-wpv-shortcode-gui-insert' )
						.addClass( 'button-secondary' )
						.removeClass( 'button-primary ui-button-disabled ui-state-disabled' )
						.prop( 'disabled', true );
				},
				close: function( event, ui ) {
					if (  !self.views_conditional_qtags_opened && typeof self.wpv_conditional_object.ed.openTags !== 'undefined' ){
						var ed = self.wpv_conditional_object.ed, ret = false, i = 0;
						self.views_conditional_qtags_opened = false;
						while ( i < ed.openTags.length ) {
							ret = ed.openTags[i] == self.wpv_conditional_object.t.id ? i : false;
							i ++;
						}
						ed.openTags.splice(ret, 1);
						self.wpv_conditional_object.e.value = self.wpv_conditional_object.t.display;
					}
					$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_closed' );
					$( 'body' ).removeClass( 'modal-open' );
				},
				buttons:[
					{
						class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-wpv-shortcode-gui-close',
						text: wpv_shortcodes_gui_texts.wpv_close,
						click: function() {
							// remove wpv-conditional from QTags:opened tags
							self.wpv_conditional_close = false;
							self.views_conditional_qtags_opened = false;
							if ( !self.views_conditional_qtags_opened && typeof self.wpv_conditional_object.openTags !== 'undefined' ) {
								var ed = self.wpv_conditional_object.ed, ret = false, i = 0;
								while ( i < ed.openTags.length ) {
									ret = ed.openTags[i] == self.wpv_conditional_object.t.id ? i : false;
									i ++;
								}
								ed.openTags.splice(ret, 1);
								self.wpv_conditional_object.e.value = self.wpv_conditional_object.t.display;
							}
							$( this ).dialog( "close" );
						}
					},
					{
						class: 'button-secondary js-wpv-shortcode-gui-insert',
						text: wpv_shortcodes_gui_texts.wpv_insert_shortcode,
						disabled: 'disabled',
						click: function() {
							self.wpv_insert_view_conditional_shortcode();
						}
					}
				]
			});
		}

		var dialog_posts		= $( 'body' ).find('.js-wpv-fields-and-views-dialog-for-posts'),
		dialog_taxonomy			= $( 'body' ).find('.js-wpv-fields-and-views-dialog-for-taxonomy'),
		dialog_users			= $( 'body' ).find('.js-wpv-fields-and-views-dialog-for-users');

		if ( dialog_posts.length > 0 ) {
			self.shortcodes_wrapper_dialogs[ 'posts' ] = $( '.js-wpv-fields-and-views-dialog-for-posts' ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				title:		wpv_shortcodes_gui_texts.wpv_fields_and_views_title,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass('modal-open');
					// Hide top links if div too small
					wpv_hide_top_groups( $( this ).parent() );
					$( dialog_posts )
						.find( '.search_field' )
						.focus();
					var data_for_events = {};
					data_for_events.kind = 'posts';
                    data_for_events.dialog = this;
					$( document ).trigger( 'js_event_wpv_fields_and_views_dialog_opened', [ data_for_events ] );
				},
				close: function( event, ui ) {
					$( 'body' ).removeClass( 'modal-open' );
					$( this ).dialog("close");
				}
			});
		}

		if ( dialog_taxonomy.length > 0 ) {
			self.shortcodes_wrapper_dialogs[ 'taxonomy' ] = $( '.js-wpv-fields-and-views-dialog-for-taxonomy' ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				title:		wpv_shortcodes_gui_texts.wpv_fields_and_views_title,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass('modal-open');
					// Hide top links if div too small
					wpv_hide_top_groups( $( this ).parent() );
					$( dialog_taxonomy )
						.find( '.search_field' )
						.focus();
					var data_for_events = {};
					data_for_events.kind = 'taxonomy';
					data_for_events.dialog = this;
					$( document ).trigger( 'js_event_wpv_fields_and_views_dialog_opened', [ data_for_events ] );
				},
				close: function( event, ui ) {
					$( 'body' ).removeClass( 'modal-open' );
					$( this ).dialog("close");
				}
			});
		}

		if ( dialog_users.length > 0 ) {
			self.shortcodes_wrapper_dialogs[ 'users' ] = $( '.js-wpv-fields-and-views-dialog-for-users' ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				title:		wpv_shortcodes_gui_texts.wpv_fields_and_views_title,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass('modal-open');
					// Hide top links if div too small
					wpv_hide_top_groups( $( this ).parent() );
					$( dialog_users )
						.find( '.search_field' )
						.focus();
					var data_for_events = {};
					data_for_events.kind = 'users';
                    data_for_events.dialog = this;
					$( document ).trigger( 'js_event_wpv_fields_and_views_dialog_opened', [ data_for_events ] );
				},
				close: function( event, ui ) {
					$( 'body' ).removeClass( 'modal-open' );
					$( this ).dialog("close");
				}
			});
		}

		self.textarea_target_dialog = $('#wpv-shortcode-generator-target-dialog').dialog({
			autoOpen:	false,
			modal:		true,
			width:		self.dialog_minWidth,
			title:		wpv_shortcodes_gui_texts.wpv_shortcode_generated,
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			create: function( event, ui ) {
				$( event.target ).parent().css( 'position', 'fixed' );
			},
			buttons: [
				{
					class: 'button-primary',
					text: wpv_shortcodes_gui_texts.wpv_close,
					click: function() {
						$( this ).dialog( "close" );
					}
				},
			],
			open: function( event, ui ) {
				// TODO: close shortcode generator dialog now
				if ( _.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set ) ) {
					self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog("close");
				}
				$( '#wpv-shortcode-generator-target' )
					.html( self.shortcode_to_insert_on_target_dialog )
					.focus();
				$('body').addClass('modal-open');
			},
			close: function( event, ui ) {
				$('body').removeClass('modal-open');
				self.shortcode_gui_insert = 'insert';
				$( this ).dialog("close");
			}
		});
	};

	//-----------------------------------------
	// Fields and Views button management
	//
	// @since 1.12
	//-----------------------------------------

	/**
	 * Init the admin bar button, if any, and make sure we load the right dialog when editing a View
	 *
	 * @since 1.10
	 */

	self.init_admin_bar_button = function() {
		if ( $( '.js-wpv-shortcode-generator-node a' ).length > 0 ) {
			$( '.js-wpv-shortcode-generator-node a' )
				.addClass( 'js-wpv-fields-and-views-in-adminbar' )
				.removeClass( 'js-wpv-fields-and-views-in-toolbar' );
		}
		if ( $( '.js-wpv-query-type' ).length > 0 ) {
			self.shortcodes_set = $( '.js-wpv-query-type:checked' ).val();
			$( document ).on( 'change', '.js-wpv-query-type', function() {
				self.shortcodes_set = $( '.js-wpv-query-type:checked' ).val();
			});
		}
	};

	$( document ).on( 'click', '.toolset-shortcodes-shortcode-menu', function( e ) {
		e.preventDefault();
		return false;
	});

	/**
	 * Set the right active editor when clicking any Fields and Views button, and open / close the dialogs when needed
	 */

	$( document ).on( 'click','.js-wpv-fields-and-views-in-adminbar', function( e ) {
		e.preventDefault();
		self.shortcode_gui_insert = 'create';
		self.open_fields_and_views_dialog();
		return false;
	});

	$( document ).on( 'click', '.js-wpv-fields-and-views-in-toolbar', function( e ) {
		e.preventDefault();
		var thiz = $( this );
		if ( thiz.attr( 'data-editor' ) ) {
			window.wpcfActiveEditor = thiz.data( 'editor' );
		}
		self.shortcode_gui_insert = 'insert';
		self.open_fields_and_views_dialog();
		return false;
	});

	self.open_fields_and_views_dialog = function() {
		if ( _.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set ) ) {
			self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog( 'open' ).dialog({
				height:		self.calculate_dialog_maxHeight(),
				maxWidth:	self.calculate_dialog_maxWidth(),
				position: 	{
					my:			"center top+50",
					at:			"center top",
					of:			window,
					collision:	"none"
				}
			});
		}
		// Bind Escape
		$( document ).bind( 'keyup', function( e ) {
			if ( e.keyCode == 27 ) {
				if ( _.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set ) ) {
					self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog( 'close' );
				}
				$( this ).unbind( e );
			}
		});
	};

	// Close when clicking on an item from it, always
	$( document ).on( 'click', '.js-wpv-fields-views-dialog-content .item', function( event, data ) {
		if (
			_.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set )
			&& self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog( "isOpen" )
		) {
			self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog('close');
		}
	});

	// Open the textarea_target_dialog dialog when the action is set to "create" and fill the value it needs to show

	$( document ).on( 'js_event_wpv_shortcode_inserted', function( event, shortcode_name, shortcode_content, shortcode_attribute_values, shortcode_to_insert ) {
		if ( self.shortcode_gui_insert == 'create' ) {
			self.shortcode_to_insert_on_target_dialog = shortcode_to_insert;

			if( typeof wpv_add_shortcode_to !== 'undefined' ) {
				wpv_add_shortcode_to.val( wpv_add_shortcode_to.val() + shortcode_to_insert );
				return;
			}

			self.textarea_target_dialog.dialog("open").dialog({
				maxHeight:	self.calculate_dialog_maxHeight(),
				maxWidth:	self.calculate_dialog_maxWidth(),
				position:	{
					my:			"center top+50",
					at:			"center top",
					of:			window,
					collision:	"none"
				}
			});
			self.shortcode_gui_insert = 'insert';
		}
	});

	$( document ).on( 'js_types_shortcode_created', function( event, shortcode_to_insert ) {
		if ( self.shortcode_gui_insert == 'create' ) {
			self.shortcode_to_insert_on_target_dialog = shortcode_to_insert;

			if( typeof wpv_add_shortcode_to !== 'undefined' ) {
				wpv_add_shortcode_to.val( wpv_add_shortcode_to.val() + shortcode_to_insert );
				return;
			}

			self.textarea_target_dialog.dialog("open").dialog({
				maxHeight:	self.calculate_dialog_maxHeight(),
				maxWidth:	self.calculate_dialog_maxWidth(),
				position:	{
					my:			"center top+50",
					at:			"center top",
					of:			window,
					collision:	"none"
				}
			});
			self.shortcode_gui_insert = 'insert';
		}
	});

	//-----------------------------------------
	// Parametric search
	//-----------------------------------------


	self.wpv_insert_view_shortcode_dialog = function( view_id, view_title, view_name, orig_id, nonce ) {
		self.ps_view_id = view_id;
		self.ps_orig_id = orig_id;

		var data_view = {
			action:		'wpv_view_form_popup',
			_wpnonce:	nonce,
			view_id:	view_id,
			orig_id:	orig_id,
			view_title:	view_title,
			view_name:	view_name
		},
		data_for_events = {};

		data_for_events.shortcode = 'wpv-view';
		data_for_events.title = view_title;
		data_for_events.params = {};
		data_for_events.nonce = nonce;
		data_for_events.object = {};
        data_for_events.dialog = this;
		$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_triggered', [ data_for_events ] );

		self.dialog_insert_view.dialog('open').dialog({
			title:		view_title,
			maxHeight:	self.calculate_dialog_maxHeight(),
			maxWidth:	self.calculate_dialog_maxWidth(),
			position:	{
				my:			"center top+50",
				at:			"center top",
				of:			window,
				collision:	"none"
			}
		});

		self.manage_dialog_button_labels();

		self.dialog_insert_view.html( self.shortcodeDialogSpinnerContent );

		//
		// Do AJAX call
		//
		$.ajax({
			url: wpv_shortcodes_gui_texts.ajaxurl,
			data: data_view,
			type:"GET",
			success: function( data ) {
				$( 'body' ).addClass( 'modal-open' );
				self.dialog_insert_view.html( data );
				$( '.js-wpv-insert-view-form-action' )
					.addClass( 'button-primary' )
					.removeClass( 'button-secondary' )
					.prop( 'disabled', false );
				$('.js-wpv-shortcode-gui-tabs')
					.tabs({
						beforeActivate: function( event, ui ) {
							if (
								ui.oldPanel.attr( 'id' ) == 'js-wpv-insert-view-parametric-search-container'
								&& self.dialog_insert_view_locked
							) {
								event.preventDefault();
								ui.oldTab.focus().addClass( 'wpv-shortcode-gui-tabs-incomplete' );
								$( '.wpv-advanced-setting', ui.oldPanel ).addClass( 'wpv-advanced-setting-incomplete' );
								setTimeout( function() {
									ui.oldTab.removeClass( 'wpv-shortcode-gui-tabs-incomplete' );
									$( '.wpv-advanced-setting', ui.oldPanel ).removeClass( 'wpv-advanced-setting-incomplete' );
								}, 1000 );
							}
						}
					})
					.addClass('ui-tabs-vertical ui-helper-clearfix')
					.removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
				$('#js-wpv-shortcode-gui-dialog-tabs ul, #js-wpv-shortcode-gui-dialog-tabs li').removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');

				$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_opened', [ data_for_events ] );
			}
		});
	};
	
	$( document ).on( 'change input cut paste', '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby', function() {
		var orderby_value = $( this ).val();
		
		if (
			self.orderby_postfield_pattern.test( orderby_value ) 
			|| self.orderby_termmeta_field_pattern.test( orderby_value )
			|| self.orderby_usermeta_field_pattern.test( orderby_value )
		) {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby_as-setting' ).fadeIn( 'fast' );
		} else {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby_as-setting' ).hide();
		}
	});
	
	$( document ).on( 'change input cut paste', '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby_second', function() {
		var orderby_second_value = $( this ).val();
		
		if ( orderby_second_value == '' ) {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-order_second' ).prop( 'disabled', true );
		} else {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-order_second' ).prop( 'disabled', false );
		}
	});

	self.wpv_get_view_override_values = function() {
		var override_container = $( '#js-wpv-insert-view-override-container' ),
			override_values = {};
			
		if ( $( '.js-wpv-insert-view-shortcode-limit', override_container ).val() != '' ) {
			override_values['limit'] = $( '.js-wpv-insert-view-shortcode-limit', override_container ).val();
		}
		if ( $( '.js-wpv-insert-view-shortcode-offset', override_container ).val() != '' ) {
			override_values['offset'] = $( '.js-wpv-insert-view-shortcode-offset', override_container ).val();
		}
		if ( $( '.js-wpv-insert-view-shortcode-orderby', override_container ).val() != '' ) {
			override_values['orderby'] = $( '.js-wpv-insert-view-shortcode-orderby', override_container ).val();
			if ( 
				$( '.js-wpv-insert-view-shortcode-orderby_as', override_container ).length > 0
				&& $( '.js-wpv-insert-view-shortcode-orderby_as', override_container ).val() != '' 
			) {
				if (
					self.orderby_postfield_pattern.test( override_values['orderby'] ) 
					|| self.orderby_termmeta_field_pattern.test( override_values['orderby'] )
					|| self.orderby_usermeta_field_pattern.test( override_values['orderby'] )
				) {
					override_values['orderby_as'] = $( '.js-wpv-insert-view-shortcode-orderby_as', override_container ).val();
				}
			}
		}
		if ( $( '.js-wpv-insert-view-shortcode-order', override_container ).val() != '' ) {
			override_values['order'] = $( '.js-wpv-insert-view-shortcode-order', override_container ).val();
		}
		// Secondary sorting
		if ( 
			$( '.js-wpv-insert-view-shortcode-orderby_second', override_container ).length > 0 
			&& $( '.js-wpv-insert-view-shortcode-orderby_second', override_container ).val() != '' 
		) {
			override_values['orderby_second'] = $( '.js-wpv-insert-view-shortcode-orderby_second', override_container ).val();
		}
		if ( 
			$( '.js-wpv-insert-view-shortcode-order_second', override_container ).length > 0 
			&& $( '.js-wpv-insert-view-shortcode-order_second', override_container ).val() != '' 
		) {
			override_values['order_second'] = $( '.js-wpv-insert-view-shortcode-order_second', override_container ).val();
		}
		return override_values;
	};

	self.wpv_get_view_extra_values = function() {
		var extra_container = $( '#js-wpv-insert-view-extra-attributes-container' ),
			extra_values = {};
		if ( extra_container.length > 0 ) {
			$( '.js-wpv-insert-view-shortcode-extra-attribute', extra_container ).each( function() {
				var thiz = $( this );
				if ( thiz.val() != '' ) {
					extra_values[ thiz.data( 'attribute' ) ] = thiz.val();
				}
			});
		}
		return extra_values;
	};

	self.wpv_get_view_cache_values = function() {
		var cache_container = $( '#js-wpv-insert-view-cache-attributes-container' ),
			cache_values = {};
		if ( cache_container.length > 0 ) {
			var use_cache = $( '.js-wpv-insert-view-shortcode-cache:checked', cache_container ).val();
			if ( 'off' == use_cache ) {
				cache_values['cached'] = 'off';
			}
		}
		return cache_values;
	};

	self.dialog_insert_view_locked_check = function() {
		var container = $( '#js-wpv-insert-view-parametric-search-container' );
		if ( $( '.js-wpv-insert-view-form-display:checked', container ).val() == 'form' ) {
			var target = $( '.js-wpv-insert-view-form-target:checked', container ).val(),
				set_target = $( '.js-wpv-insert-view-form-target-set:checked', container ).val(),
				set_target_id = $( '.js-wpv-insert-view-form-target-set-existing-id', container ).val();
			if ( target == 'self' ) {
				$( '.js-wpv-insert-view-form-action' ).addClass( 'button-primary' ).removeClass( 'button-secondary' ).prop( 'disabled', false );
				self.dialog_insert_view_locked = false;
			} else {
				if ( set_target == 'existing' && set_target_id != '' ) {
					$( '.js-wpv-insert-view-form-target-set-actions' ).show();
				}
				$( '.js-wpv-insert-view-form-action' ).removeClass( 'button-primary' ).addClass( 'button-secondary' ).prop( 'disabled', true );
				self.dialog_insert_view_locked = true;
			}
		} else {
			self.dialog_insert_view_locked = false;
		}
	};

	self.wpv_insert_view_shortcode_to_editor = function() {
		var form_name = $( '#js-wpv-view-shortcode-gui-dialog-view-title' ).val(),
			override_values = self.wpv_get_view_override_values(),
			override_values_string = '',
			extra_values = self.wpv_get_view_extra_values(),
			extra_values_string = '',
			cache_values = self.wpv_get_view_cache_values(),
			cache_values_string = '',
			valid = self.validate_shortcode_attributes( $( '#js-wpv-view-shortcode-gui-dialog-container' ), $( '#js-wpv-view-shortcode-gui-dialog-container' ), $( '#js-wpv-view-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) ),
			shortcode_to_insert = '',
			shortcode_attribute_values = {};

		if ( ! valid ) {
			return;
		}

		shortcode_attribute_values['name'] = form_name;
		_.map( override_values, function( over_val, over_key ) {
			shortcode_attribute_values[ over_key ] = over_val;
			override_values_string += ' ' + over_key + '="' + over_val + '"';
		});
		_.map( extra_values, function( extra_val, extra_key ) {
			shortcode_attribute_values[ extra_key ] = extra_val;
			extra_values_string += ' ' + extra_key + '="' + extra_val + '"';
		});
		_.each( cache_values, function( cache_val, cache_key ) {
			shortcode_attribute_values[ cache_key ] = cache_val;
			cache_values_string += ' ' + cache_key + '="' + cache_val + '"';
		});

		if ( $( '#js-wpv-insert-view-parametric-search-container' ).length > 0 ) {

			var display = $( '.js-wpv-insert-view-form-display:checked' ).val(),
				target = $( '.js-wpv-insert-view-form-target:checked' ).val(),
				set_target = $( '.js-wpv-insert-view-form-target-set:checked' ).val(),
				set_target_id = $( '.js-wpv-insert-view-form-target-set-existing-id' ).val(),
				results_helper_container = $( '.js-wpv-insert-form-workflow-help-box' ),
				results_helper_container_after = $( '.js-wpv-insert-form-workflow-help-box-after' );

			if ( display == 'both' ) {
				shortcode_to_insert = '[wpv-view name="' + form_name + '"' + override_values_string + extra_values_string + cache_values_string + ']';
				self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-view', shortcode_attribute_values, shortcode_to_insert );
				if (
					results_helper_container.length > 0
					&& results_helper_container.hasClass( 'js-wpv-insert-form-workflow-help-box-for-' + self.ps_view_id )
				) {
					results_helper_container.fadeOut( 'fast' );
				}
				if (
					results_helper_container_after.length > 0
					&& results_helper_container_after.hasClass( 'js-wpv-insert-form-workflow-help-box-for-after-' + self.ps_view_id )
				) {
					results_helper_container_after.show();
				}
			} else if ( display == 'results' ) {
				shortcode_to_insert = '[wpv-view name="' + form_name + '" view_display="layout"' + override_values_string + extra_values_string + cache_values_string + ']';
				self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-view', shortcode_attribute_values, shortcode_to_insert );
				if (
					results_helper_container.length > 0
					&& results_helper_container.hasClass( 'js-wpv-insert-form-workflow-help-box-for-' + self.ps_view_id )
				) {
					results_helper_container.fadeOut( 'fast' );
				}
				if (
					results_helper_container_after.length > 0
					&& results_helper_container_after.hasClass( 'js-wpv-insert-form-workflow-help-box-for-after-' + self.ps_view_id )
				) {
					results_helper_container_after.show();
				}
			} else if ( display == 'form' ) {
				if ( target == 'self' ) {
					shortcode_to_insert = '[wpv-form-view name="' + form_name + '" target_id="self"]';
					self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-form-view', shortcode_attribute_values, shortcode_to_insert );
					if ( results_helper_container.length > 0 ) {
						var results_shortcode = '<code>[wpv-view name="' + form_name + '" view_display="layout"]</code>';
						results_helper_container.find( '.js-wpv-insert-view-form-results-helper-name' ).html( form_name );
						results_helper_container.find( '.js-wpv-insert-view-form-results-helper-shortcode' ).html( results_shortcode );
						results_helper_container.addClass( 'js-wpv-insert-form-workflow-help-box-for-' + self.ps_view_id ).fadeIn( 'fast' );
					}
				} else {
					shortcode_to_insert = '[wpv-form-view name="' + form_name + '" target_id="' + set_target_id + '"' + override_values_string + extra_values_string + cache_values_string + ']';
					self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-form-view', shortcode_attribute_values, shortcode_to_insert );
				}
			}

		} else {
			shortcode_to_insert = '[wpv-view name="' + form_name + '"' + override_values_string + extra_values_string + cache_values_string + ']';
			self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-view', shortcode_attribute_values, shortcode_to_insert );

		}
	};

	self.wpv_insert_view_shortcode_to_editor_helper = function( shortcode_name, shortcode_attribute_values, shortcode_to_insert ) {
		self.dialog_insert_view.dialog('close');
		if ( self.shortcode_gui_insert == 'insert' ) {
			window.icl_editor.insert( shortcode_to_insert );
		}
		$( document ).trigger( 'js_event_wpv_shortcode_inserted', [ shortcode_name, '', shortcode_attribute_values, shortcode_to_insert ] );
	};


	/**
	 * Suggest for parametric search target
	 */

	$( document ).on( 'focus', '.js-wpv-insert-view-form-target-set-existing-title:not(.js-wpv-shortcode-gui-suggest-inited)', function() {
		var thiz = $( this );
		thiz
			.addClass( 'js-wpv-shortcode-gui-suggest-inited' )
			.suggest( wpv_shortcodes_gui_texts.ajaxurl + '&action=wpv_suggest_form_targets', {
				resultsClass: 'ac_results wpv-suggest-results',
				onSelect: function() {
					var t_value = this.value,
						t_split_point = t_value.lastIndexOf(' ['),
						t_title = t_value.substr( 0, t_split_point ),
						t_extra = t_value.substr( t_split_point ).split('#'),
						t_id = t_extra[1].replace(']', '');
					$( '.js-wpv-filter-form-help' ).hide();
					$('.js-wpv-insert-view-form-target-set-existing-title').val( t_title );
					t_edit_link = $('.js-wpv-insert-view-form-target-set-existing-link').data( 'editurl' );
					t_view_id = $('.js-wpv-insert-view-form-target-set-existing-link').data( 'viewid' );
					t_orig_id = $('.js-wpv-insert-view-form-target-set-existing-link').data('origid');
					$( '.js-wpv-insert-view-form-target-set-existing-link' ).attr( 'href', t_edit_link + t_id + '&action=edit&completeview=' + t_view_id + '&origid=' + t_orig_id );
					$( '.js-wpv-insert-view-form-target-set-existing-id' ).val( t_id ).trigger( 'change' );
					$( '.js-wpv-insert-view-form-target-set-actions' ).show();
				}
			});
	});

	/*
	 * Adjust the action button text copy based on the action to perform
	 */

	$( document ).on( 'change', '.js-wpv-insert-view-form-display', function() {
		var container = $( '#js-wpv-insert-view-parametric-search-container' ),
			display_container = $( '.js-wpv-insert-view-form-display-container', container ),
			display = $( '.js-wpv-insert-view-form-display:checked', container ).val(),
			target_container = $( '.js-wpv-insert-view-form-target-container', container ),
			target = $( '.js-wpv-insert-view-form-target:checked', container ).val(),
			set_target = $( '.js-wpv-insert-view-form-target-set:checked', container ).val(),
			set_target_id = $( '.js-wpv-insert-view-form-target-set-existing-id', container ).val(),
			results_helper_container = $( '.js-wpv-insert-form-workflow-help-box', container ),
			results_helper_container_after = $( '.js-wpv-insert-form-workflow-help-box-after', container );
		if ( display == 'form' ) {
			target_container.fadeIn();
		} else {
			target_container.fadeOut();
		}
		self.dialog_insert_view_locked_check();
	});

	/*
	 * Adjust the GUI when inserting just the form, based on the target options - target this or other page
	 */

	$( document ).on( 'change', '.js-wpv-insert-view-form-target', function() {
		var target = $( '.js-wpv-insert-view-form-target:checked' ).val(),
			set_target = $( '.js-wpv-insert-view-form-target-set:checked' ).val();
		if ( target == 'self' ) {
			$( '.js-wpv-insert-view-form-target-set-container' ).hide();
		} else if ( target == 'other' ) {
			$( '.js-wpv-insert-view-form-target-set-container' ).fadeIn( 'fast' );
		}
		self.dialog_insert_view_locked_check();
	});

	$( document ).on( 'click', '.js-wpv-insert-view-form-target-set-discard', function( e ) {
		e.preventDefault();
		self.dialog_insert_view_locked = false;
		$( '.js-wpv-insert-view-form-action' )
			.addClass( 'button-primary' )
			.removeClass( 'button-secondary' )
			.prop( 'disabled', false );
		$( '.js-wpv-insert-view-form-target-set-actions' ).hide();
	});

	$( document ).on( 'click', '.js-wpv-insert-view-form-target-set-existing-link', function() {
		self.dialog_insert_view_locked = false;
		$( '.js-wpv-insert-view-form-action' )
			.addClass( 'button-primary' )
			.removeClass( 'button-secondary' )
			.prop( 'disabled', false );
		$( '.js-wpv-insert-view-form-target-set-actions' ).hide();
	});

	/*
	 * Adjust the GUI when inserting just the form and targeting another page, based on the target options - target existing or new page
	 */

	$( document ).on( 'change', '.js-wpv-insert-view-form-target-set', function() {
		var set_target = $( '.js-wpv-insert-view-form-target-set:checked' ).val();
		if ( set_target == 'create' ) {
			$( '.js-wpv-insert-view-form-target-set-existing-extra' ).hide();
			$( '.js-wpv-insert-view-form-target-set-create-extra' ).fadeIn( 'fast' );
			$( '.js-wpv-insert-view-form-action' )
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
		} else if ( set_target == 'existing' ) {
			$( '.js-wpv-insert-view-form-target-set-create-extra' ).hide();
			$( '.js-wpv-insert-view-form-target-set-existing-extra' ).fadeIn( 'fast' );
			$( '.js-wpv-insert-view-form-action' )
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
			if ( $( '.js-wpv-insert-view-form-target-set-existing-id' ).val() != '' ) {
				$( '.js-wpv-insert-view-form-target-set-actions' ).show();
			}
		}
		self.dialog_insert_view_locked_check();
	});

	/*
	 * Adjust values when editing the target page title - clean data and mark this as unfinished
	 */

	$( document ).on('change input cut paste', '.js-wpv-insert-view-form-target-set-existing-title', function() {
		$( '.js-wpv-insert-view-form-target-set-actions' ).hide();
		$( '.js-wpv-insert-view-form-target-set-existing-link' ).attr( 'data-targetid', '' );
		$('.js-wpv-insert-view-form-target-set-existing-id')
			.val( '' )
			.trigger( 'manchange' );
	});

	/*
	 * Disable the insert button when doing any change in the existing title textfield
	 *
	 * We use a custom event 'manchange' as in "manual change"
	 */

	$( document ).on( 'manchange', '.js-wpv-insert-view-form-target-set-existing-id', function() {
		$( '.js-wpv-insert-view-form-action' )
			.removeClass( 'button-primary' )
			.addClass( 'button-secondary' )
			.prop( 'disabled', true );
		self.dialog_insert_view_locked_check();
	});

	/*
	 * Adjust GUI when creating a target page, based on the title value
	 */

	$( document ).on( 'change input cut paste', '.js-wpv-insert-view-form-target-set-create-title', function() {
		if ( $( '.js-wpv-insert-view-form-target-set-create-title' ).val() == '' ) {
			$( '.js-wpv-insert-view-form-target-set-create-action' )
				.prop( 'disabled', true )
				.addClass( 'button-secondary' )
				.removeClass( 'button-primary' );
		} else {
			$( '.js-wpv-insert-view-form-target-set-create-action' )
				.prop( 'disabled', false )
				.addClass( 'button-primary' )
				.removeClass( 'button-secondary' );
		}
	});

	/*
	 * AJAX action to create a new target page
	 */

	$( document ).on( 'click', '.js-wpv-insert-view-form-target-set-create-action', function() {
		var thiz = $( this ),
			thiz_existing_radio = $( '.js-wpv-insert-view-form-target-set[value="existing"]' ),
			spinnerContainer = $('<div class="wpv-spinner ajax-loader">').insertAfter( thiz ).show();
		data = {
			action: 'wpv_create_form_target_page',
			post_title: $( '.js-wpv-insert-view-form-target-set-create-title' ).val(),
			_wpnonce: thiz.data( 'nonce' )
		};
		$.ajax({
			url: wpv_shortcodes_gui_texts.ajaxurl,
			data: data,
			success: function( response ) {
				decoded_response = $.parseJSON( response );
				if ( decoded_response.result == 'error' ) {

				} else {
					$( '.js-wpv-insert-view-form-target-set-existing-title' ).val( decoded_response.page_title );
					$( '.js-wpv-insert-view-form-target-set-existing-id' ).val( decoded_response.page_id );
					t_edit_link = $('.js-wpv-insert-view-form-target-set-existing-link').data( 'editurl' );
					$('.js-wpv-insert-view-form-target-set-existing-link')
						.attr( 'href', t_edit_link + decoded_response.page_id + '&action=edit&completeview=' + self.ps_view_id + '&origid=' + self.ps_orig_id );
					thiz_existing_radio
						.prop( 'checked', true )
						.trigger( 'change' );
					$( '.js-wpv-insert-view-form-target-set-actions' ).show();
				}
			},
			error: function ( ajaxContext ) {

			},
			complete: function() {
				spinnerContainer.remove();
			}
		});
	});

	// Close the finished help boxes

	$( document ).on( 'click', '.js-wpv-insert-form-workflow-help-box-close', function( e ) {
		e.preventDefault();
		$( this ).closest( '.js-wpv-insert-form-workflow-help-box, .js-wpv-insert-form-workflow-help-box-after' ).hide();
	});
	
	// Toggle advanced settings on the dialog to insert a View
	
	$( document ).on( 'click', '.js-wpv-insert-views-shortcode-advanced-toggler', function( e ) {
		e.preventDefault();
		$( this )
			.find( 'i' )
				.toggleClass( 'fa-caret-down fa-caret-up' );
		$( '.js-wpv-insert-views-shortcode-advanced-wrapper' ).fadeToggle( 'fast' );
	});


	/**
	 * wpv_insert_popup_conditional
	 *
	 * @since 1.9
	 */

	self.wpv_insert_popup_conditional = function( shortcode, title, params, nonce, object ) {
		/**
		 * Build AJAX url
		 */

		var url = wpv_shortcodes_gui_texts.ajaxurl;
		url += '&_wpnonce=' + nonce;
		url += '&action=wpv_shortcode_gui_dialog_conditional_create';
		url += '&post_id=' + parseInt( object.post_id );

		self.dialog_insert_views_conditional.dialog('open').dialog({
			title:		title,
			height:		self.calculate_dialog_maxHeight(),
			width:		self.calculate_dialog_maxWidth(),
			position:	{
				my:			"center top+50",
				at:			"center top",
				of:			window,
				collision:	"none"
			}
		});

		self.dialog_insert_views_conditional.html( self.shortcodeDialogSpinnerContent );
		/**
		 * Do AJAX call
		 */
		$.ajax({
			url: url,
			success: function( data ) {
				$( 'body' ).addClass( 'modal-open' );
				self.dialog_insert_views_conditional.html( data ).dialog( 'open' );
				$( '.js-wpv-shortcode-gui-insert' )
					.addClass( 'button-primary' )
					.removeClass( 'button-secondary' )
					.prop( 'disabled', false );

				$('.js-wpv-conditional-shortcode-gui-tabs')
					.tabs()
					.addClass('ui-tabs-vertical ui-helper-clearfix')
					.removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
				$('.js-wpv-conditional-shortcode-gui-tabs, .js-wpv-conditional-shortcode-gui-tabs li').removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');


				self.wpv_conditional_editor = object.codemirror;
				self.wpv_conditional_object = object;
				if ( object.codemirror == '' ) {
					if ( typeof object.ed.canvas !== 'undefined' ) {
						self.wpv_conditional_text = object.ed.canvas.value.substring(object.ed.canvas.selectionStart, object.ed.canvas.selectionEnd);
					} else {
						self.wpv_conditional_text = object.ed.selection.getContent();

					}
				} else {
					self.wpv_conditional_text = WPV_Toolset.CodeMirror_instance[object.codemirror].getSelection();
				}
				self.wpv_conditional_close = object.close_tag;


				/**
				 *
				 */
				self.wpv_conditional_add_row($('#js-wpv-conditionals'));

			}
		});


	};

	$(document).on('click', '.js-wpv-views-conditional-add-term', function(e) {
		self.wpv_conditional_add_row($('#js-wpv-conditionals'));
	});

	/**
	 * bind type
	 */
	$( document ).on( 'click', '#js-wpv-views-conditional-shortcode-gui-dialog-container .js-wpv-shortcode-expression-switcher', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
			thiz_container = thiz.closest( '.js-wpv-shortcode-gui-attribute-wrapper-for-if' ),
			thiz_container_gui = $( '.js-wpv-conditionals-set-with-gui', thiz_container ),
			thiz_container_manual = $( '.js-wpv-conditionals-set-manual', thiz_container ),
			thiz_add_condition_button = $( '.js-wpv-views-conditional-add-term', thiz_container )
		if ( self.views_conditional_use_gui ) {
			thiz.fadeOut( 400 );
			thiz_add_condition_button.fadeOut( 400 );
			thiz_container_gui.fadeOut( 400, function() {
				self.views_conditional_use_gui = false;
				$('#wpv-conditional-custom-expressions')
					.val( self.wpv_conditional_create_if_attribute('multiline') )
					.data( 'edited', false );
				thiz.html( wpv_shortcodes_gui_texts.conditional_enter_conditions_gui ).fadeIn( 400 );
				thiz_container_manual.fadeIn( 400, function() {

				});
			});
		} else {
			/**
			 * check editor if was edited, ask user
			 */
			if ( $('#wpv-conditional-custom-expressions').data( 'edited' ) ) {
				if ( ! confirm( wpv_shortcodes_gui_texts.conditional_switch_alert ) ) {
					return;
				}
			}
			thiz.fadeOut( 400 );
			thiz_container_manual.fadeOut( 400, function() {
				self.views_conditional_use_gui = true;
				thiz.html( wpv_shortcodes_gui_texts.conditional_enter_conditions_manually ).fadeIn( 400 );
				thiz_add_condition_button.fadeIn( 400 );
				thiz_container_gui.fadeIn( 400, function() {

				});
			});
		}
	});

	/**
	 * add wpv-conditional-custom-expressions
	 */
	$(document).on('keyup', '#wpv-conditional-custom-expressions', function() {
		if ( !$(this).data('edited') ) {
			$(this).data('edited', true);
		}
	});


	self.wpv_conditional_add_row = function ( container ) {
		if ( 'unfinished' == typeof WPViews.wpv_conditional_data ) {
			return false;
		}
		html = '<tr class="js-wpv-views-condtional-item">';
		/**
		 * type
		 */
		html += '<td class="js-wpv-views-conditional-origin">';
		html += '<span class="js-wpv-views-condtional-type" style="display:inline-block;width:150px;"><select><option value="">' + WPViews.wpv_conditional_data.labels.select_choose + '</option>';
		$.each( WPViews.wpv_conditional_data.fields, function( key, field ) {
			if ( ! $.isEmptyObject( field.fields ) ) {
				html += '<option value="' + field.slug + '">' + field.label + '</option>';
			}
		});
		html += '</select></span>';
		/**
		 * field
		 */
		html += '<span class="js-wpv-views-condtional-field" style="display:inline-block;width:150px;"><select disabled="disabled">';
		html += '</select></td>';
		html += '</select></span>';
		html += '</td>';
		/**
		 * operator
		 */
		html += '<td class="js-wpv-views-condtional-operator">';
		html += '<select>';
		html += '<option value="eq">=</option>';
		html += '<option value="ne">!=</option>';
		html += '<option value="gt">&gt;</option>';
		html += '<option value="lt">&lt;</option>';
		html += '<option value="gte">&gt;=</option>';
		html += '<option value="lte">&lt;=</option>';
		html += '</select>';
		html += '</td>';
		html += '</select></td>';
		/**
		 * value
		 */
		html += '<td class="js-wpv-views-condtional-value">';
		html += '<input type="text">';
		html += '</td>';
		html += '<td class="js-wpv-views-condtional-connect">';
		html += '<select>';
		html += '<option value="AND">AND</option>';
		html += '<option value="OR">OR</option>';
		html += '</select>';
		html += '</td>';
		html += '</select></td>';
		/**
		 * action
		 */
		html += '<td>';
		html += '<i class="icon-remove fa fa-times wpv-views-condtional-remove js-wpv-views-condtional-remove"></i>';
		html += '</td></tr>';
		$('.js-wpv-views-conditional-body').append(html);
		/**
		 * remove operator for first row
		 */
		self.wpv_conditional_row_remove_trash_from_first();


		return false;
	}

	/**
	 * bind remove
	 */
	$(document).on('click', '.js-wpv-views-condtional-remove', function() {
		var row = $(this).closest('tr');
		$( '.js-wpv-views-condtional-remove', '#js-wpv-conditionals' ).prop( 'disabled', true );
		row.addClass( 'wpv-condition-deleted' );
		row.fadeOut( 400, function() {
			row.remove();
			self.wpv_conditional_row_remove_trash_from_first();
			$( '.js-wpv-views-condtional-remove', '#js-wpv-conditionals' ).prop( 'disabled', false );
		});
	});

	/**
	 * bind type
	 */
	$( document ).on( 'change', '.js-wpv-views-condtional-type select', function() {
		var wpv_type = $( ':selected', $( this ) ).val();
		if ( wpv_type === '' ) {
			$( '.js-wpv-views-condtional-field select', $( this ).closest( 'tr' ) ).html( '' ).prop( 'disabled', true );
			return;
		}
		var html = '';
		$.each( WPViews.wpv_conditional_data.fields[wpv_type].fields, function( key, field ) {
			html += '<option value="' + field.slug + '" ';
			html += 'data-field-type="' + field.type + '" ';
			html += 'data-view-type="' + wpv_type + '" ';
			html += '>' + field.label + '</option>';
		});
		$( '.js-wpv-views-condtional-field select', $( this ).closest('tr') ).html( html ).prop( 'disabled', false );
	});

	/**
	 * remove operator for first row
	 */
	self.wpv_conditional_row_remove_trash_from_first = function(container) {
		if ( $('.js-wpv-views-condtional-item').length == 1) {
			$('.js-wpv-views-condtional-remove').hide();
		} else {
			$('.js-wpv-views-condtional-remove').show();
		}
		$('.js-wpv-views-conditional-body .js-wpv-views-condtional-item:first-child .js-wpv-views-condtional-connect', container).html('&nbsp;');
	}

	$( document ).on( 'change input cut paste', '#wpv-conditional-extra-settings .js-wpv-add-item-settings-form-newname', function() {
		var thiz = $( this ),
			thiz_form = thiz.closest( 'form' ),
			thiz_button = thiz_form.find( '.js-wpv-add-item-settings-form-button' );
		$( '.js-wpv-cs-error, .js-wpv-cs-dup, .js-wpv-cs-ajaxfail', thiz_form ).hide();
		if ( thiz.val() == '' ) {
			thiz_button.prop( 'disabled', true );
		} else {
			thiz_button.prop( 'disabled', false );
		}
	});

	$( document ).on( 'click', '.js-wpv-add-item-settings-form-button', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
			shortcode_pattern,
			thiz_append,
			thiz_kind,
			parent_form = thiz.closest( '.js-wpv-add-item-settings-form' ),
			parent_container = thiz.closest( '.wpv-shortcode-gui-attribute-wrapper' ),
			newitem = $( '.js-wpv-add-item-settings-form-newname', parent_form ),
			spinnerContainer = $('<div class="wpv-spinner ajax-loader">'),
			data = {
				csaction: 'add',
				cstarget: newitem.val(),
				wpnonce: $( '#wpv_custom_conditional_extra_settings' ).val()
			};
		if ( thiz.hasClass( 'js-wpv-custom-inner-shortcodes-add' ) ) {
			shortcode_pattern = /^[a-z0-9\-\_]+$/;
			data.action = 'wpv_update_custom_inner_shortcodes';
			thiz_append = '<li class="js-' + newitem.val() + '-item"><span class="">[' + newitem.val() + ']</span></li>';
			thiz_kind = 'custom-shortcodes';
		} else if ( thiz.hasClass( 'js-wpv-custom-conditional-functions-add' ) ) {
			shortcode_pattern = /^[a-zA-Z0-9\:\-\_]+$/;
			data.action = 'wpv_update_custom_conditional_functions';
			thiz_append = '<li class="js-' + newitem.val() + '-item"><span class="">' + newitem.val() + '</span></li>';
			thiz_kind = 'custom-functions';
		} else {
			return;
		}
		$( '.js-wpv-cs-error, .js-wpv-cs-dup, .js-wpv-cs-ajaxfail', parent_form ).hide();
		if ( shortcode_pattern.test( newitem.val() ) == false ) {
			$( '.js-wpv-cs-error', parent_form ).show();
		} else if ( $( '.js-' + newitem.val() + '-item', parent_container ).length > 0 ) {
			$( '.js-wpv-cs-dup', parent_form ).show();
		} else {
			spinnerContainer.insertAfter( thiz ).show();
			thiz
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );

			$.ajax({
				async: false,
				dataType: "json",
				type: "POST",
				url: wpv_shortcodes_gui_texts.ajaxurl,
				data: data,
				success: function( response ) {
					if ( response.success ) {
						$( '.js-wpv-add-item-settings-list', parent_container )
							.append( thiz_append );
						$( document ).trigger( 'js_event_wpv_extra_conditional_registered', [ { kind: thiz_kind, value: newitem.val() } ] );
						newitem.val('');
					} else {
						$( '.js-wpv-cs-ajaxfail', parent_form ).show();
						console.log( "Error: AJAX returned ", response );
					}
				},
				error: function (ajaxContext) {
					$( '.js-wpv-cs-ajaxfail', parent_form ).show();
					console.log( "Error: ", ajaxContext.responseText );
				},
				complete: function() {
					spinnerContainer.remove();
				}
			});
		}
		return false;
	});

	$( document ).on( 'submit', '.js-wpv-add-item-settings-form' , function( e ) {
		e.preventDefault();
		var thiz = $( this );
		$( '.js-wpv-add-item-settings-form-button', thiz ).click();
		return false;
	});

	$( document ).on( 'js_event_wpv_extra_conditional_registered', function( event, data ) {
		var html = '',
			type_select = $( '.js-wpv-views-condtional-type select' );
		switch ( data.kind ) {
			case 'custom-shortcodes':
				if ( $.isEmptyObject( WPViews.wpv_conditional_data.fields['custom-shortcodes'].fields ) ) {
					WPViews.wpv_conditional_data.fields['custom-shortcodes'].fields = {};
				}
				WPViews.wpv_conditional_data.fields['custom-shortcodes'].fields[data.value] = {
					label: data.value,
					slug: '\'[' + data.value + ']\'',
					type: 'text'
				};
				break;
			case 'custom-functions':
				if ( $.isEmptyObject( WPViews.wpv_conditional_data.fields['custom-functions'].fields ) ) {
					WPViews.wpv_conditional_data.fields['custom-functions'].fields = {};
				}
				WPViews.wpv_conditional_data.fields['custom-functions'].fields[data.value] = {
					label: data.value,
					slug: data.value + '()',
					type: 'text'
				};
				break;
		}
		html += '<option value="">' + WPViews.wpv_conditional_data.labels.select_choose + '</option>';
		$.each( WPViews.wpv_conditional_data.fields, function( key, field ) {
			if ( ! $.isEmptyObject( field.fields ) ) {
				html += '<option value="' + field.slug + '">' + field.label + '</option>';
			}
		});
		type_select.html( html );
		type_select.trigger( 'change' );
	});

	//-----------------------------------------
	// Generic shortcodes API GUI
	//-----------------------------------------

	/**
	 * Insert a shortcode into an editor when there are no attributes to set.
	 *
	 * Used in Basic taxonomy shortcodes, for example.
	 *
	 * todo explain parameters
	 * @param shortcode_name
	 * @param shortcode_to_insert
	 *
	 * @since 1.12
	 */

	self.insert_shortcode_with_no_attributes = function( shortcode_name, shortcode_to_insert ) {
		if (
			_.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set )
			&& self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog( "isOpen" )
		) {
			self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog('close');
		}
		if ( self.shortcode_gui_insert == 'insert' ) {
			window.icl_editor.insert( shortcode_to_insert );
		}
		$( document ).trigger( 'js_event_wpv_shortcode_inserted', [ shortcode_name, '', {}, shortcode_to_insert ] );
	};

	/**
	 * Display a dialog for inserting a specific Views shortcode.
	 *
	 * todo explain parameters
	 * @param shortcode
	 * @param {string} title Dialog title.
	 * @param params
	 * @param nonce
	 * @param object
	 *
	 * @since 1.9
	 */
	self.wpv_insert_popup = function( shortcode, title, params, nonce, object ) {

		//
		// Build AJAX url for displaying the dialog
		//
		var url = wpv_shortcodes_gui_texts.ajaxurl,
			url_extra_data = '',
			data_for_events = {};

		url += '&_wpnonce=' + nonce;
		url += '&action=wpv_shortcode_gui_dialog_create';
		url += '&shortcode=' + shortcode;
		url += '&post_id=' + parseInt( $( object ).data( 'post-id' ) );

		url_extra_data = self.filter_dialog_ajax_data( shortcode );

		url += url_extra_data;

		data_for_events.shortcode = shortcode;
		data_for_events.title = title;
		data_for_events.params = params;
		data_for_events.nonce = nonce;
		data_for_events.object = object;
        data_for_events.dialog = this;
		$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_triggered', [ data_for_events ] );
		
		// Show the "empty" dialog with a spinner while loading dialog content
		self.dialog_insert_shortcode.dialog('open').dialog({
			title:		title,
			maxHeight:	self.calculate_dialog_maxHeight(),
			maxWidth:	self.calculate_dialog_maxWidth(),
			position:	{
				my:			"center top+50",
				at:			"center top",
				of:			window,
				collision:	"none"
			}
		});

		self.manage_dialog_button_labels();

		self.dialog_insert_shortcode.html( self.shortcodeDialogSpinnerContent );

		//
		// Do AJAX call
		//
		$.ajax({
			url: url,
			success: function( data ) {
				$( 'body' ).addClass( 'modal-open' );
				/**
				 * Load dialog data
				 */
				self.dialog_insert_shortcode.html(data);
				$( '.js-wpv-shortcode-gui-insert' )
					.addClass( 'button-primary' )
					.removeClass( 'button-secondary' )
					.prop( 'disabled', false );

				/**
				 * Init dialog tabs
				 */
				$('.js-wpv-shortcode-gui-tabs')
					.tabs({
						beforeActivate: function( event, ui ) {
							var valid = self.validate_shortcode_attributes( $( '#js-wpv-shortcode-gui-dialog-container' ), ui.oldPanel, $( '#js-wpv-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) );
							if ( ! valid ) {
								event.preventDefault();
								ui.oldTab.focus().addClass( 'wpv-shortcode-gui-tabs-incomplete' );
								setTimeout( function() {
									ui.oldTab.removeClass( 'wpv-shortcode-gui-tabs-incomplete' );
								}, 1000 );
							}
						}
					})
					.addClass('ui-tabs-vertical ui-helper-clearfix')
					.removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
				$('#js-wpv-shortcode-gui-dialog-tabs ul, #js-wpv-shortcode-gui-dialog-tabs li').removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');

				/**
				 * After open dialog
				 */
				self.after_open_dialog(shortcode, title, params, nonce, object);

				/**
				 * Custom combo management
				 */
				self.custom_combo_management();

				$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_opened', [ data_for_events ] );
			}
		});
	};

	/**
	 * Custom combo management
	 */
	self.custom_combo_management = function () {
		$( '.js-wpv-shortcode-gui-attribute-custom-combo').each( function() {
			var combo_parent = $( this ).closest( '.js-wpv-shortcode-gui-attribute-wrapper' ),
				combo_target = $( '.js-wpv-shortcode-gui-attribute-custom-combo-target', combo_parent );
			if ( $( '[value=custom-combo]:checked', combo_parent ).length) {
				$combo_target.show();
			}
			$( '[type=radio]', combo_parent ).on( 'change', function() {
				var thiz_radio = $( this );
				if (
					thiz_radio.is( ':checked' )
					&& 'custom-combo' == thiz_radio.val()
				) {
					combo_target.slideDown( 'fast' );
				} else {
					combo_target.slideUp( 'fast' );
				}
			});
		});
	}

	/**
	 * filter_dialog_ajax_data
	 *
	 * Filter the empty extra string added to the request to create the dialog GUI, so we can pass additional parameters for some shortcodes.
	 *
	 * @param shortcode The shortcode to which the dialog is being created.
	 *
	 * @return ajax_extra_data
	 *
	 * @since 1.9
	 */

	self.filter_dialog_ajax_data = function( shortcode ) {
		var ajax_extra_data = '';
		switch( shortcode ) {
			case 'wpv-post-body':
				// Check for excluded content templates list via the filter.
				var excluded_cts = [];
				excluded_cts = Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-gui-wpv_post_body-exclude-content-template', excluded_cts );

				// Prepare a form array of all excluded CT IDs, to transmit via URL
				if( Array.isArray( excluded_cts ) && excluded_cts.length > 0 ) {
					for( var i = 0; i < excluded_cts.length; i++ ) {
						ajax_extra_data += '&wpv_suggest_wpv_post_body_view_template_exclude[]=' + excluded_cts[i];
					}
				}

				break;
		}
		return ajax_extra_data;
	};


	/**
	 * after_open_dialog
	 *
	 * @since 1.9
	 */
	self.after_open_dialog = function( shortcode, title, params, nonce, object ) {
		self.manage_fixed_initial_params( params );
		self.manage_special_cases( shortcode );
		self.manage_suggest_cache();
	};

	/**
	 * manage_dialog_button_labels
	 *
	 * Adjusts the dialog button labels for usage on Fields and Views or Loop Wizard scenarios.
	 *
	 * @since 1.9
	 */

	self.manage_dialog_button_labels = function() {
		switch ( self.shortcode_gui_insert ) {
			case 'save':
				$( '.js-wpv-shortcode-gui-button-close .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_cancel );
				$( '.js-wpv-shortcode-gui-button-insert .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_save_settings );
				break;
			case 'create':
				$( '.js-wpv-shortcode-gui-button-close .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_cancel );
				$( '.js-wpv-shortcode-gui-button-insert .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_create_shortcode );
				break;
			case 'insert':
			default:
				$( '.js-wpv-shortcode-gui-button-close .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_close );
				$( '.js-wpv-shortcode-gui-button-insert .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_insert_shortcode );
				break;
		}
	};

	/**
	 * manage_fixed_initial_params
	 *
	 * @since 1.9
	 */

	self.manage_fixed_initial_params = function( params ) {
		for ( var item in params ) {
			$( '.wpv-dialog' ).prepend( '<span class="wpv-shortcode-gui-attribute-wrapper js-wpv-shortcode-gui-attribute-wrapper" data-attribute="' + item + '" data-type="param"><input type="hidden" name="' + item + '" value="' + params[ item ].value + '" disabled="disabled" /></span>' );
		}
	};

	/**
	 * manage_special_cases
	 *
	 * @since 1.9
	 */

	self.manage_special_cases = function( shortcode ) {
		switch ( shortcode ) {
			case 'wpv-post-author':
				self.manage_wpv_post_author_format_show_relation();
				break;
			case 'wpv-post-taxonomy':
				self.manage_wpv_post_taxonomy_format_show_relation();
				break;
			case 'wpv-post-featured-image':
				self.manage_wpv_post_featured_image_output_show_class();
				self.manage_wpv_post_featured_image_resize_show_relation();
				self.manage_wpv_post_featured_image_crop_show_relation();
				break;
		}
	};

	/**
	 * manage_suggest_cache
	 *
	 * Populate suggest fields from cache if available
	 *
	 * @since 1.9
	 */

	self.manage_suggest_cache = function() {
		$( '.js-wpv-shortcode-gui-suggest' ).each( function() {
			var thiz_inner = $( this ),
				action_inner = '';
			if ( thiz_inner.data('action') != '' ) {
				action_inner = thiz_inner.data('action');
				if ( self.suggest_cache.hasOwnProperty( action_inner ) ) {
					thiz_inner
						.val( self.suggest_cache[action_inner] )
						.trigger( 'change' );
				}
			}
		});
	};

	/**
	 * Init suggest on suggest attributes
	 *
	 * @since 1.9
	 */

	$( document ).on( 'focus', '.js-wpv-shortcode-gui-suggest:not(.js-wpv-shortcode-gui-suggest-inited)', function() {
		var thiz = $( this ),
			action = '';
		if ( thiz.data('action') != '' ) {
			action = thiz.data('action');
			ajax_extra_data = self.filter_suggest_ajax_data( action );
			thiz
				.addClass( 'js-wpv-shortcode-gui-suggest-inited' )
				.suggest( wpv_shortcodes_gui_texts.ajaxurl + '&action=' + action + ajax_extra_data, {
					resultsClass: 'ac_results wpv-suggest-results',
					onSelect: function() {
						self.suggest_cache[action] = this.value;
					}
				});
		}
	});

	/**
	 * filter_suggest_ajax_data
	 *
	 * Filter the empty extra string added to the suggest request, so we can pass additional parameters for some shortcodes.
	 *
	 * @param action The suggest action to perform.
	 *
	 * @return ajax_extra_data
	 *
	 * @since 1.9
	 */

	self.filter_suggest_ajax_data = function( action ) {
		var ajax_extra_data = '';
		switch( action ) {
			case 'wpv_suggest_wpv_post_body_view_template':
				if (
					typeof WPViews.ct_edit_screen != 'undefined'
					&& typeof WPViews.ct_edit_screen.ct_data != 'undefined'
					&& typeof WPViews.ct_edit_screen.ct_data.id != 'undefined'
				) {
					ajax_extra_data = '&wpv_suggest_wpv_post_body_view_template_exclude=' + WPViews.ct_edit_screen.ct_data.id;
				}
				break;
		}
		return ajax_extra_data;
	};

	/**
	 * Manage item selector GUI
	 *
	 * @since 1.9
	 */

	$( document ).on( 'change', 'input.js-wpv-shortcode-gui-item-selector', function() {
		var thiz = $( this ),
			checked = thiz.val();
		$('.js-wpv-shortcode-gui-item-selector-has-related').each( function() {
			var thiz_inner = $( this );
			if ( $( 'input.js-wpv-shortcode-gui-item-selector:checked', thiz_inner ).val() == checked ) {
				$( '.js-wpv-shortcode-gui-item-selector-is-related', thiz_inner ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-item-selector-is-related', thiz_inner ).slideUp( 'fast' );
			}
		});
	});

	/**
	 * Manage placeholders: should be removed when focusing on a textfield, added back on blur
	 *
	 * @since 1.9
	 */

	$( document )
		.on( 'focus', '.js-wpv-shortcode-gui-attribute-has-placeholder, .js-wpv-has-placeholder', function() {
			var thiz = $( this );
			thiz.attr( 'placeholder', '' );
		})
		.on( 'blur', '.js-wpv-shortcode-gui-attribute-has-placeholder, .js-wpv-has-placeholder', function() {
			var thiz = $( this );
			if ( thiz.data( 'placeholder' ) ) {
				thiz.attr( 'placeholder', thiz.data( 'placeholder' ) );
			}
		});

	/**
	 * validate_shortcode_attributes
	 *
	 * Validate method
	 *
	 * @since 1.9
	 */

	self.validate_shortcode_attributes = function( container, evaluate_container, error_container ) {
		self.clear_validate_messages( container );
		var valid = true;
		valid = self.manage_required_attributes( evaluate_container, error_container );
		evaluate_container.find( 'input:text' ).each( function() {
			var thiz = $( this ),
				thiz_val = thiz.val(),
				thiz_type = thiz.data( 'type' ),
				thiz_message = '',
				thiz_valid = true;
			if ( ! thiz.hasClass( 'js-toolset-shortcode-gui-invalid-attr' ) ) {
				switch ( thiz_type ) {
					case 'number':
						if (
							self.numeric_natural_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_number_invalid;
						}
						break;
					case 'numberextended':
						if (
							self.numeric_natural_extended_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_number_invalid;
						}
						break;
					case 'numberlist':
						if (
							self.numeric_natural_list_pattern.test( thiz_val.replace(/\s+/g, '') ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_numberlist_invalid;
						}
						break;
					case 'year':
						if (
							self.year_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_year_invalid;
						}
						break;
					case 'month':
						if (
							self.month_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_month_invalid;
						}
						break;
					case 'week':
						if (
							self.week_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_week_invalid;
						}
						break;
					case 'day':
						if (
							self.day_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_day_invalid;
						}
						break;
					case 'hour':
						if (
							self.hour_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_hour_invalid;
						}
						break;
					case 'minute':
						if (
							self.minute_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_minute_invalid;
						}
						break;
					case 'second':
						if (
							self.second_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_second_invalid;
						}
						break;
					case 'dayofyear':
						if (
							self.dayofyear_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_dayofyear_invalid;
						}
						break;
					case 'dayofweek':
						if (
							self.dayofweek_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_dayofweek_invalid;
						}
						break;
					case 'url':
						if (
							self.url_patern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_url_invalid;
						}
						break;
				}
				if ( ! thiz_valid ) {
					valid = false;
					error_container
						.wpvToolsetMessage({
							text: thiz_message,
							type: 'error',
							inline: false,
							stay: true
						});
					// Hack to allow more than one error message per filter
					error_container
						.data( 'message-box', null )
						.data( 'has_message', false );
				}
			}
		});
		// Special case: item selector tab
		if (
			$( '.js-wpv-shortcode-gui-item-selector:checked', evaluate_container ).length > 0
			&& 'item_id' == $( '.js-wpv-shortcode-gui-item-selector:checked', evaluate_container ).val()
		) {
			var item_selection = $( '[name=specific_item_id]', evaluate_container ),
				item_selection_id = item_selection.val(),
				item_selection_valid = true,
				item_selection_message = '';
			if ( '' == item_selection_id ) {
				item_selection_valid = false;
				item_selection.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				item_selection_message = wpv_shortcodes_gui_texts.attr_empty;
			} else if ( self.numeric_natural_pattern.test( item_selection_id ) == false ) {
				item_selection_valid = false;
				item_selection.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				item_selection_message = wpv_shortcodes_gui_texts.attr_number_invalid;
			}
			if ( ! item_selection_valid ) {
				valid = false;
				error_container
					.wpvToolsetMessage({
						text: item_selection_message,
						type: 'error',
						inline: false,
						stay: true
					});
				// Hack to allow more than one error message per filter
				error_container
					.data( 'message-box', null )
					.data( 'has_message', false );
			}
		}
		return valid;
	};

	$( document ).on( 'change keyup input cut paste', '.js-wpv-shortcode-gui-dialog-container input, .js-wpv-shortcode-gui-dialog-container select', function() {
		var thiz = $( this );
		thiz.removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
		thiz
			.closest( '.js-wpv-shortcode-gui-dialog-container' )
			.find('.toolset-alert-error').not( '.js-wpv-permanent-alert-error' )
			.each( function() {
				$( this ).remove();
			});
	});

	self.clear_validate_messages = function( container ) {
		container
			.find('.toolset-alert-error').not( '.js-wpv-permanent-alert-error' )
			.each( function() {
				$( this ).remove();
			});
		container
			.find( '.js-toolset-shortcode-gui-invalid-attr' )
			.removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
	};

	/**
	 * manage_required_attributes
	 *
	 * @since 1.9
	 */

	self.manage_required_attributes = function( evaluate_container, error_container ) {
		var valid = true,
			error_container = $( '#js-wpv-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' );
		evaluate_container.find( '.js-shortcode-gui-field.js-wpv-shortcode-gui-required' ).each( function() {
			var thiz = $( this ),
				thiz_valid = true,
				thiz_parent = thiz.closest('.js-wpv-shortcode-gui-attribute-custom-combo');
			if ( thiz_parent.length ) {
				if (
					$( '[value=custom-combo]:checked', thiz_parent ).length
					&& thiz.val() == ''
				) {
					thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
					thiz_valid = false;
				}
			} else {
				if ( thiz.val() == '' ) {
					thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
					thiz_valid = false;
				}
			}
			if ( ! thiz_valid ) {
				valid = false;
				error_container
					.wpvToolsetMessage({
						text: wpv_shortcodes_gui_texts.attr_empty,
						type: 'error',
						inline: false,
						stay: true
					});
				// Hack to allow more than one error message per filter
				error_container
					.data( 'message-box', null )
					.data( 'has_message', false );
			}
		});
		return valid;
	};

	/**
	 * wpv_insert_shortcode
	 *
	 * Insert shortcode to active editor
	 *
	 * @since 1.9
	 */

	self.wpv_insert_shortcode = function() {

		var shortcode_name = $('.js-wpv-shortcode-gui-shortcode-name').val(),
			shortcode_attribute_key,
			shortcode_attribute_value,
			shortcode_attribute_default_value,
			shortcode_attribute_string = '',
			shortcode_attribute_values = {},
			shortcode_content = '',
			shortcode_to_insert = '',
			shortcode_valid = self.validate_shortcode_attributes( $( '#js-wpv-shortcode-gui-dialog-container' ), $( '#js-wpv-shortcode-gui-dialog-container' ), $( '#js-wpv-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) );
		if ( ! shortcode_valid ) {
			return;
		}
		$( '.js-wpv-shortcode-gui-attribute-wrapper', '#js-wpv-shortcode-gui-dialog-container' ).each( function() {
			var thiz_attribute_wrapper = $( this ),
				shortcode_attribute_key = thiz_attribute_wrapper.data('attribute');
			switch ( thiz_attribute_wrapper.data('type') ) {
				case 'post':
				case 'user':
					shortcode_attribute_value = $( '.js-wpv-shortcode-gui-item-selector:checked', thiz_attribute_wrapper ).val();
					switch( shortcode_attribute_value ) {
						case 'current':
							shortcode_attribute_value = false;
							break;
						case 'parent':
							if ( shortcode_attribute_value ) {
								shortcode_attribute_value = '$' + shortcode_attribute_value;
							}
							break;
						case 'related':
							shortcode_attribute_value = $( '[name=related_post]:checked', thiz_attribute_wrapper ).val();
							if ( shortcode_attribute_value ) {
								shortcode_attribute_value = '$' + shortcode_attribute_value;
							}
							break;
						case 'item_id':
							shortcode_attribute_value = $( '[name=specific_item_id]', thiz_attribute_wrapper ).val();
						default:
					}
					break;
				case 'select':
					shortcode_attribute_value = $('option:checked', thiz_attribute_wrapper ).val();
					break;
				case 'radio':
				case 'radiohtml':
					shortcode_attribute_value = $('input:checked', thiz_attribute_wrapper ).val();
					if ( 'custom-combo' == shortcode_attribute_value ) {
						shortcode_attribute_value = $('.js-wpv-shortcode-gui-attribute-custom-combo-target', $('input:checked', thiz_attribute_wrapper ).closest('.js-wpv-shortcode-gui-attribute-custom-combo')).val();
					}
					break;
				case 'checkbox':
					shortcode_attribute_value = $('input:checked', thiz_attribute_wrapper ).val();
					break;
				default:
					shortcode_attribute_value = $('input', thiz_attribute_wrapper ).val();
			}

			shortcode_attribute_default_value = thiz_attribute_wrapper.data('default');
			/**
			 * Fix true/false from data attribute for shortcode_attribute_default_value
			 */
			if ( 'boolean' == typeof shortcode_attribute_default_value ) {
				shortcode_attribute_default_value = shortcode_attribute_default_value ? 'true' :'false';
			}
			/**
			 * Filter value
			 */
			shortcode_attribute_value = self.filter_computed_attribute_value( shortcode_name, shortcode_attribute_key, shortcode_attribute_value );
			/**
			 * Add to the shortcode_attribute_values object
			 */
			if (
				shortcode_attribute_value
				&& shortcode_attribute_value != shortcode_attribute_default_value
			) {
				shortcode_attribute_values[shortcode_attribute_key] = shortcode_attribute_value;
			}
		});
		// Filter pairs key => value
		shortcode_attribute_values = self.filter_computed_attribute_pairs( shortcode_name, shortcode_attribute_values );
		// Compose the shortcode_attribute_string string
		_.each( shortcode_attribute_values, function( value, key ) {
			if ( value ) {
				shortcode_attribute_string += " " + key + "='" + value + "'";
			}
		});
		shortcode_to_insert = '[' + shortcode_name + shortcode_attribute_string + ']';
		/**
		 * Shortcodes with content
		 */
		if ( $( '.js-wpv-shortcode-gui-content' ).length > 0 ) {
			shortcode_content = $( '.js-wpv-shortcode-gui-content' ).val();
			/**
			 * Filter shortcode content
			 */
			shortcode_content = self.filter_computed_content( shortcode_name, shortcode_content, shortcode_attribute_values );
			shortcode_to_insert += shortcode_content;
			shortcode_to_insert += '[/' + shortcode_name + ']';
		}
		/**
		 * Close, insert if needed and fire custom event
		 */
		self.dialog_insert_shortcode.dialog('close');
		if ( self.shortcode_gui_insert == 'insert' ) {
			window.icl_editor.insert( shortcode_to_insert );
		}
		$( document ).trigger( 'js_event_wpv_shortcode_inserted', [ shortcode_name, shortcode_content, shortcode_attribute_values, shortcode_to_insert ] );

	};

	$( document ).on( 'js_event_wpv_shortcode_inserted', function() {
		self.shortcode_gui_insert_count = self.shortcode_gui_insert_count + 1;
	});

	/**
	 * wpv_insert_view_conditional_shortcode
	 *
	 * Insert Views conditional shortcode to active editor
	 *
	 * @since 1.10
	 */

	self.wpv_insert_view_conditional_shortcode = function() {
		var shortcode_name = $('.js-wpv-views-conditional-shortcode-gui-dialog-name').val(),
			shortcode_attribute_key,
			shortcode_attribute_value,
			shortcode_attribute_default_value,
			shortcode_attribute_string = '',
			shortcode_attribute_values = {},
			shortcode_content = '',
			shortcode_to_insert = '',
			shortcode_valid = self.validate_shortcode_attributes( $( '#js-wpv-views-conditional-shortcode-gui-dialog-container' ), $( '#js-wpv-views-conditional-shortcode-gui-dialog-container' ), $( '#js-wpv-views-conditional-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) );
		if ( ! shortcode_valid ) {
			return;
		}
		$( '.js-wpv-shortcode-gui-attribute-wrapper', '#js-wpv-views-conditional-shortcode-gui-dialog-container' ).each( function() {
			var thiz_attribute_wrapper = $( this ),
				shortcode_attribute_key = thiz_attribute_wrapper.data('attribute');
			switch ( thiz_attribute_wrapper.data('type') ) {
				case 'radio':
					shortcode_attribute_value = $('input:checked', thiz_attribute_wrapper ).val();
					if ( 'custom-combo' == shortcode_attribute_value ) {
						shortcode_attribute_value = $('.js-wpv-shortcode-gui-attribute-custom-combo-target', $('input:checked', thiz_attribute_wrapper ).closest('.js-wpv-shortcode-gui-attribute-custom-combo')).val();
					}
					break;
				default:
					shortcode_attribute_value = $('input', thiz_attribute_wrapper ).val();
			}

			shortcode_attribute_default_value = thiz_attribute_wrapper.data('default');
			/**
			 * Fix true/false from data attribute for shortcode_attribute_default_value
			 */
			if ( 'boolean' == typeof shortcode_attribute_default_value ) {
				shortcode_attribute_default_value = shortcode_attribute_default_value ? 'true' :'false';
			}
			/**
			 * Filter value
			 */
			shortcode_attribute_value = self.filter_computed_attribute_value( shortcode_name, shortcode_attribute_key, shortcode_attribute_value );
			/**
			 * Add to the shortcode_attribute_string string
			 */
			if (
				shortcode_attribute_value
				&& shortcode_attribute_value != shortcode_attribute_default_value
			) {
				shortcode_attribute_string += ' ' + shortcode_attribute_key + '="' + shortcode_attribute_value + '"';
				shortcode_attribute_values[shortcode_attribute_key] = shortcode_attribute_value;
			}
		});

		shortcode_to_insert = '[' + shortcode_name + shortcode_attribute_string + ']';
		/**
		 * Shortcodes with content
		 */
		if ( $( '.js-wpv-shortcode-conditional-gui-content' ).length > 0 ) {
			shortcode_content = $( '.js-wpv-shortcode-conditional-gui-content' ).val();
			/**
			 * Filter shortcode content
			 */
			shortcode_content = self.filter_computed_content( shortcode_name, shortcode_content, shortcode_attribute_values );
			var selected_text = self.wpv_conditional_text;
			if ( self.wpv_conditional_close ) {
				shortcode_to_insert += selected_text;
				shortcode_to_insert += '[/' + shortcode_name + ']';
				self.views_conditional_qtags_opened = false;
			} else {
				self.views_conditional_qtags_opened = true;
			}
		}
		/**
		 * Close, insert if needed and fire custom event
		 */
		self.dialog_insert_views_conditional.dialog('close');
		if ( self.shortcode_gui_insert == 'insert' ) {
			window.icl_editor.insert( shortcode_to_insert );
		}
		$( document ).trigger( 'js_event_wpv_shortcode_inserted', [ shortcode_name, shortcode_content, shortcode_attribute_values, shortcode_to_insert ] );
	};

	//--------------------------------
	// Special cases
	//--------------------------------

	/**
	 * wpv-post-author management
	 * Handle the change in format that shows/hides the show attribute
	 *
	 * @since 1.9
	 */

	$( document ).on( 'change', '#wpv-post-author-format .js-shortcode-gui-field', function() {
		self.manage_wpv_post_author_format_show_relation();
	});

	self.manage_wpv_post_author_format_show_relation = function() {
		if ( $( '#wpv-post-author-format' ).length ) {
			if ( 'meta' == $( '.js-shortcode-gui-field:checked', '#wpv-post-author-format' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-meta', '#wpv-post-author-display-options' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-meta', '#wpv-post-author-display-options' ).hide();
			}
		}
	};

	/**
	 * wpv-post-taxonomy management
	 * Handle the change in format that shows/hides the show attribute
	 *
	 * @since 1.9
	 */

	$( document ).on( 'change', '#wpv-post-taxonomy-format .js-shortcode-gui-field', function() {
		self.manage_wpv_post_taxonomy_format_show_relation();
	});

	self.manage_wpv_post_taxonomy_format_show_relation = function() {
		if ( $( '#wpv-post-taxonomy-format' ).length ) {
			if ( 'link' == $( '.js-shortcode-gui-field:checked', '#wpv-post-taxonomy-format' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-show', '#wpv-post-taxonomy-display-options' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-show', '#wpv-post-taxonomy-display-options' ).slideUp( 'fast' );
			}
		}
	};

	/**
	 * wpv-post-featured-image management
	 * Handle the change in output that shows/hides the class attribute
	 *
	 * @since 1.9
	 */

	$( document ).on( 'change', '#wpv-post-featured-image-output.js-shortcode-gui-field', function() {
		self.manage_wpv_post_featured_image_output_show_class();
	});

	self.manage_wpv_post_featured_image_output_show_class = function() {
		if ( $( '#wpv-post-featured-image-output' ).length ) {
			if ( 'img' == $( '#wpv-post-featured-image-output.js-shortcode-gui-field' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-class', '#wpv-post-featured-image-display-options' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-class', '#wpv-post-featured-image-display-options' ).slideUp( 'fast' );
			}
		}
	};

	/**
	 * wpv-post-featured-image management
	 * Handle the change in UI to show/hide attributes for custom image resizing and cropping
	 *
	 * @since 2.2
	 */
	$( document ).on( 'change', '#wpv-post-featured-image-size.js-shortcode-gui-field', function() {
		self.manage_wpv_post_featured_image_resize_show_relation();
	});

	self.manage_wpv_post_featured_image_resize_show_relation = function() {
		if( 'custom' == $( '#wpv-post-featured-image-size.js-shortcode-gui-field' ).val() ) {
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-width' ).slideDown( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-height' ).slideDown( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop' ).slideDown( 'fast' );

			self.manage_wpv_post_featured_image_crop_show_relation();
		} else {
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-width' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-height' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_horizontal' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_vertical' ).slideUp( 'fast' );
		}
	};

	/**
	 * wpv-post-featured-image management
	 * Handle the change in UI to show/hide attributes for crop positions
	 *
	 * @since 2.2
	 */
	$( document ).on( 'change', '#wpv-post-featured-image-crop .js-shortcode-gui-field', function() {
		self.manage_wpv_post_featured_image_crop_show_relation();
	});

	self.manage_wpv_post_featured_image_crop_show_relation = function() {
		if ( $( '#wpv-post-featured-image-crop' ).length ) {
			if( 'true' == $( '.js-shortcode-gui-field:checked', '#wpv-post-featured-image-crop' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_horizontal' ).slideDown( 'fast' );
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_vertical' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_horizontal' ).slideUp( 'fast' );
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_vertical' ).slideUp( 'fast' );
			}
		}
	};


	/**
	 * filter_computed_attribute_value
	 *
	 * @since 1.9
	 */

	self.filter_computed_attribute_value = function( shortcode, attribute, value ) {
		switch ( shortcode ) {
			case 'wpv-post-author':
				if (
					'meta' == attribute
					&& 'meta' != $( '.js-shortcode-gui-field:checked', '#wpv-post-author-format' ).val()
				) {
					value = false;
				}
				break;
			case 'wpv-post-taxonomy':
				if (
					'show' == attribute
					&& 'link' != $( '.js-shortcode-gui-field:checked', '#wpv-post-taxonomy-format' ).val()
				) {
					value = false;
				}
				break;
			case 'wpv-post-featured-image':
				if (
					'class' == attribute
					&& 'img' != $( '#wpv-post-featured-image-output.js-shortcode-gui-field' ).val()
				) {
					value = false;
				}
				break;
			case 'wpv-conditional':
				switch( attribute ) {
					case 'if':
						if ( self.views_conditional_use_gui ) {
							value = self.wpv_conditional_create_if_attribute( 'singleline' );
						} else {
							value = $('#wpv-conditional-custom-expressions').val();
						}
						if ( value == '' ) {
							value = "('1' eq '1')";
						}
						break;
					/*
					 case 'custom-expressions':
					 value = false;
					 */
				}
				break;
		}
		return value;
	};

	self.filter_computed_attribute_pairs = function( shortcode_name, shortcode_attribute_values ) {
		if ( shortcode_name in self.shortcode_gui_computed_attribute_pairs_filters ) {
			var filter_callback_func = self.shortcode_gui_computed_attribute_pairs_filters[ shortcode_name ];
			if ( typeof filter_callback_func == "function" ) {
				shortcode_attribute_values = filter_callback_func( shortcode_attribute_values );
			}
		}
		return shortcode_attribute_values;
	};

	/**
	 * wpv_conditional_create_if_attribute
	 *
	 * @since 1.9
	 */
	self.wpv_conditional_create_if_attribute = function( mode ) {
		var value = '';
		$('.js-wpv-views-condtional-item').each( function() {
			var tr = $(this);
			if ( $('.js-wpv-views-condtional-field :selected', tr).val() ) {
				if ( value ) {
					if ( 'multiline' == mode ) {
						value += "\n";
					}
					value += ' '+$('.js-wpv-views-condtional-connect :checked', tr).val()+' ';
					if ( 'multiline' == mode ) {
						value += "\n";
					}
				}
				value += '( ';
				value += $('.js-wpv-views-condtional-field :selected', tr).val();
				value += ' ';
				value += $('.js-wpv-views-condtional-operator :selected', tr).val();
				value += ' \'';
				value += $('.js-wpv-views-condtional-value input', tr).val();
				value += '\' ';
				value += ')';
			}
		});
		return value;
	}

	/**
	 * filter_computed_content
	 *
	 * @since 1.9
	 */

	self.filter_computed_content = function( shortcode, content, values ) {
		switch ( shortcode ) {
			case 'wpv-for-each':
				if ( values.hasOwnProperty( 'field' ) ) {
					content = '[wpv-post-field name="' + values.field + '"]';
				}
				break;
		}
		return content;
	};

	/**
	 * load_post_field_section_on_demand
	 *
	 * Load the Post field section on the shortcodes GUI on demand
	 * Used to load non-Types custom fields only when needed
	 *
	 * @since 1.10
	 */

	self.load_post_field_section_on_demand = function( event, object ) {
		event.stopPropagation();
		var thiz = $( object );
		if ( self.post_field_section ) {
			var thiz_group_list = thiz.closest( '.js-wpv-shortcode-gui-group-list' );
			thiz_group_list
				.fadeOut( 'fast', function() {
					thiz_group_list
						.html( response.data.section )
						.fadeIn( 'fast' );
				});
		} else {
			var url = wpv_shortcodes_gui_texts.ajaxurl + '&action=wpv_shortcodes_gui_load_post_field_section_on_demand';
			$.ajax({
				url: url,
				success: function( response ) {
					self.post_field_section = response.data.section;
					$( '.js-wpv-shortcode-gui-group-list-post-field-section' ).each( function() {
						var thiz_instance = $( this );
						thiz_instance
							.fadeOut( 'fast', function() {
								thiz_instance
									.html( response.data.section )
									.fadeIn( 'fast' );
							});
					});
				}
			});
		}
	};

	/**
	 * Insert wpv-post-field shortcodes after generating the section on the GUI on demand
	 *
	 * @since 1.10
	 */

	$( document ).on( 'click', '.js-wpv-shortcode-gui-post-field-section-item', function() {
		var thiz = $( this ),
			thiz_fieldkey = thiz.data( 'fieldkey' ),
			thiz_group_list = thiz.closest( '.js-wpv-shortcode-gui-group-list' ),
			thiz_shortcode = "[wpv-post-field name='" + thiz_fieldkey + "']";
		self.insert_shortcode_with_no_attributes( 'wpv-post-field', thiz_shortcode );
	});
	
	/**
	* Scroll the Fields and Views dialog when clicking on a header menu item
	*
	* @since 2.2
	*/
	
	$( document ).on( 'click','.editor-addon-top-link', function() {
		
        var thiz	= $( this ),
		scrolling	= thiz.closest('.wpv-fields-and-views-dialog'),
        scrollingto	= scrolling.find( '.' + thiz.data('editor_addon_target' )+'-target' ),
        position	= scrollingto.position(),
        scrollto	= position.top;

        scrolling.animate({
            scrollTop: Math.round( scrollto ) - 25
        }, 'fast');

    });

	self.init();

};

jQuery( document ).ready( function( $ ) {
	WPViews.shortcodes_gui = new WPViews.ShortcodesGUI( $ );
});

var wpcfFieldsEditorCallback_redirect = null;

function wpcfFieldsEditorCallback_set_redirect(function_name, params) {
	wpcfFieldsEditorCallback_redirect = {'function' : function_name, 'params' : params};
}

/*
 * wpv-conditional shortcode QTags callback
 */
function wpv_add_conditional_quicktag_function(e, c, ed) {
	var  t = this;
	/*
	 !Important fix. If shortcode added from quicktags and not closed and we chage mode from text to visual, JS will generate error that closeTag = undefined.
	 */
	t.closeTag = function(el, event) {
		var ret = false, i = 0;
		while ( i < event.openTags.length ) {
			ret = event.openTags[i] == this.id ? i : false;
			el.value = this.display;
			i ++;
		}
		ed.openTags.splice(ret, 1);
	};
	window.wpcfActiveEditor = ed.id;
	var current_editor_object = {};
	if ( ed.canvas.selectionStart !== ed.canvas.selectionEnd ) {
		//When texty selected
		current_editor_object = {'e' : e, 'c' : c, 'ed' : ed, 't' : t, 'post_id' : '', 'close_tag' : true, 'codemirror' : ''};
		WPViews.shortcodes_gui.wpv_insert_popup_conditional('wpv-conditional', icl_editor_localization_texts.wpv_insert_conditional_shortcode, {}, icl_editor_localization_texts.wpv_editor_callback_nonce, current_editor_object );
	} else if ( ed.openTags ) {
		// if we have an open tag, see if it's ours
		var ret = false, i = 0, t = this;
		while ( i < ed.openTags.length ) {
			ret = ed.openTags[i] == t.id ? i : false;
			i ++;
		}
		if ( ret === false ) {
			t.tagStart = '';
			t.tagEnd = false;
			if ( ! ed.openTags ) {
				ed.openTags = [];
			}
			ed.openTags.push(t.id);
			e.value = '/' + e.value;
			current_editor_object = {'e' : e, 'c' : c, 'ed' : ed, 't' : t, 'post_id' : '', 'close_tag' : false, 'codemirror' : ''};
			WPViews.shortcodes_gui.wpv_insert_popup_conditional('wpv-conditional', icl_editor_localization_texts.wpv_insert_conditional_shortcode, {}, icl_editor_localization_texts.wpv_editor_callback_nonce, current_editor_object );
		} else {
			// close tag
			ed.openTags.splice(ret, 1);
			WPViews.shortcodes_gui.views_conditional_qtags_opened = false;
			t.tagStart = '[/wpv-conditional]';
			e.value = t.display;
			QTags.TagButton.prototype.callback.call(t, e, c, ed);
		}
	} else {
		// last resort, no selection and no open tags
		// so prompt for input and just open the tag
		t.tagStart = '';
		t.tagEnd = false;
		if ( ! ed.openTags ) {
			ed.openTags = [];
		}
		ed.openTags.push(t.id);
		e.value = '/' + e.value;
		current_editor_object = {'e' : e, 'c' : c, 'ed' : ed, 't' : t, 'post_id' : '', 'close_tag' : false, 'codemirror' : ''};
		WPViews.shortcodes_gui.wpv_insert_popup_conditional('wpv-conditional', icl_editor_localization_texts.wpv_insert_conditional_shortcode, {}, icl_editor_localization_texts.wpv_editor_callback_nonce, current_editor_object );
	}
=======
/**
 * views_shortcode_gui.js
 *
 * Contains helper functions for the popup GUI used to set Views shortcode attributes
 *
 * @since 1.7
 * @package Views
 */

var WPViews = WPViews || {};

WPViews.ShortcodesGUI = function( $ ) {
	var self = this;

	// Parametric search
	self.ps_view_id = 0;
	self.ps_orig_id = 0;

	self.dialog_insert_view						= null;
	self.dialog_insert_shortcode				= null;
	self.dialog_insert_views_conditional		= null;
	self.shortcodes_wrapper_dialogs				= {};
	
	self.dialog_minWidth						= 870;
	
	self.calculate_dialog_maxWidth = function() {
		return ( $( window ).width() - 100 );
	};
	
	self.calculate_dialog_maxHeight = function() {
		return ( $( window ).height() - 100 );
	};

	self.dialog_insert_view_locked				= false;

	self.suggest_cache							= {};
	self.shortcode_gui_insert					= 'insert';// @note This is also used in Types as a dependency!!
	self.shortcodes_set							= 'posts';
	self.shortcode_to_insert_on_target_dialog	= '';
	self.shortcode_gui_insert_count 			= 0;

	self.shortcode_gui_computed_attribute_pairs_filters = {};

	self.views_conditional_qtags_opened			= false;

	self.post_field_section						= false;

	self.views_conditional_use_gui				= true;

	self.numeric_natural_pattern				= /^[0-9]+$/;
	self.numeric_natural_list_pattern			= /^\d+(?:,\d+)*$/;
	self.numeric_natural_extended_pattern		= /^(-1|[0-9]+)$/;
	self.year_pattern							= /^([0-9]{4})$/;
	self.month_pattern							= /^([1-9]|1[0-2])$/;
	self.week_pattern							= /^([1-9]|[1234][0-9]|5[0-3])$/;
	self.day_pattern							= /^([1-9]|[12][0-9]|3[0-1])$/;
	self.hour_pattern							= /^([0-9]|[1][0-9]|2[0-3])$/;
	self.minute_pattern							= /^([0-9]|[1234][0-9]|5[0-9])$/;
	self.second_pattern							= /^([0-9]|[1234][0-9]|5[0-9])$/;
	self.dayofyear_pattern						= /^([1-9]|[1-9][0-9]|[12][0-9][0-9]|3[0-6][0-6])$/;
	self.dayofweek_pattern						= /^[1-7]+$/;
	self.url_patern								= /^(https?):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
	self.orderby_postfield_pattern				= /^field-/;
	self.orderby_termmeta_field_pattern			= /^taxonomy-field-/;
	self.orderby_usermeta_field_pattern			= /^user-field-/;

	/**
	 * Temporary dialog content to be displayed while the actual content is loading.
	 *
	 * It contains a simple spinner in the centre. I decided to implement styling directly, it will not be reused and
	 * it would only bloat views-admin.css (jan).
	 *
	 * @type {HTMLElement}
	 * @since 1.9
	 */
	self.shortcodeDialogSpinnerContent = $(
		'<div style="min-height: 150px;">' +
		'<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; ">' +
		'<div class="wpv-spinner ajax-loader"></div>' +
		'<p>' + wpv_shortcodes_gui_texts.loading_options + '</p>' +
		'</div>' +
		'</div>'
	);

	self.init = function() {
		self.init_admin_bar_button();
		self.init_dialogs();
	};

	self.init_dialogs = function() {
		// Initialize dialogs
		if ( ! $('#js-wpv-shortcode-gui-dialog-container').length ) {
			$( 'body' ).append( '<div id="js-wpv-shortcode-gui-dialog-container" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-shortcode-gui-dialog-container"></div>' );
			self.dialog_insert_shortcode = $( "#js-wpv-shortcode-gui-dialog-container" ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass( 'modal-open' );
					$( '.js-wpv-shortcode-gui-insert' )
						.addClass( 'button-secondary' )
						.removeClass( 'button-primary ui-button-disabled ui-state-disabled' )
						.prop( 'disabled', true );
				},
				close: function( event, ui ) {
					$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_closed' );
					$( 'body' ).removeClass( 'modal-open' );
				},
				buttons:[
					{
						class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-wpv-shortcode-gui-button-close js-wpv-shortcode-gui-close',
						text: wpv_shortcodes_gui_texts.wpv_close,
						click: function() {
							$( this ).dialog( "close" );
						}
					},
					{
						class: 'button-secondary js-wpv-shortcode-gui-button-insert js-wpv-shortcode-gui-insert',
						text: wpv_shortcodes_gui_texts.wpv_insert_shortcode,
						disabled: 'disabled',
						click: function() {
							self.wpv_insert_shortcode();
						}
					}
				]
			});

			$( 'body' ).append( '<div id="js-wpv-view-shortcode-gui-dialog-container" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-shortcode-gui-dialog-container"></div>' );
			self.dialog_insert_view = $( "#js-wpv-view-shortcode-gui-dialog-container" ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass( 'modal-open' );
					$( '.js-wpv-insert-view-form-action' )
						.addClass( 'button-secondary' )
						.removeClass( 'button-primary ui-button-disabled ui-state-disabled' )
						.prop( 'disabled', true );
				},
				close: function( event, ui ) {
					$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_closed' );
					$( 'body' ).removeClass( 'modal-open' );
				},
				buttons:[
					{
						class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-wpv-shortcode-gui-button-close',
						text: wpv_shortcodes_gui_texts.wpv_close,
						click: function() {
							$( this ).dialog( "close" );
						}
					},
					{
						class: 'button-secondary js-wpv-shortcode-gui-button-insert js-wpv-insert-view-form-action',
						text: wpv_shortcodes_gui_texts.wpv_insert_shortcode,
						disabled: 'disabled',
						click: function() {
							self.wpv_insert_view_shortcode_to_editor();
						}
					}
				]
			});

			$( 'body' ).append( '<div id="js-wpv-views-conditional-shortcode-gui-dialog-container" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-shortcode-gui-dialog-container"></div>' );
			self.dialog_insert_views_conditional = $( "#js-wpv-views-conditional-shortcode-gui-dialog-container" ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass( 'modal-open' );
					$( ".ui-dialog-titlebar-close" ).hide();
					self.views_conditional_use_gui = true;
					$( '.js-wpv-shortcode-gui-insert' )
						.addClass( 'button-secondary' )
						.removeClass( 'button-primary ui-button-disabled ui-state-disabled' )
						.prop( 'disabled', true );
				},
				close: function( event, ui ) {
					if (  !self.views_conditional_qtags_opened && typeof self.wpv_conditional_object.ed.openTags !== 'undefined' ){
						var ed = self.wpv_conditional_object.ed, ret = false, i = 0;
						self.views_conditional_qtags_opened = false;
						while ( i < ed.openTags.length ) {
							ret = ed.openTags[i] == self.wpv_conditional_object.t.id ? i : false;
							i ++;
						}
						ed.openTags.splice(ret, 1);
						self.wpv_conditional_object.e.value = self.wpv_conditional_object.t.display;
					}
					$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_closed' );
					$( 'body' ).removeClass( 'modal-open' );
				},
				buttons:[
					{
						class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-wpv-shortcode-gui-close',
						text: wpv_shortcodes_gui_texts.wpv_close,
						click: function() {
							// remove wpv-conditional from QTags:opened tags
							self.wpv_conditional_close = false;
							self.views_conditional_qtags_opened = false;
							if ( !self.views_conditional_qtags_opened && typeof self.wpv_conditional_object.openTags !== 'undefined' ) {
								var ed = self.wpv_conditional_object.ed, ret = false, i = 0;
								while ( i < ed.openTags.length ) {
									ret = ed.openTags[i] == self.wpv_conditional_object.t.id ? i : false;
									i ++;
								}
								ed.openTags.splice(ret, 1);
								self.wpv_conditional_object.e.value = self.wpv_conditional_object.t.display;
							}
							$( this ).dialog( "close" );
						}
					},
					{
						class: 'button-secondary js-wpv-shortcode-gui-insert',
						text: wpv_shortcodes_gui_texts.wpv_insert_shortcode,
						disabled: 'disabled',
						click: function() {
							self.wpv_insert_view_conditional_shortcode();
						}
					}
				]
			});
		}

		var dialog_posts		= $( 'body' ).find('.js-wpv-fields-and-views-dialog-for-posts'),
		dialog_taxonomy			= $( 'body' ).find('.js-wpv-fields-and-views-dialog-for-taxonomy'),
		dialog_users			= $( 'body' ).find('.js-wpv-fields-and-views-dialog-for-users');

		if ( dialog_posts.length > 0 ) {
			self.shortcodes_wrapper_dialogs[ 'posts' ] = $( '.js-wpv-fields-and-views-dialog-for-posts' ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				title:		wpv_shortcodes_gui_texts.wpv_fields_and_views_title,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass('modal-open');
					// Hide top links if div too small
					wpv_hide_top_groups( $( this ).parent() );
					$( dialog_posts )
						.find( '.search_field' )
						.focus();
					var data_for_events = {};
					data_for_events.kind = 'posts';
                    data_for_events.dialog = this;
					$( document ).trigger( 'js_event_wpv_fields_and_views_dialog_opened', [ data_for_events ] );
				},
				close: function( event, ui ) {
					$( 'body' ).removeClass( 'modal-open' );
					$( this ).dialog("close");
				}
			});
		}

		if ( dialog_taxonomy.length > 0 ) {
			self.shortcodes_wrapper_dialogs[ 'taxonomy' ] = $( '.js-wpv-fields-and-views-dialog-for-taxonomy' ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				title:		wpv_shortcodes_gui_texts.wpv_fields_and_views_title,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass('modal-open');
					// Hide top links if div too small
					wpv_hide_top_groups( $( this ).parent() );
					$( dialog_taxonomy )
						.find( '.search_field' )
						.focus();
					var data_for_events = {};
					data_for_events.kind = 'taxonomy';
					data_for_events.dialog = this;
					$( document ).trigger( 'js_event_wpv_fields_and_views_dialog_opened', [ data_for_events ] );
				},
				close: function( event, ui ) {
					$( 'body' ).removeClass( 'modal-open' );
					$( this ).dialog("close");
				}
			});
		}

		if ( dialog_users.length > 0 ) {
			self.shortcodes_wrapper_dialogs[ 'users' ] = $( '.js-wpv-fields-and-views-dialog-for-users' ).dialog({
				autoOpen:	false,
				modal:		true,
				width:		self.dialog_minWidth,
				title:		wpv_shortcodes_gui_texts.wpv_fields_and_views_title,
				resizable:	false,
				draggable:	false,
				show: {
					effect:		"blind",
					duration:	800
				},
				create: function( event, ui ) {
					$( event.target ).parent().css( 'position', 'fixed' );
				},
				open: function( event, ui ) {
					$( 'body' ).addClass('modal-open');
					// Hide top links if div too small
					wpv_hide_top_groups( $( this ).parent() );
					$( dialog_users )
						.find( '.search_field' )
						.focus();
					var data_for_events = {};
					data_for_events.kind = 'users';
                    data_for_events.dialog = this;
					$( document ).trigger( 'js_event_wpv_fields_and_views_dialog_opened', [ data_for_events ] );
				},
				close: function( event, ui ) {
					$( 'body' ).removeClass( 'modal-open' );
					$( this ).dialog("close");
				}
			});
		}

		self.textarea_target_dialog = $('#wpv-shortcode-generator-target-dialog').dialog({
			autoOpen:	false,
			modal:		true,
			width:		self.dialog_minWidth,
			title:		wpv_shortcodes_gui_texts.wpv_shortcode_generated,
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			create: function( event, ui ) {
				$( event.target ).parent().css( 'position', 'fixed' );
			},
			buttons: [
				{
					class: 'button-primary',
					text: wpv_shortcodes_gui_texts.wpv_close,
					click: function() {
						$( this ).dialog( "close" );
					}
				},
			],
			open: function( event, ui ) {
				// TODO: close shortcode generator dialog now
				if ( _.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set ) ) {
					self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog("close");
				}
				$( '#wpv-shortcode-generator-target' )
					.html( self.shortcode_to_insert_on_target_dialog )
					.focus();
				$('body').addClass('modal-open');
			},
			close: function( event, ui ) {
				$('body').removeClass('modal-open');
				self.shortcode_gui_insert = 'insert';
				$( this ).dialog("close");
			}
		});
	};

	//-----------------------------------------
	// Fields and Views button management
	//
	// @since 1.12
	//-----------------------------------------

	/**
	 * Init the admin bar button, if any, and make sure we load the right dialog when editing a View
	 *
	 * @since 1.10
	 */

	self.init_admin_bar_button = function() {
		if ( $( '.js-wpv-shortcode-generator-node a' ).length > 0 ) {
			$( '.js-wpv-shortcode-generator-node a' )
				.addClass( 'js-wpv-fields-and-views-in-adminbar' )
				.removeClass( 'js-wpv-fields-and-views-in-toolbar' );
		}
		if ( $( '.js-wpv-query-type' ).length > 0 ) {
			self.shortcodes_set = $( '.js-wpv-query-type:checked' ).val();
			$( document ).on( 'change', '.js-wpv-query-type', function() {
				self.shortcodes_set = $( '.js-wpv-query-type:checked' ).val();
			});
		}
	};

	$( document ).on( 'click', '.toolset-shortcodes-shortcode-menu', function( e ) {
		e.preventDefault();
		return false;
	});

	/**
	 * Set the right active editor when clicking any Fields and Views button, and open / close the dialogs when needed
	 */

	$( document ).on( 'click','.js-wpv-fields-and-views-in-adminbar', function( e ) {
		e.preventDefault();
		self.shortcode_gui_insert = 'create';
		self.open_fields_and_views_dialog();
		return false;
	});

	$( document ).on( 'click', '.js-wpv-fields-and-views-in-toolbar', function( e ) {
		e.preventDefault();
		var thiz = $( this );
		if ( thiz.attr( 'data-editor' ) ) {
			window.wpcfActiveEditor = thiz.data( 'editor' );
		}
		self.shortcode_gui_insert = 'insert';
		self.open_fields_and_views_dialog();
		return false;
	});

	self.open_fields_and_views_dialog = function() {
		if ( _.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set ) ) {
			self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog( 'open' ).dialog({
				height:		self.calculate_dialog_maxHeight(),
				maxWidth:	self.calculate_dialog_maxWidth(),
				position: 	{
					my:			"center top+50",
					at:			"center top",
					of:			window,
					collision:	"none"
				}
			});
		}
		// Bind Escape
		$( document ).bind( 'keyup', function( e ) {
			if ( e.keyCode == 27 ) {
				if ( _.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set ) ) {
					self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog( 'close' );
				}
				$( this ).unbind( e );
			}
		});
	};

	// Close when clicking on an item from it, always
	$( document ).on( 'click', '.js-wpv-fields-views-dialog-content .item', function( event, data ) {
		if (
			_.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set )
			&& self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog( "isOpen" )
		) {
			self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog('close');
		}
	});

	// Open the textarea_target_dialog dialog when the action is set to "create" and fill the value it needs to show

	$( document ).on( 'js_event_wpv_shortcode_inserted', function( event, shortcode_name, shortcode_content, shortcode_attribute_values, shortcode_to_insert ) {
		if ( self.shortcode_gui_insert == 'create' ) {
			self.shortcode_to_insert_on_target_dialog = shortcode_to_insert;

			if( typeof wpv_add_shortcode_to !== 'undefined' ) {
				wpv_add_shortcode_to.val( wpv_add_shortcode_to.val() + shortcode_to_insert );
				return;
			}

			self.textarea_target_dialog.dialog("open").dialog({
				maxHeight:	self.calculate_dialog_maxHeight(),
				maxWidth:	self.calculate_dialog_maxWidth(),
				position:	{
					my:			"center top+50",
					at:			"center top",
					of:			window,
					collision:	"none"
				}
			});
			self.shortcode_gui_insert = 'insert';
		}
	});

	$( document ).on( 'js_types_shortcode_created', function( event, shortcode_to_insert ) {
		if ( self.shortcode_gui_insert == 'create' ) {
			self.shortcode_to_insert_on_target_dialog = shortcode_to_insert;

			if( typeof wpv_add_shortcode_to !== 'undefined' ) {
				wpv_add_shortcode_to.val( wpv_add_shortcode_to.val() + shortcode_to_insert );
				return;
			}

			self.textarea_target_dialog.dialog("open").dialog({
				maxHeight:	self.calculate_dialog_maxHeight(),
				maxWidth:	self.calculate_dialog_maxWidth(),
				position:	{
					my:			"center top+50",
					at:			"center top",
					of:			window,
					collision:	"none"
				}
			});
			self.shortcode_gui_insert = 'insert';
		}
	});

	//-----------------------------------------
	// Parametric search
	//-----------------------------------------


	self.wpv_insert_view_shortcode_dialog = function( view_id, view_title, view_name, orig_id, nonce ) {
		self.ps_view_id = view_id;
		self.ps_orig_id = orig_id;

		var data_view = {
			action:		'wpv_view_form_popup',
			_wpnonce:	nonce,
			view_id:	view_id,
			orig_id:	orig_id,
			view_title:	view_title,
			view_name:	view_name
		},
		data_for_events = {};

		data_for_events.shortcode = 'wpv-view';
		data_for_events.title = view_title;
		data_for_events.params = {};
		data_for_events.nonce = nonce;
		data_for_events.object = {};
        data_for_events.dialog = this;
		$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_triggered', [ data_for_events ] );

		self.dialog_insert_view.dialog('open').dialog({
			title:		view_title,
			maxHeight:	self.calculate_dialog_maxHeight(),
			maxWidth:	self.calculate_dialog_maxWidth(),
			position:	{
				my:			"center top+50",
				at:			"center top",
				of:			window,
				collision:	"none"
			}
		});

		self.manage_dialog_button_labels();

		self.dialog_insert_view.html( self.shortcodeDialogSpinnerContent );

		//
		// Do AJAX call
		//
		$.ajax({
			url: wpv_shortcodes_gui_texts.ajaxurl,
			data: data_view,
			type:"GET",
			success: function( data ) {
				$( 'body' ).addClass( 'modal-open' );
				self.dialog_insert_view.html( data );
				$( '.js-wpv-insert-view-form-action' )
					.addClass( 'button-primary' )
					.removeClass( 'button-secondary' )
					.prop( 'disabled', false );
				$('.js-wpv-shortcode-gui-tabs')
					.tabs({
						beforeActivate: function( event, ui ) {
							if (
								ui.oldPanel.attr( 'id' ) == 'js-wpv-insert-view-parametric-search-container'
								&& self.dialog_insert_view_locked
							) {
								event.preventDefault();
								ui.oldTab.focus().addClass( 'wpv-shortcode-gui-tabs-incomplete' );
								$( '.wpv-advanced-setting', ui.oldPanel ).addClass( 'wpv-advanced-setting-incomplete' );
								setTimeout( function() {
									ui.oldTab.removeClass( 'wpv-shortcode-gui-tabs-incomplete' );
									$( '.wpv-advanced-setting', ui.oldPanel ).removeClass( 'wpv-advanced-setting-incomplete' );
								}, 1000 );
							}
						}
					})
					.addClass('ui-tabs-vertical ui-helper-clearfix')
					.removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
				$('#js-wpv-shortcode-gui-dialog-tabs ul, #js-wpv-shortcode-gui-dialog-tabs li').removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');

				$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_opened', [ data_for_events ] );
			}
		});
	};
	
	$( document ).on( 'change input cut paste', '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby', function() {
		var orderby_value = $( this ).val();
		
		if (
			self.orderby_postfield_pattern.test( orderby_value ) 
			|| self.orderby_termmeta_field_pattern.test( orderby_value )
			|| self.orderby_usermeta_field_pattern.test( orderby_value )
		) {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby_as-setting' ).fadeIn( 'fast' );
		} else {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby_as-setting' ).hide();
		}
	});
	
	$( document ).on( 'change input cut paste', '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby_second', function() {
		var orderby_second_value = $( this ).val();
		
		if ( orderby_second_value == '' ) {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-order_second' ).prop( 'disabled', true );
		} else {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-order_second' ).prop( 'disabled', false );
		}
	});

	self.wpv_get_view_override_values = function() {
		var override_container = $( '#js-wpv-insert-view-override-container' ),
			override_values = {};
			
		if ( $( '.js-wpv-insert-view-shortcode-limit', override_container ).val() != '' ) {
			override_values['limit'] = $( '.js-wpv-insert-view-shortcode-limit', override_container ).val();
		}
		if ( $( '.js-wpv-insert-view-shortcode-offset', override_container ).val() != '' ) {
			override_values['offset'] = $( '.js-wpv-insert-view-shortcode-offset', override_container ).val();
		}
		if ( $( '.js-wpv-insert-view-shortcode-orderby', override_container ).val() != '' ) {
			override_values['orderby'] = $( '.js-wpv-insert-view-shortcode-orderby', override_container ).val();
			if ( 
				$( '.js-wpv-insert-view-shortcode-orderby_as', override_container ).length > 0
				&& $( '.js-wpv-insert-view-shortcode-orderby_as', override_container ).val() != '' 
			) {
				if (
					self.orderby_postfield_pattern.test( override_values['orderby'] ) 
					|| self.orderby_termmeta_field_pattern.test( override_values['orderby'] )
					|| self.orderby_usermeta_field_pattern.test( override_values['orderby'] )
				) {
					override_values['orderby_as'] = $( '.js-wpv-insert-view-shortcode-orderby_as', override_container ).val();
				}
			}
		}
		if ( $( '.js-wpv-insert-view-shortcode-order', override_container ).val() != '' ) {
			override_values['order'] = $( '.js-wpv-insert-view-shortcode-order', override_container ).val();
		}
		// Secondary sorting
		if ( 
			$( '.js-wpv-insert-view-shortcode-orderby_second', override_container ).length > 0 
			&& $( '.js-wpv-insert-view-shortcode-orderby_second', override_container ).val() != '' 
		) {
			override_values['orderby_second'] = $( '.js-wpv-insert-view-shortcode-orderby_second', override_container ).val();
		}
		if ( 
			$( '.js-wpv-insert-view-shortcode-order_second', override_container ).length > 0 
			&& $( '.js-wpv-insert-view-shortcode-order_second', override_container ).val() != '' 
		) {
			override_values['order_second'] = $( '.js-wpv-insert-view-shortcode-order_second', override_container ).val();
		}
		return override_values;
	};

	self.wpv_get_view_extra_values = function() {
		var extra_container = $( '#js-wpv-insert-view-extra-attributes-container' ),
			extra_values = {};
		if ( extra_container.length > 0 ) {
			$( '.js-wpv-insert-view-shortcode-extra-attribute', extra_container ).each( function() {
				var thiz = $( this );
				if ( thiz.val() != '' ) {
					extra_values[ thiz.data( 'attribute' ) ] = thiz.val();
				}
			});
		}
		return extra_values;
	};

	self.wpv_get_view_cache_values = function() {
		var cache_container = $( '#js-wpv-insert-view-cache-attributes-container' ),
			cache_values = {};
		if ( cache_container.length > 0 ) {
			var use_cache = $( '.js-wpv-insert-view-shortcode-cache:checked', cache_container ).val();
			if ( 'off' == use_cache ) {
				cache_values['cached'] = 'off';
			}
		}
		return cache_values;
	};

	self.dialog_insert_view_locked_check = function() {
		var container = $( '#js-wpv-insert-view-parametric-search-container' );
		if ( $( '.js-wpv-insert-view-form-display:checked', container ).val() == 'form' ) {
			var target = $( '.js-wpv-insert-view-form-target:checked', container ).val(),
				set_target = $( '.js-wpv-insert-view-form-target-set:checked', container ).val(),
				set_target_id = $( '.js-wpv-insert-view-form-target-set-existing-id', container ).val();
			if ( target == 'self' ) {
				$( '.js-wpv-insert-view-form-action' ).addClass( 'button-primary' ).removeClass( 'button-secondary' ).prop( 'disabled', false );
				self.dialog_insert_view_locked = false;
			} else {
				if ( set_target == 'existing' && set_target_id != '' ) {
					$( '.js-wpv-insert-view-form-target-set-actions' ).show();
				}
				$( '.js-wpv-insert-view-form-action' ).removeClass( 'button-primary' ).addClass( 'button-secondary' ).prop( 'disabled', true );
				self.dialog_insert_view_locked = true;
			}
		} else {
			self.dialog_insert_view_locked = false;
		}
	};

	self.wpv_insert_view_shortcode_to_editor = function() {
		var form_name = $( '#js-wpv-view-shortcode-gui-dialog-view-title' ).val(),
			override_values = self.wpv_get_view_override_values(),
			override_values_string = '',
			extra_values = self.wpv_get_view_extra_values(),
			extra_values_string = '',
			cache_values = self.wpv_get_view_cache_values(),
			cache_values_string = '',
			valid = self.validate_shortcode_attributes( $( '#js-wpv-view-shortcode-gui-dialog-container' ), $( '#js-wpv-view-shortcode-gui-dialog-container' ), $( '#js-wpv-view-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) ),
			shortcode_to_insert = '',
			shortcode_attribute_values = {};

		if ( ! valid ) {
			return;
		}

		shortcode_attribute_values['name'] = form_name;
		_.map( override_values, function( over_val, over_key ) {
			shortcode_attribute_values[ over_key ] = over_val;
			override_values_string += ' ' + over_key + '="' + over_val + '"';
		});
		_.map( extra_values, function( extra_val, extra_key ) {
			shortcode_attribute_values[ extra_key ] = extra_val;
			extra_values_string += ' ' + extra_key + '="' + extra_val + '"';
		});
		_.each( cache_values, function( cache_val, cache_key ) {
			shortcode_attribute_values[ cache_key ] = cache_val;
			cache_values_string += ' ' + cache_key + '="' + cache_val + '"';
		});

		if ( $( '#js-wpv-insert-view-parametric-search-container' ).length > 0 ) {

			var display = $( '.js-wpv-insert-view-form-display:checked' ).val(),
				target = $( '.js-wpv-insert-view-form-target:checked' ).val(),
				set_target = $( '.js-wpv-insert-view-form-target-set:checked' ).val(),
				set_target_id = $( '.js-wpv-insert-view-form-target-set-existing-id' ).val(),
				results_helper_container = $( '.js-wpv-insert-form-workflow-help-box' ),
				results_helper_container_after = $( '.js-wpv-insert-form-workflow-help-box-after' );

			if ( display == 'both' ) {
				shortcode_to_insert = '[wpv-view name="' + form_name + '"' + override_values_string + extra_values_string + cache_values_string + ']';
				self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-view', shortcode_attribute_values, shortcode_to_insert );
				if (
					results_helper_container.length > 0
					&& results_helper_container.hasClass( 'js-wpv-insert-form-workflow-help-box-for-' + self.ps_view_id )
				) {
					results_helper_container.fadeOut( 'fast' );
				}
				if (
					results_helper_container_after.length > 0
					&& results_helper_container_after.hasClass( 'js-wpv-insert-form-workflow-help-box-for-after-' + self.ps_view_id )
				) {
					results_helper_container_after.show();
				}
			} else if ( display == 'results' ) {
				shortcode_to_insert = '[wpv-view name="' + form_name + '" view_display="layout"' + override_values_string + extra_values_string + cache_values_string + ']';
				self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-view', shortcode_attribute_values, shortcode_to_insert );
				if (
					results_helper_container.length > 0
					&& results_helper_container.hasClass( 'js-wpv-insert-form-workflow-help-box-for-' + self.ps_view_id )
				) {
					results_helper_container.fadeOut( 'fast' );
				}
				if (
					results_helper_container_after.length > 0
					&& results_helper_container_after.hasClass( 'js-wpv-insert-form-workflow-help-box-for-after-' + self.ps_view_id )
				) {
					results_helper_container_after.show();
				}
			} else if ( display == 'form' ) {
				if ( target == 'self' ) {
					shortcode_to_insert = '[wpv-form-view name="' + form_name + '" target_id="self"]';
					self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-form-view', shortcode_attribute_values, shortcode_to_insert );
					if ( results_helper_container.length > 0 ) {
						var results_shortcode = '<code>[wpv-view name="' + form_name + '" view_display="layout"]</code>';
						results_helper_container.find( '.js-wpv-insert-view-form-results-helper-name' ).html( form_name );
						results_helper_container.find( '.js-wpv-insert-view-form-results-helper-shortcode' ).html( results_shortcode );
						results_helper_container.addClass( 'js-wpv-insert-form-workflow-help-box-for-' + self.ps_view_id ).fadeIn( 'fast' );
					}
				} else {
					shortcode_to_insert = '[wpv-form-view name="' + form_name + '" target_id="' + set_target_id + '"' + override_values_string + extra_values_string + cache_values_string + ']';
					self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-form-view', shortcode_attribute_values, shortcode_to_insert );
				}
			}

		} else {
			shortcode_to_insert = '[wpv-view name="' + form_name + '"' + override_values_string + extra_values_string + cache_values_string + ']';
			self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-view', shortcode_attribute_values, shortcode_to_insert );

		}
	};

	self.wpv_insert_view_shortcode_to_editor_helper = function( shortcode_name, shortcode_attribute_values, shortcode_to_insert ) {
		self.dialog_insert_view.dialog('close');
		if ( self.shortcode_gui_insert == 'insert' ) {
			window.icl_editor.insert( shortcode_to_insert );
		}
		$( document ).trigger( 'js_event_wpv_shortcode_inserted', [ shortcode_name, '', shortcode_attribute_values, shortcode_to_insert ] );
	};


	/**
	 * Suggest for parametric search target
	 */

	$( document ).on( 'focus', '.js-wpv-insert-view-form-target-set-existing-title:not(.js-wpv-shortcode-gui-suggest-inited)', function() {
		var thiz = $( this );
		thiz
			.addClass( 'js-wpv-shortcode-gui-suggest-inited' )
			.suggest( wpv_shortcodes_gui_texts.ajaxurl + '&action=wpv_suggest_form_targets', {
				resultsClass: 'ac_results wpv-suggest-results',
				onSelect: function() {
					var t_value = this.value,
						t_split_point = t_value.lastIndexOf(' ['),
						t_title = t_value.substr( 0, t_split_point ),
						t_extra = t_value.substr( t_split_point ).split('#'),
						t_id = t_extra[1].replace(']', '');
					$( '.js-wpv-filter-form-help' ).hide();
					$('.js-wpv-insert-view-form-target-set-existing-title').val( t_title );
					t_edit_link = $('.js-wpv-insert-view-form-target-set-existing-link').data( 'editurl' );
					t_view_id = $('.js-wpv-insert-view-form-target-set-existing-link').data( 'viewid' );
					t_orig_id = $('.js-wpv-insert-view-form-target-set-existing-link').data('origid');
					$( '.js-wpv-insert-view-form-target-set-existing-link' ).attr( 'href', t_edit_link + t_id + '&action=edit&completeview=' + t_view_id + '&origid=' + t_orig_id );
					$( '.js-wpv-insert-view-form-target-set-existing-id' ).val( t_id ).trigger( 'change' );
					$( '.js-wpv-insert-view-form-target-set-actions' ).show();
				}
			});
	});

	/*
	 * Adjust the action button text copy based on the action to perform
	 */

	$( document ).on( 'change', '.js-wpv-insert-view-form-display', function() {
		var container = $( '#js-wpv-insert-view-parametric-search-container' ),
			display_container = $( '.js-wpv-insert-view-form-display-container', container ),
			display = $( '.js-wpv-insert-view-form-display:checked', container ).val(),
			target_container = $( '.js-wpv-insert-view-form-target-container', container ),
			target = $( '.js-wpv-insert-view-form-target:checked', container ).val(),
			set_target = $( '.js-wpv-insert-view-form-target-set:checked', container ).val(),
			set_target_id = $( '.js-wpv-insert-view-form-target-set-existing-id', container ).val(),
			results_helper_container = $( '.js-wpv-insert-form-workflow-help-box', container ),
			results_helper_container_after = $( '.js-wpv-insert-form-workflow-help-box-after', container );
		if ( display == 'form' ) {
			target_container.fadeIn();
		} else {
			target_container.fadeOut();
		}
		self.dialog_insert_view_locked_check();
	});

	/*
	 * Adjust the GUI when inserting just the form, based on the target options - target this or other page
	 */

	$( document ).on( 'change', '.js-wpv-insert-view-form-target', function() {
		var target = $( '.js-wpv-insert-view-form-target:checked' ).val(),
			set_target = $( '.js-wpv-insert-view-form-target-set:checked' ).val();
		if ( target == 'self' ) {
			$( '.js-wpv-insert-view-form-target-set-container' ).hide();
		} else if ( target == 'other' ) {
			$( '.js-wpv-insert-view-form-target-set-container' ).fadeIn( 'fast' );
		}
		self.dialog_insert_view_locked_check();
	});

	$( document ).on( 'click', '.js-wpv-insert-view-form-target-set-discard', function( e ) {
		e.preventDefault();
		self.dialog_insert_view_locked = false;
		$( '.js-wpv-insert-view-form-action' )
			.addClass( 'button-primary' )
			.removeClass( 'button-secondary' )
			.prop( 'disabled', false );
		$( '.js-wpv-insert-view-form-target-set-actions' ).hide();
	});

	$( document ).on( 'click', '.js-wpv-insert-view-form-target-set-existing-link', function() {
		self.dialog_insert_view_locked = false;
		$( '.js-wpv-insert-view-form-action' )
			.addClass( 'button-primary' )
			.removeClass( 'button-secondary' )
			.prop( 'disabled', false );
		$( '.js-wpv-insert-view-form-target-set-actions' ).hide();
	});

	/*
	 * Adjust the GUI when inserting just the form and targeting another page, based on the target options - target existing or new page
	 */

	$( document ).on( 'change', '.js-wpv-insert-view-form-target-set', function() {
		var set_target = $( '.js-wpv-insert-view-form-target-set:checked' ).val();
		if ( set_target == 'create' ) {
			$( '.js-wpv-insert-view-form-target-set-existing-extra' ).hide();
			$( '.js-wpv-insert-view-form-target-set-create-extra' ).fadeIn( 'fast' );
			$( '.js-wpv-insert-view-form-action' )
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
		} else if ( set_target == 'existing' ) {
			$( '.js-wpv-insert-view-form-target-set-create-extra' ).hide();
			$( '.js-wpv-insert-view-form-target-set-existing-extra' ).fadeIn( 'fast' );
			$( '.js-wpv-insert-view-form-action' )
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
			if ( $( '.js-wpv-insert-view-form-target-set-existing-id' ).val() != '' ) {
				$( '.js-wpv-insert-view-form-target-set-actions' ).show();
			}
		}
		self.dialog_insert_view_locked_check();
	});

	/*
	 * Adjust values when editing the target page title - clean data and mark this as unfinished
	 */

	$( document ).on('change input cut paste', '.js-wpv-insert-view-form-target-set-existing-title', function() {
		$( '.js-wpv-insert-view-form-target-set-actions' ).hide();
		$( '.js-wpv-insert-view-form-target-set-existing-link' ).attr( 'data-targetid', '' );
		$('.js-wpv-insert-view-form-target-set-existing-id')
			.val( '' )
			.trigger( 'manchange' );
	});

	/*
	 * Disable the insert button when doing any change in the existing title textfield
	 *
	 * We use a custom event 'manchange' as in "manual change"
	 */

	$( document ).on( 'manchange', '.js-wpv-insert-view-form-target-set-existing-id', function() {
		$( '.js-wpv-insert-view-form-action' )
			.removeClass( 'button-primary' )
			.addClass( 'button-secondary' )
			.prop( 'disabled', true );
		self.dialog_insert_view_locked_check();
	});

	/*
	 * Adjust GUI when creating a target page, based on the title value
	 */

	$( document ).on( 'change input cut paste', '.js-wpv-insert-view-form-target-set-create-title', function() {
		if ( $( '.js-wpv-insert-view-form-target-set-create-title' ).val() == '' ) {
			$( '.js-wpv-insert-view-form-target-set-create-action' )
				.prop( 'disabled', true )
				.addClass( 'button-secondary' )
				.removeClass( 'button-primary' );
		} else {
			$( '.js-wpv-insert-view-form-target-set-create-action' )
				.prop( 'disabled', false )
				.addClass( 'button-primary' )
				.removeClass( 'button-secondary' );
		}
	});

	/*
	 * AJAX action to create a new target page
	 */

	$( document ).on( 'click', '.js-wpv-insert-view-form-target-set-create-action', function() {
		var thiz = $( this ),
			thiz_existing_radio = $( '.js-wpv-insert-view-form-target-set[value="existing"]' ),
			spinnerContainer = $('<div class="wpv-spinner ajax-loader">').insertAfter( thiz ).show();
		data = {
			action: 'wpv_create_form_target_page',
			post_title: $( '.js-wpv-insert-view-form-target-set-create-title' ).val(),
			_wpnonce: thiz.data( 'nonce' )
		};
		$.ajax({
			url: wpv_shortcodes_gui_texts.ajaxurl,
			data: data,
			success: function( response ) {
				decoded_response = $.parseJSON( response );
				if ( decoded_response.result == 'error' ) {

				} else {
					$( '.js-wpv-insert-view-form-target-set-existing-title' ).val( decoded_response.page_title );
					$( '.js-wpv-insert-view-form-target-set-existing-id' ).val( decoded_response.page_id );
					t_edit_link = $('.js-wpv-insert-view-form-target-set-existing-link').data( 'editurl' );
					$('.js-wpv-insert-view-form-target-set-existing-link')
						.attr( 'href', t_edit_link + decoded_response.page_id + '&action=edit&completeview=' + self.ps_view_id + '&origid=' + self.ps_orig_id );
					thiz_existing_radio
						.prop( 'checked', true )
						.trigger( 'change' );
					$( '.js-wpv-insert-view-form-target-set-actions' ).show();
				}
			},
			error: function ( ajaxContext ) {

			},
			complete: function() {
				spinnerContainer.remove();
			}
		});
	});

	// Close the finished help boxes

	$( document ).on( 'click', '.js-wpv-insert-form-workflow-help-box-close', function( e ) {
		e.preventDefault();
		$( this ).closest( '.js-wpv-insert-form-workflow-help-box, .js-wpv-insert-form-workflow-help-box-after' ).hide();
	});
	
	// Toggle advanced settings on the dialog to insert a View
	
	$( document ).on( 'click', '.js-wpv-insert-views-shortcode-advanced-toggler', function( e ) {
		e.preventDefault();
		$( this )
			.find( 'i' )
				.toggleClass( 'fa-caret-down fa-caret-up' );
		$( '.js-wpv-insert-views-shortcode-advanced-wrapper' ).fadeToggle( 'fast' );
	});


	/**
	 * wpv_insert_popup_conditional
	 *
	 * @since 1.9
	 */

	self.wpv_insert_popup_conditional = function( shortcode, title, params, nonce, object ) {
		/**
		 * Build AJAX url
		 */

		var url = wpv_shortcodes_gui_texts.ajaxurl;
		url += '&_wpnonce=' + nonce;
		url += '&action=wpv_shortcode_gui_dialog_conditional_create';
		url += '&post_id=' + parseInt( object.post_id );

		self.dialog_insert_views_conditional.dialog('open').dialog({
			title:		title,
			height:		self.calculate_dialog_maxHeight(),
			width:		self.calculate_dialog_maxWidth(),
			position:	{
				my:			"center top+50",
				at:			"center top",
				of:			window,
				collision:	"none"
			}
		});

		self.dialog_insert_views_conditional.html( self.shortcodeDialogSpinnerContent );
		/**
		 * Do AJAX call
		 */
		$.ajax({
			url: url,
			success: function( data ) {
				$( 'body' ).addClass( 'modal-open' );
				self.dialog_insert_views_conditional.html( data ).dialog( 'open' );
				$( '.js-wpv-shortcode-gui-insert' )
					.addClass( 'button-primary' )
					.removeClass( 'button-secondary' )
					.prop( 'disabled', false );

				$('.js-wpv-conditional-shortcode-gui-tabs')
					.tabs()
					.addClass('ui-tabs-vertical ui-helper-clearfix')
					.removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
				$('.js-wpv-conditional-shortcode-gui-tabs, .js-wpv-conditional-shortcode-gui-tabs li').removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');


				self.wpv_conditional_editor = object.codemirror;
				self.wpv_conditional_object = object;
				if ( object.codemirror == '' ) {
					if ( typeof object.ed.canvas !== 'undefined' ) {
						self.wpv_conditional_text = object.ed.canvas.value.substring(object.ed.canvas.selectionStart, object.ed.canvas.selectionEnd);
					} else {
						self.wpv_conditional_text = object.ed.selection.getContent();

					}
				} else {
					self.wpv_conditional_text = WPV_Toolset.CodeMirror_instance[object.codemirror].getSelection();
				}
				self.wpv_conditional_close = object.close_tag;


				/**
				 *
				 */
				self.wpv_conditional_add_row($('#js-wpv-conditionals'));

			}
		});


	};

	$(document).on('click', '.js-wpv-views-conditional-add-term', function(e) {
		self.wpv_conditional_add_row($('#js-wpv-conditionals'));
	});

	/**
	 * bind type
	 */
	$( document ).on( 'click', '#js-wpv-views-conditional-shortcode-gui-dialog-container .js-wpv-shortcode-expression-switcher', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
			thiz_container = thiz.closest( '.js-wpv-shortcode-gui-attribute-wrapper-for-if' ),
			thiz_container_gui = $( '.js-wpv-conditionals-set-with-gui', thiz_container ),
			thiz_container_manual = $( '.js-wpv-conditionals-set-manual', thiz_container ),
			thiz_add_condition_button = $( '.js-wpv-views-conditional-add-term', thiz_container )
		if ( self.views_conditional_use_gui ) {
			thiz.fadeOut( 400 );
			thiz_add_condition_button.fadeOut( 400 );
			thiz_container_gui.fadeOut( 400, function() {
				self.views_conditional_use_gui = false;
				$('#wpv-conditional-custom-expressions')
					.val( self.wpv_conditional_create_if_attribute('multiline') )
					.data( 'edited', false );
				thiz.html( wpv_shortcodes_gui_texts.conditional_enter_conditions_gui ).fadeIn( 400 );
				thiz_container_manual.fadeIn( 400, function() {

				});
			});
		} else {
			/**
			 * check editor if was edited, ask user
			 */
			if ( $('#wpv-conditional-custom-expressions').data( 'edited' ) ) {
				if ( ! confirm( wpv_shortcodes_gui_texts.conditional_switch_alert ) ) {
					return;
				}
			}
			thiz.fadeOut( 400 );
			thiz_container_manual.fadeOut( 400, function() {
				self.views_conditional_use_gui = true;
				thiz.html( wpv_shortcodes_gui_texts.conditional_enter_conditions_manually ).fadeIn( 400 );
				thiz_add_condition_button.fadeIn( 400 );
				thiz_container_gui.fadeIn( 400, function() {

				});
			});
		}
	});

	/**
	 * add wpv-conditional-custom-expressions
	 */
	$(document).on('keyup', '#wpv-conditional-custom-expressions', function() {
		if ( !$(this).data('edited') ) {
			$(this).data('edited', true);
		}
	});


	self.wpv_conditional_add_row = function ( container ) {
		if ( 'unfinished' == typeof WPViews.wpv_conditional_data ) {
			return false;
		}
		html = '<tr class="js-wpv-views-condtional-item">';
		/**
		 * type
		 */
		html += '<td class="js-wpv-views-conditional-origin">';
		html += '<span class="js-wpv-views-condtional-type" style="display:inline-block;width:150px;"><select><option value="">' + WPViews.wpv_conditional_data.labels.select_choose + '</option>';
		$.each( WPViews.wpv_conditional_data.fields, function( key, field ) {
			if ( ! $.isEmptyObject( field.fields ) ) {
				html += '<option value="' + field.slug + '">' + field.label + '</option>';
			}
		});
		html += '</select></span>';
		/**
		 * field
		 */
		html += '<span class="js-wpv-views-condtional-field" style="display:inline-block;width:150px;"><select disabled="disabled">';
		html += '</select></td>';
		html += '</select></span>';
		html += '</td>';
		/**
		 * operator
		 */
		html += '<td class="js-wpv-views-condtional-operator">';
		html += '<select>';
		html += '<option value="eq">=</option>';
		html += '<option value="ne">!=</option>';
		html += '<option value="gt">&gt;</option>';
		html += '<option value="lt">&lt;</option>';
		html += '<option value="gte">&gt;=</option>';
		html += '<option value="lte">&lt;=</option>';
		html += '</select>';
		html += '</td>';
		html += '</select></td>';
		/**
		 * value
		 */
		html += '<td class="js-wpv-views-condtional-value">';
		html += '<input type="text">';
		html += '</td>';
		html += '<td class="js-wpv-views-condtional-connect">';
		html += '<select>';
		html += '<option value="AND">AND</option>';
		html += '<option value="OR">OR</option>';
		html += '</select>';
		html += '</td>';
		html += '</select></td>';
		/**
		 * action
		 */
		html += '<td>';
		html += '<i class="icon-remove fa fa-times wpv-views-condtional-remove js-wpv-views-condtional-remove"></i>';
		html += '</td></tr>';
		$('.js-wpv-views-conditional-body').append(html);
		/**
		 * remove operator for first row
		 */
		self.wpv_conditional_row_remove_trash_from_first();


		return false;
	}

	/**
	 * bind remove
	 */
	$(document).on('click', '.js-wpv-views-condtional-remove', function() {
		var row = $(this).closest('tr');
		$( '.js-wpv-views-condtional-remove', '#js-wpv-conditionals' ).prop( 'disabled', true );
		row.addClass( 'wpv-condition-deleted' );
		row.fadeOut( 400, function() {
			row.remove();
			self.wpv_conditional_row_remove_trash_from_first();
			$( '.js-wpv-views-condtional-remove', '#js-wpv-conditionals' ).prop( 'disabled', false );
		});
	});

	/**
	 * bind type
	 */
	$( document ).on( 'change', '.js-wpv-views-condtional-type select', function() {
		var wpv_type = $( ':selected', $( this ) ).val();
		if ( wpv_type === '' ) {
			$( '.js-wpv-views-condtional-field select', $( this ).closest( 'tr' ) ).html( '' ).prop( 'disabled', true );
			return;
		}
		var html = '';
		$.each( WPViews.wpv_conditional_data.fields[wpv_type].fields, function( key, field ) {
			html += '<option value="' + field.slug + '" ';
			html += 'data-field-type="' + field.type + '" ';
			html += 'data-view-type="' + wpv_type + '" ';
			html += '>' + field.label + '</option>';
		});
		$( '.js-wpv-views-condtional-field select', $( this ).closest('tr') ).html( html ).prop( 'disabled', false );
	});

	/**
	 * remove operator for first row
	 */
	self.wpv_conditional_row_remove_trash_from_first = function(container) {
		if ( $('.js-wpv-views-condtional-item').length == 1) {
			$('.js-wpv-views-condtional-remove').hide();
		} else {
			$('.js-wpv-views-condtional-remove').show();
		}
		$('.js-wpv-views-conditional-body .js-wpv-views-condtional-item:first-child .js-wpv-views-condtional-connect', container).html('&nbsp;');
	}

	$( document ).on( 'change input cut paste', '#wpv-conditional-extra-settings .js-wpv-add-item-settings-form-newname', function() {
		var thiz = $( this ),
			thiz_form = thiz.closest( 'form' ),
			thiz_button = thiz_form.find( '.js-wpv-add-item-settings-form-button' );
		$( '.js-wpv-cs-error, .js-wpv-cs-dup, .js-wpv-cs-ajaxfail', thiz_form ).hide();
		if ( thiz.val() == '' ) {
			thiz_button.prop( 'disabled', true );
		} else {
			thiz_button.prop( 'disabled', false );
		}
	});

	$( document ).on( 'click', '.js-wpv-add-item-settings-form-button', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
			shortcode_pattern,
			thiz_append,
			thiz_kind,
			parent_form = thiz.closest( '.js-wpv-add-item-settings-form' ),
			parent_container = thiz.closest( '.wpv-shortcode-gui-attribute-wrapper' ),
			newitem = $( '.js-wpv-add-item-settings-form-newname', parent_form ),
			spinnerContainer = $('<div class="wpv-spinner ajax-loader">'),
			data = {
				csaction: 'add',
				cstarget: newitem.val(),
				wpnonce: $( '#wpv_custom_conditional_extra_settings' ).val()
			};
		if ( thiz.hasClass( 'js-wpv-custom-inner-shortcodes-add' ) ) {
			shortcode_pattern = /^[a-z0-9\-\_]+$/;
			data.action = 'wpv_update_custom_inner_shortcodes';
			thiz_append = '<li class="js-' + newitem.val() + '-item"><span class="">[' + newitem.val() + ']</span></li>';
			thiz_kind = 'custom-shortcodes';
		} else if ( thiz.hasClass( 'js-wpv-custom-conditional-functions-add' ) ) {
			shortcode_pattern = /^[a-zA-Z0-9\:\-\_]+$/;
			data.action = 'wpv_update_custom_conditional_functions';
			thiz_append = '<li class="js-' + newitem.val() + '-item"><span class="">' + newitem.val() + '</span></li>';
			thiz_kind = 'custom-functions';
		} else {
			return;
		}
		$( '.js-wpv-cs-error, .js-wpv-cs-dup, .js-wpv-cs-ajaxfail', parent_form ).hide();
		if ( shortcode_pattern.test( newitem.val() ) == false ) {
			$( '.js-wpv-cs-error', parent_form ).show();
		} else if ( $( '.js-' + newitem.val() + '-item', parent_container ).length > 0 ) {
			$( '.js-wpv-cs-dup', parent_form ).show();
		} else {
			spinnerContainer.insertAfter( thiz ).show();
			thiz
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );

			$.ajax({
				async: false,
				dataType: "json",
				type: "POST",
				url: wpv_shortcodes_gui_texts.ajaxurl,
				data: data,
				success: function( response ) {
					if ( response.success ) {
						$( '.js-wpv-add-item-settings-list', parent_container )
							.append( thiz_append );
						$( document ).trigger( 'js_event_wpv_extra_conditional_registered', [ { kind: thiz_kind, value: newitem.val() } ] );
						newitem.val('');
					} else {
						$( '.js-wpv-cs-ajaxfail', parent_form ).show();
						console.log( "Error: AJAX returned ", response );
					}
				},
				error: function (ajaxContext) {
					$( '.js-wpv-cs-ajaxfail', parent_form ).show();
					console.log( "Error: ", ajaxContext.responseText );
				},
				complete: function() {
					spinnerContainer.remove();
				}
			});
		}
		return false;
	});

	$( document ).on( 'submit', '.js-wpv-add-item-settings-form' , function( e ) {
		e.preventDefault();
		var thiz = $( this );
		$( '.js-wpv-add-item-settings-form-button', thiz ).click();
		return false;
	});

	$( document ).on( 'js_event_wpv_extra_conditional_registered', function( event, data ) {
		var html = '',
			type_select = $( '.js-wpv-views-condtional-type select' );
		switch ( data.kind ) {
			case 'custom-shortcodes':
				if ( $.isEmptyObject( WPViews.wpv_conditional_data.fields['custom-shortcodes'].fields ) ) {
					WPViews.wpv_conditional_data.fields['custom-shortcodes'].fields = {};
				}
				WPViews.wpv_conditional_data.fields['custom-shortcodes'].fields[data.value] = {
					label: data.value,
					slug: '\'[' + data.value + ']\'',
					type: 'text'
				};
				break;
			case 'custom-functions':
				if ( $.isEmptyObject( WPViews.wpv_conditional_data.fields['custom-functions'].fields ) ) {
					WPViews.wpv_conditional_data.fields['custom-functions'].fields = {};
				}
				WPViews.wpv_conditional_data.fields['custom-functions'].fields[data.value] = {
					label: data.value,
					slug: data.value + '()',
					type: 'text'
				};
				break;
		}
		html += '<option value="">' + WPViews.wpv_conditional_data.labels.select_choose + '</option>';
		$.each( WPViews.wpv_conditional_data.fields, function( key, field ) {
			if ( ! $.isEmptyObject( field.fields ) ) {
				html += '<option value="' + field.slug + '">' + field.label + '</option>';
			}
		});
		type_select.html( html );
		type_select.trigger( 'change' );
	});

	//-----------------------------------------
	// Generic shortcodes API GUI
	//-----------------------------------------

	/**
	 * Insert a shortcode into an editor when there are no attributes to set.
	 *
	 * Used in Basic taxonomy shortcodes, for example.
	 *
	 * todo explain parameters
	 * @param shortcode_name
	 * @param shortcode_to_insert
	 *
	 * @since 1.12
	 */

	self.insert_shortcode_with_no_attributes = function( shortcode_name, shortcode_to_insert ) {
		if (
			_.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set )
			&& self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog( "isOpen" )
		) {
			self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog('close');
		}
		if ( self.shortcode_gui_insert == 'insert' ) {
			window.icl_editor.insert( shortcode_to_insert );
		}
		$( document ).trigger( 'js_event_wpv_shortcode_inserted', [ shortcode_name, '', {}, shortcode_to_insert ] );
	};

	/**
	 * Display a dialog for inserting a specific Views shortcode.
	 *
	 * todo explain parameters
	 * @param shortcode
	 * @param {string} title Dialog title.
	 * @param params
	 * @param nonce
	 * @param object
	 *
	 * @since 1.9
	 */
	self.wpv_insert_popup = function( shortcode, title, params, nonce, object ) {

		//
		// Build AJAX url for displaying the dialog
		//
		var url = wpv_shortcodes_gui_texts.ajaxurl,
			url_extra_data = '',
			data_for_events = {};

		url += '&_wpnonce=' + nonce;
		url += '&action=wpv_shortcode_gui_dialog_create';
		url += '&shortcode=' + shortcode;
		url += '&post_id=' + parseInt( $( object ).data( 'post-id' ) );

		url_extra_data = self.filter_dialog_ajax_data( shortcode );

		url += url_extra_data;

		data_for_events.shortcode = shortcode;
		data_for_events.title = title;
		data_for_events.params = params;
		data_for_events.nonce = nonce;
		data_for_events.object = object;
        data_for_events.dialog = this;
		$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_triggered', [ data_for_events ] );
		
		// Show the "empty" dialog with a spinner while loading dialog content
		self.dialog_insert_shortcode.dialog('open').dialog({
			title:		title,
			maxHeight:	self.calculate_dialog_maxHeight(),
			maxWidth:	self.calculate_dialog_maxWidth(),
			position:	{
				my:			"center top+50",
				at:			"center top",
				of:			window,
				collision:	"none"
			}
		});

		self.manage_dialog_button_labels();

		self.dialog_insert_shortcode.html( self.shortcodeDialogSpinnerContent );

		//
		// Do AJAX call
		//
		$.ajax({
			url: url,
			success: function( data ) {
				$( 'body' ).addClass( 'modal-open' );
				/**
				 * Load dialog data
				 */
				self.dialog_insert_shortcode.html(data);
				$( '.js-wpv-shortcode-gui-insert' )
					.addClass( 'button-primary' )
					.removeClass( 'button-secondary' )
					.prop( 'disabled', false );

				/**
				 * Init dialog tabs
				 */
				$('.js-wpv-shortcode-gui-tabs')
					.tabs({
						beforeActivate: function( event, ui ) {
							var valid = self.validate_shortcode_attributes( $( '#js-wpv-shortcode-gui-dialog-container' ), ui.oldPanel, $( '#js-wpv-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) );
							if ( ! valid ) {
								event.preventDefault();
								ui.oldTab.focus().addClass( 'wpv-shortcode-gui-tabs-incomplete' );
								setTimeout( function() {
									ui.oldTab.removeClass( 'wpv-shortcode-gui-tabs-incomplete' );
								}, 1000 );
							}
						}
					})
					.addClass('ui-tabs-vertical ui-helper-clearfix')
					.removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
				$('#js-wpv-shortcode-gui-dialog-tabs ul, #js-wpv-shortcode-gui-dialog-tabs li').removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');

				/**
				 * After open dialog
				 */
				self.after_open_dialog(shortcode, title, params, nonce, object);

				/**
				 * Custom combo management
				 */
				self.custom_combo_management();

				$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_opened', [ data_for_events ] );
			}
		});
	};

	/**
	 * Custom combo management
	 */
	self.custom_combo_management = function () {
		$( '.js-wpv-shortcode-gui-attribute-custom-combo').each( function() {
			var combo_parent = $( this ).closest( '.js-wpv-shortcode-gui-attribute-wrapper' ),
				combo_target = $( '.js-wpv-shortcode-gui-attribute-custom-combo-target', combo_parent );
			if ( $( '[value=custom-combo]:checked', combo_parent ).length) {
				$combo_target.show();
			}
			$( '[type=radio]', combo_parent ).on( 'change', function() {
				var thiz_radio = $( this );
				if (
					thiz_radio.is( ':checked' )
					&& 'custom-combo' == thiz_radio.val()
				) {
					combo_target.slideDown( 'fast' );
				} else {
					combo_target.slideUp( 'fast' );
				}
			});
		});
	}

	/**
	 * filter_dialog_ajax_data
	 *
	 * Filter the empty extra string added to the request to create the dialog GUI, so we can pass additional parameters for some shortcodes.
	 *
	 * @param shortcode The shortcode to which the dialog is being created.
	 *
	 * @return ajax_extra_data
	 *
	 * @since 1.9
	 */

	self.filter_dialog_ajax_data = function( shortcode ) {
		var ajax_extra_data = '';
		switch( shortcode ) {
			case 'wpv-post-body':
				// Check for excluded content templates list via the filter.
				var excluded_cts = [];
				excluded_cts = Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-gui-wpv_post_body-exclude-content-template', excluded_cts );

				// Prepare a form array of all excluded CT IDs, to transmit via URL
				if( Array.isArray( excluded_cts ) && excluded_cts.length > 0 ) {
					for( var i = 0; i < excluded_cts.length; i++ ) {
						ajax_extra_data += '&wpv_suggest_wpv_post_body_view_template_exclude[]=' + excluded_cts[i];
					}
				}

				break;
		}
		return ajax_extra_data;
	};


	/**
	 * after_open_dialog
	 *
	 * @since 1.9
	 */
	self.after_open_dialog = function( shortcode, title, params, nonce, object ) {
		self.manage_fixed_initial_params( params );
		self.manage_special_cases( shortcode );
		self.manage_suggest_cache();
	};

	/**
	 * manage_dialog_button_labels
	 *
	 * Adjusts the dialog button labels for usage on Fields and Views or Loop Wizard scenarios.
	 *
	 * @since 1.9
	 */

	self.manage_dialog_button_labels = function() {
		switch ( self.shortcode_gui_insert ) {
			case 'save':
				$( '.js-wpv-shortcode-gui-button-close .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_cancel );
				$( '.js-wpv-shortcode-gui-button-insert .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_save_settings );
				break;
			case 'create':
				$( '.js-wpv-shortcode-gui-button-close .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_cancel );
				$( '.js-wpv-shortcode-gui-button-insert .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_create_shortcode );
				break;
			case 'insert':
			default:
				$( '.js-wpv-shortcode-gui-button-close .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_close );
				$( '.js-wpv-shortcode-gui-button-insert .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_insert_shortcode );
				break;
		}
	};

	/**
	 * manage_fixed_initial_params
	 *
	 * @since 1.9
	 */

	self.manage_fixed_initial_params = function( params ) {
		for ( var item in params ) {
			$( '.wpv-dialog' ).prepend( '<span class="wpv-shortcode-gui-attribute-wrapper js-wpv-shortcode-gui-attribute-wrapper" data-attribute="' + item + '" data-type="param"><input type="hidden" name="' + item + '" value="' + params[ item ].value + '" disabled="disabled" /></span>' );
		}
	};

	/**
	 * manage_special_cases
	 *
	 * @since 1.9
	 */

	self.manage_special_cases = function( shortcode ) {
		switch ( shortcode ) {
			case 'wpv-post-author':
				self.manage_wpv_post_author_format_show_relation();
				break;
			case 'wpv-post-taxonomy':
				self.manage_wpv_post_taxonomy_format_show_relation();
				break;
			case 'wpv-post-featured-image':
				self.manage_wpv_post_featured_image_output_show_class();
				self.manage_wpv_post_featured_image_resize_show_relation();
				self.manage_wpv_post_featured_image_crop_show_relation();
				break;
		}
	};

	/**
	 * manage_suggest_cache
	 *
	 * Populate suggest fields from cache if available
	 *
	 * @since 1.9
	 */

	self.manage_suggest_cache = function() {
		$( '.js-wpv-shortcode-gui-suggest' ).each( function() {
			var thiz_inner = $( this ),
				action_inner = '';
			if ( thiz_inner.data('action') != '' ) {
				action_inner = thiz_inner.data('action');
				if ( self.suggest_cache.hasOwnProperty( action_inner ) ) {
					thiz_inner
						.val( self.suggest_cache[action_inner] )
						.trigger( 'change' );
				}
			}
		});
	};

	/**
	 * Init suggest on suggest attributes
	 *
	 * @since 1.9
	 */

	$( document ).on( 'focus', '.js-wpv-shortcode-gui-suggest:not(.js-wpv-shortcode-gui-suggest-inited)', function() {
		var thiz = $( this ),
			action = '';
		if ( thiz.data('action') != '' ) {
			action = thiz.data('action');
			ajax_extra_data = self.filter_suggest_ajax_data( action );
			thiz
				.addClass( 'js-wpv-shortcode-gui-suggest-inited' )
				.suggest( wpv_shortcodes_gui_texts.ajaxurl + '&action=' + action + ajax_extra_data, {
					resultsClass: 'ac_results wpv-suggest-results',
					onSelect: function() {
						self.suggest_cache[action] = this.value;
					}
				});
		}
	});

	/**
	 * filter_suggest_ajax_data
	 *
	 * Filter the empty extra string added to the suggest request, so we can pass additional parameters for some shortcodes.
	 *
	 * @param action The suggest action to perform.
	 *
	 * @return ajax_extra_data
	 *
	 * @since 1.9
	 */

	self.filter_suggest_ajax_data = function( action ) {
		var ajax_extra_data = '';
		switch( action ) {
			case 'wpv_suggest_wpv_post_body_view_template':
				if (
					typeof WPViews.ct_edit_screen != 'undefined'
					&& typeof WPViews.ct_edit_screen.ct_data != 'undefined'
					&& typeof WPViews.ct_edit_screen.ct_data.id != 'undefined'
				) {
					ajax_extra_data = '&wpv_suggest_wpv_post_body_view_template_exclude=' + WPViews.ct_edit_screen.ct_data.id;
				}
				break;
		}
		return ajax_extra_data;
	};

	/**
	 * Manage item selector GUI
	 *
	 * @since 1.9
	 */

	$( document ).on( 'change', 'input.js-wpv-shortcode-gui-item-selector', function() {
		var thiz = $( this ),
			checked = thiz.val();
		$('.js-wpv-shortcode-gui-item-selector-has-related').each( function() {
			var thiz_inner = $( this );
			if ( $( 'input.js-wpv-shortcode-gui-item-selector:checked', thiz_inner ).val() == checked ) {
				$( '.js-wpv-shortcode-gui-item-selector-is-related', thiz_inner ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-item-selector-is-related', thiz_inner ).slideUp( 'fast' );
			}
		});
	});

	/**
	 * Manage placeholders: should be removed when focusing on a textfield, added back on blur
	 *
	 * @since 1.9
	 */

	$( document )
		.on( 'focus', '.js-wpv-shortcode-gui-attribute-has-placeholder, .js-wpv-has-placeholder', function() {
			var thiz = $( this );
			thiz.attr( 'placeholder', '' );
		})
		.on( 'blur', '.js-wpv-shortcode-gui-attribute-has-placeholder, .js-wpv-has-placeholder', function() {
			var thiz = $( this );
			if ( thiz.data( 'placeholder' ) ) {
				thiz.attr( 'placeholder', thiz.data( 'placeholder' ) );
			}
		});

	/**
	 * validate_shortcode_attributes
	 *
	 * Validate method
	 *
	 * @since 1.9
	 */

	self.validate_shortcode_attributes = function( container, evaluate_container, error_container ) {
		self.clear_validate_messages( container );
		var valid = true;
		valid = self.manage_required_attributes( evaluate_container, error_container );
		evaluate_container.find( 'input:text' ).each( function() {
			var thiz = $( this ),
				thiz_val = thiz.val(),
				thiz_type = thiz.data( 'type' ),
				thiz_message = '',
				thiz_valid = true;
			if ( ! thiz.hasClass( 'js-toolset-shortcode-gui-invalid-attr' ) ) {
				switch ( thiz_type ) {
					case 'number':
						if (
							self.numeric_natural_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_number_invalid;
						}
						break;
					case 'numberextended':
						if (
							self.numeric_natural_extended_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_number_invalid;
						}
						break;
					case 'numberlist':
						if (
							self.numeric_natural_list_pattern.test( thiz_val.replace(/\s+/g, '') ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_numberlist_invalid;
						}
						break;
					case 'year':
						if (
							self.year_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_year_invalid;
						}
						break;
					case 'month':
						if (
							self.month_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_month_invalid;
						}
						break;
					case 'week':
						if (
							self.week_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_week_invalid;
						}
						break;
					case 'day':
						if (
							self.day_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_day_invalid;
						}
						break;
					case 'hour':
						if (
							self.hour_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_hour_invalid;
						}
						break;
					case 'minute':
						if (
							self.minute_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_minute_invalid;
						}
						break;
					case 'second':
						if (
							self.second_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_second_invalid;
						}
						break;
					case 'dayofyear':
						if (
							self.dayofyear_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_dayofyear_invalid;
						}
						break;
					case 'dayofweek':
						if (
							self.dayofweek_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_dayofweek_invalid;
						}
						break;
					case 'url':
						if (
							self.url_patern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_url_invalid;
						}
						break;
				}
				if ( ! thiz_valid ) {
					valid = false;
					error_container
						.wpvToolsetMessage({
							text: thiz_message,
							type: 'error',
							inline: false,
							stay: true
						});
					// Hack to allow more than one error message per filter
					error_container
						.data( 'message-box', null )
						.data( 'has_message', false );
				}
			}
		});
		// Special case: item selector tab
		if (
			$( '.js-wpv-shortcode-gui-item-selector:checked', evaluate_container ).length > 0
			&& 'item_id' == $( '.js-wpv-shortcode-gui-item-selector:checked', evaluate_container ).val()
		) {
			var item_selection = $( '[name=specific_item_id]', evaluate_container ),
				item_selection_id = item_selection.val(),
				item_selection_valid = true,
				item_selection_message = '';
			if ( '' == item_selection_id ) {
				item_selection_valid = false;
				item_selection.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				item_selection_message = wpv_shortcodes_gui_texts.attr_empty;
			} else if ( self.numeric_natural_pattern.test( item_selection_id ) == false ) {
				item_selection_valid = false;
				item_selection.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				item_selection_message = wpv_shortcodes_gui_texts.attr_number_invalid;
			}
			if ( ! item_selection_valid ) {
				valid = false;
				error_container
					.wpvToolsetMessage({
						text: item_selection_message,
						type: 'error',
						inline: false,
						stay: true
					});
				// Hack to allow more than one error message per filter
				error_container
					.data( 'message-box', null )
					.data( 'has_message', false );
			}
		}
		return valid;
	};

	$( document ).on( 'change keyup input cut paste', '.js-wpv-shortcode-gui-dialog-container input, .js-wpv-shortcode-gui-dialog-container select', function() {
		var thiz = $( this );
		thiz.removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
		thiz
			.closest( '.js-wpv-shortcode-gui-dialog-container' )
			.find('.toolset-alert-error').not( '.js-wpv-permanent-alert-error' )
			.each( function() {
				$( this ).remove();
			});
	});

	self.clear_validate_messages = function( container ) {
		container
			.find('.toolset-alert-error').not( '.js-wpv-permanent-alert-error' )
			.each( function() {
				$( this ).remove();
			});
		container
			.find( '.js-toolset-shortcode-gui-invalid-attr' )
			.removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
	};

	/**
	 * manage_required_attributes
	 *
	 * @since 1.9
	 */

	self.manage_required_attributes = function( evaluate_container, error_container ) {
		var valid = true,
			error_container = $( '#js-wpv-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' );
		evaluate_container.find( '.js-shortcode-gui-field.js-wpv-shortcode-gui-required' ).each( function() {
			var thiz = $( this ),
				thiz_valid = true,
				thiz_parent = thiz.closest('.js-wpv-shortcode-gui-attribute-custom-combo');
			if ( thiz_parent.length ) {
				if (
					$( '[value=custom-combo]:checked', thiz_parent ).length
					&& thiz.val() == ''
				) {
					thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
					thiz_valid = false;
				}
			} else {
				if ( thiz.val() == '' ) {
					thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
					thiz_valid = false;
				}
			}
			if ( ! thiz_valid ) {
				valid = false;
				error_container
					.wpvToolsetMessage({
						text: wpv_shortcodes_gui_texts.attr_empty,
						type: 'error',
						inline: false,
						stay: true
					});
				// Hack to allow more than one error message per filter
				error_container
					.data( 'message-box', null )
					.data( 'has_message', false );
			}
		});
		return valid;
	};

	/**
	 * wpv_insert_shortcode
	 *
	 * Insert shortcode to active editor
	 *
	 * @since 1.9
	 */

	self.wpv_insert_shortcode = function() {

		var shortcode_name = $('.js-wpv-shortcode-gui-shortcode-name').val(),
			shortcode_attribute_key,
			shortcode_attribute_value,
			shortcode_attribute_default_value,
			shortcode_attribute_string = '',
			shortcode_attribute_values = {},
			shortcode_content = '',
			shortcode_to_insert = '',
			shortcode_valid = self.validate_shortcode_attributes( $( '#js-wpv-shortcode-gui-dialog-container' ), $( '#js-wpv-shortcode-gui-dialog-container' ), $( '#js-wpv-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) );
		if ( ! shortcode_valid ) {
			return;
		}
		$( '.js-wpv-shortcode-gui-attribute-wrapper', '#js-wpv-shortcode-gui-dialog-container' ).each( function() {
			var thiz_attribute_wrapper = $( this ),
				shortcode_attribute_key = thiz_attribute_wrapper.data('attribute');
			switch ( thiz_attribute_wrapper.data('type') ) {
				case 'post':
				case 'user':
					shortcode_attribute_value = $( '.js-wpv-shortcode-gui-item-selector:checked', thiz_attribute_wrapper ).val();
					switch( shortcode_attribute_value ) {
						case 'current':
							shortcode_attribute_value = false;
							break;
						case 'parent':
							if ( shortcode_attribute_value ) {
								shortcode_attribute_value = '$' + shortcode_attribute_value;
							}
							break;
						case 'related':
							shortcode_attribute_value = $( '[name=related_post]:checked', thiz_attribute_wrapper ).val();
							if ( shortcode_attribute_value ) {
								shortcode_attribute_value = '$' + shortcode_attribute_value;
							}
							break;
						case 'item_id':
							shortcode_attribute_value = $( '[name=specific_item_id]', thiz_attribute_wrapper ).val();
						default:
					}
					break;
				case 'select':
					shortcode_attribute_value = $('option:checked', thiz_attribute_wrapper ).val();
					break;
				case 'radio':
				case 'radiohtml':
					shortcode_attribute_value = $('input:checked', thiz_attribute_wrapper ).val();
					if ( 'custom-combo' == shortcode_attribute_value ) {
						shortcode_attribute_value = $('.js-wpv-shortcode-gui-attribute-custom-combo-target', $('input:checked', thiz_attribute_wrapper ).closest('.js-wpv-shortcode-gui-attribute-custom-combo')).val();
					}
					break;
				case 'checkbox':
					shortcode_attribute_value = $('input:checked', thiz_attribute_wrapper ).val();
					break;
				default:
					shortcode_attribute_value = $('input', thiz_attribute_wrapper ).val();
			}

			shortcode_attribute_default_value = thiz_attribute_wrapper.data('default');
			/**
			 * Fix true/false from data attribute for shortcode_attribute_default_value
			 */
			if ( 'boolean' == typeof shortcode_attribute_default_value ) {
				shortcode_attribute_default_value = shortcode_attribute_default_value ? 'true' :'false';
			}
			/**
			 * Filter value
			 */
			shortcode_attribute_value = self.filter_computed_attribute_value( shortcode_name, shortcode_attribute_key, shortcode_attribute_value );
			/**
			 * Add to the shortcode_attribute_values object
			 */
			if (
				shortcode_attribute_value
				&& shortcode_attribute_value != shortcode_attribute_default_value
			) {
				shortcode_attribute_values[shortcode_attribute_key] = shortcode_attribute_value;
			}
		});
		// Filter pairs key => value
		shortcode_attribute_values = self.filter_computed_attribute_pairs( shortcode_name, shortcode_attribute_values );
		// Compose the shortcode_attribute_string string
		_.each( shortcode_attribute_values, function( value, key ) {
			if ( value ) {
				shortcode_attribute_string += " " + key + "='" + value + "'";
			}
		});
		shortcode_to_insert = '[' + shortcode_name + shortcode_attribute_string + ']';
		/**
		 * Shortcodes with content
		 */
		if ( $( '.js-wpv-shortcode-gui-content' ).length > 0 ) {
			shortcode_content = $( '.js-wpv-shortcode-gui-content' ).val();
			/**
			 * Filter shortcode content
			 */
			shortcode_content = self.filter_computed_content( shortcode_name, shortcode_content, shortcode_attribute_values );
			shortcode_to_insert += shortcode_content;
			shortcode_to_insert += '[/' + shortcode_name + ']';
		}
		/**
		 * Close, insert if needed and fire custom event
		 */
		self.dialog_insert_shortcode.dialog('close');
		if ( self.shortcode_gui_insert == 'insert' ) {
			window.icl_editor.insert( shortcode_to_insert );
		}
		$( document ).trigger( 'js_event_wpv_shortcode_inserted', [ shortcode_name, shortcode_content, shortcode_attribute_values, shortcode_to_insert ] );

	};

	$( document ).on( 'js_event_wpv_shortcode_inserted', function() {
		self.shortcode_gui_insert_count = self.shortcode_gui_insert_count + 1;
	});

	/**
	 * wpv_insert_view_conditional_shortcode
	 *
	 * Insert Views conditional shortcode to active editor
	 *
	 * @since 1.10
	 */

	self.wpv_insert_view_conditional_shortcode = function() {
		var shortcode_name = $('.js-wpv-views-conditional-shortcode-gui-dialog-name').val(),
			shortcode_attribute_key,
			shortcode_attribute_value,
			shortcode_attribute_default_value,
			shortcode_attribute_string = '',
			shortcode_attribute_values = {},
			shortcode_content = '',
			shortcode_to_insert = '',
			shortcode_valid = self.validate_shortcode_attributes( $( '#js-wpv-views-conditional-shortcode-gui-dialog-container' ), $( '#js-wpv-views-conditional-shortcode-gui-dialog-container' ), $( '#js-wpv-views-conditional-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) );
		if ( ! shortcode_valid ) {
			return;
		}
		$( '.js-wpv-shortcode-gui-attribute-wrapper', '#js-wpv-views-conditional-shortcode-gui-dialog-container' ).each( function() {
			var thiz_attribute_wrapper = $( this ),
				shortcode_attribute_key = thiz_attribute_wrapper.data('attribute');
			switch ( thiz_attribute_wrapper.data('type') ) {
				case 'radio':
					shortcode_attribute_value = $('input:checked', thiz_attribute_wrapper ).val();
					if ( 'custom-combo' == shortcode_attribute_value ) {
						shortcode_attribute_value = $('.js-wpv-shortcode-gui-attribute-custom-combo-target', $('input:checked', thiz_attribute_wrapper ).closest('.js-wpv-shortcode-gui-attribute-custom-combo')).val();
					}
					break;
				default:
					shortcode_attribute_value = $('input', thiz_attribute_wrapper ).val();
			}

			shortcode_attribute_default_value = thiz_attribute_wrapper.data('default');
			/**
			 * Fix true/false from data attribute for shortcode_attribute_default_value
			 */
			if ( 'boolean' == typeof shortcode_attribute_default_value ) {
				shortcode_attribute_default_value = shortcode_attribute_default_value ? 'true' :'false';
			}
			/**
			 * Filter value
			 */
			shortcode_attribute_value = self.filter_computed_attribute_value( shortcode_name, shortcode_attribute_key, shortcode_attribute_value );
			/**
			 * Add to the shortcode_attribute_string string
			 */
			if (
				shortcode_attribute_value
				&& shortcode_attribute_value != shortcode_attribute_default_value
			) {
				shortcode_attribute_string += ' ' + shortcode_attribute_key + '="' + shortcode_attribute_value + '"';
				shortcode_attribute_values[shortcode_attribute_key] = shortcode_attribute_value;
			}
		});

		shortcode_to_insert = '[' + shortcode_name + shortcode_attribute_string + ']';
		/**
		 * Shortcodes with content
		 */
		if ( $( '.js-wpv-shortcode-conditional-gui-content' ).length > 0 ) {
			shortcode_content = $( '.js-wpv-shortcode-conditional-gui-content' ).val();
			/**
			 * Filter shortcode content
			 */
			shortcode_content = self.filter_computed_content( shortcode_name, shortcode_content, shortcode_attribute_values );
			var selected_text = self.wpv_conditional_text;
			if ( self.wpv_conditional_close ) {
				shortcode_to_insert += selected_text;
				shortcode_to_insert += '[/' + shortcode_name + ']';
				self.views_conditional_qtags_opened = false;
			} else {
				self.views_conditional_qtags_opened = true;
			}
		}
		/**
		 * Close, insert if needed and fire custom event
		 */
		self.dialog_insert_views_conditional.dialog('close');
		if ( self.shortcode_gui_insert == 'insert' ) {
			window.icl_editor.insert( shortcode_to_insert );
		}
		$( document ).trigger( 'js_event_wpv_shortcode_inserted', [ shortcode_name, shortcode_content, shortcode_attribute_values, shortcode_to_insert ] );
	};

	//--------------------------------
	// Special cases
	//--------------------------------

	/**
	 * wpv-post-author management
	 * Handle the change in format that shows/hides the show attribute
	 *
	 * @since 1.9
	 */

	$( document ).on( 'change', '#wpv-post-author-format .js-shortcode-gui-field', function() {
		self.manage_wpv_post_author_format_show_relation();
	});

	self.manage_wpv_post_author_format_show_relation = function() {
		if ( $( '#wpv-post-author-format' ).length ) {
			if ( 'meta' == $( '.js-shortcode-gui-field:checked', '#wpv-post-author-format' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-meta', '#wpv-post-author-display-options' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-meta', '#wpv-post-author-display-options' ).hide();
			}
		}
	};

	/**
	 * wpv-post-taxonomy management
	 * Handle the change in format that shows/hides the show attribute
	 *
	 * @since 1.9
	 */

	$( document ).on( 'change', '#wpv-post-taxonomy-format .js-shortcode-gui-field', function() {
		self.manage_wpv_post_taxonomy_format_show_relation();
	});

	self.manage_wpv_post_taxonomy_format_show_relation = function() {
		if ( $( '#wpv-post-taxonomy-format' ).length ) {
			if ( 'link' == $( '.js-shortcode-gui-field:checked', '#wpv-post-taxonomy-format' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-show', '#wpv-post-taxonomy-display-options' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-show', '#wpv-post-taxonomy-display-options' ).slideUp( 'fast' );
			}
		}
	};

	/**
	 * wpv-post-featured-image management
	 * Handle the change in output that shows/hides the class attribute
	 *
	 * @since 1.9
	 */

	$( document ).on( 'change', '#wpv-post-featured-image-output.js-shortcode-gui-field', function() {
		self.manage_wpv_post_featured_image_output_show_class();
	});

	self.manage_wpv_post_featured_image_output_show_class = function() {
		if ( $( '#wpv-post-featured-image-output' ).length ) {
			if ( 'img' == $( '#wpv-post-featured-image-output.js-shortcode-gui-field' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-class', '#wpv-post-featured-image-display-options' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-class', '#wpv-post-featured-image-display-options' ).slideUp( 'fast' );
			}
		}
	};

	/**
	 * wpv-post-featured-image management
	 * Handle the change in UI to show/hide attributes for custom image resizing and cropping
	 *
	 * @since 2.2
	 */
	$( document ).on( 'change', '#wpv-post-featured-image-size.js-shortcode-gui-field', function() {
		self.manage_wpv_post_featured_image_resize_show_relation();
	});

	self.manage_wpv_post_featured_image_resize_show_relation = function() {
		if( 'custom' == $( '#wpv-post-featured-image-size.js-shortcode-gui-field' ).val() ) {
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-width' ).slideDown( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-height' ).slideDown( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop' ).slideDown( 'fast' );

			self.manage_wpv_post_featured_image_crop_show_relation();
		} else {
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-width' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-height' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_horizontal' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_vertical' ).slideUp( 'fast' );
		}
	};

	/**
	 * wpv-post-featured-image management
	 * Handle the change in UI to show/hide attributes for crop positions
	 *
	 * @since 2.2
	 */
	$( document ).on( 'change', '#wpv-post-featured-image-crop .js-shortcode-gui-field', function() {
		self.manage_wpv_post_featured_image_crop_show_relation();
	});

	self.manage_wpv_post_featured_image_crop_show_relation = function() {
		if ( $( '#wpv-post-featured-image-crop' ).length ) {
			if( 'true' == $( '.js-shortcode-gui-field:checked', '#wpv-post-featured-image-crop' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_horizontal' ).slideDown( 'fast' );
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_vertical' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_horizontal' ).slideUp( 'fast' );
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_vertical' ).slideUp( 'fast' );
			}
		}
	};


	/**
	 * filter_computed_attribute_value
	 *
	 * @since 1.9
	 */

	self.filter_computed_attribute_value = function( shortcode, attribute, value ) {
		switch ( shortcode ) {
			case 'wpv-post-author':
				if (
					'meta' == attribute
					&& 'meta' != $( '.js-shortcode-gui-field:checked', '#wpv-post-author-format' ).val()
				) {
					value = false;
				}
				break;
			case 'wpv-post-taxonomy':
				if (
					'show' == attribute
					&& 'link' != $( '.js-shortcode-gui-field:checked', '#wpv-post-taxonomy-format' ).val()
				) {
					value = false;
				}
				break;
			case 'wpv-post-featured-image':
				if (
					'class' == attribute
					&& 'img' != $( '#wpv-post-featured-image-output.js-shortcode-gui-field' ).val()
				) {
					value = false;
				}
				break;
			case 'wpv-conditional':
				switch( attribute ) {
					case 'if':
						if ( self.views_conditional_use_gui ) {
							value = self.wpv_conditional_create_if_attribute( 'singleline' );
						} else {
							value = $('#wpv-conditional-custom-expressions').val();
						}
						if ( value == '' ) {
							value = "('1' eq '1')";
						}
						break;
					/*
					 case 'custom-expressions':
					 value = false;
					 */
				}
				break;
		}
		return value;
	};

	self.filter_computed_attribute_pairs = function( shortcode_name, shortcode_attribute_values ) {
		if ( shortcode_name in self.shortcode_gui_computed_attribute_pairs_filters ) {
			var filter_callback_func = self.shortcode_gui_computed_attribute_pairs_filters[ shortcode_name ];
			if ( typeof filter_callback_func == "function" ) {
				shortcode_attribute_values = filter_callback_func( shortcode_attribute_values );
			}
		}
		return shortcode_attribute_values;
	};

	/**
	 * wpv_conditional_create_if_attribute
	 *
	 * @since 1.9
	 */
	self.wpv_conditional_create_if_attribute = function( mode ) {
		var value = '';
		$('.js-wpv-views-condtional-item').each( function() {
			var tr = $(this);
			if ( $('.js-wpv-views-condtional-field :selected', tr).val() ) {
				if ( value ) {
					if ( 'multiline' == mode ) {
						value += "\n";
					}
					value += ' '+$('.js-wpv-views-condtional-connect :checked', tr).val()+' ';
					if ( 'multiline' == mode ) {
						value += "\n";
					}
				}
				value += '( ';
				value += $('.js-wpv-views-condtional-field :selected', tr).val();
				value += ' ';
				value += $('.js-wpv-views-condtional-operator :selected', tr).val();
				value += ' \'';
				value += $('.js-wpv-views-condtional-value input', tr).val();
				value += '\' ';
				value += ')';
			}
		});
		return value;
	}

	/**
	 * filter_computed_content
	 *
	 * @since 1.9
	 */

	self.filter_computed_content = function( shortcode, content, values ) {
		switch ( shortcode ) {
			case 'wpv-for-each':
				if ( values.hasOwnProperty( 'field' ) ) {
					content = '[wpv-post-field name="' + values.field + '"]';
				}
				break;
		}
		return content;
	};

	/**
	 * load_post_field_section_on_demand
	 *
	 * Load the Post field section on the shortcodes GUI on demand
	 * Used to load non-Types custom fields only when needed
	 *
	 * @since 1.10
	 */

	self.load_post_field_section_on_demand = function( event, object ) {
		event.stopPropagation();
		var thiz = $( object );
		if ( self.post_field_section ) {
			var thiz_group_list = thiz.closest( '.js-wpv-shortcode-gui-group-list' );
			thiz_group_list
				.fadeOut( 'fast', function() {
					thiz_group_list
						.html( response.data.section )
						.fadeIn( 'fast' );
				});
		} else {
			var url = wpv_shortcodes_gui_texts.ajaxurl + '&action=wpv_shortcodes_gui_load_post_field_section_on_demand';
			$.ajax({
				url: url,
				success: function( response ) {
					self.post_field_section = response.data.section;
					$( '.js-wpv-shortcode-gui-group-list-post-field-section' ).each( function() {
						var thiz_instance = $( this );
						thiz_instance
							.fadeOut( 'fast', function() {
								thiz_instance
									.html( response.data.section )
									.fadeIn( 'fast' );
							});
					});
				}
			});
		}
	};

	/**
	 * Insert wpv-post-field shortcodes after generating the section on the GUI on demand
	 *
	 * @since 1.10
	 */

	$( document ).on( 'click', '.js-wpv-shortcode-gui-post-field-section-item', function() {
		var thiz = $( this ),
			thiz_fieldkey = thiz.data( 'fieldkey' ),
			thiz_group_list = thiz.closest( '.js-wpv-shortcode-gui-group-list' ),
			thiz_shortcode = "[wpv-post-field name='" + thiz_fieldkey + "']";
		self.insert_shortcode_with_no_attributes( 'wpv-post-field', thiz_shortcode );
	});
	
	/**
	* Scroll the Fields and Views dialog when clicking on a header menu item
	*
	* @since 2.2
	*/
	
	$( document ).on( 'click','.editor-addon-top-link', function() {
		
        var thiz	= $( this ),
		scrolling	= thiz.closest('.wpv-fields-and-views-dialog'),
        scrollingto	= scrolling.find( '.' + thiz.data('editor_addon_target' )+'-target' ),
        position	= scrollingto.position(),
        scrollto	= position.top;

        scrolling.animate({
            scrollTop: Math.round( scrollto ) - 25
        }, 'fast');

    });

	self.init();

};

jQuery( document ).ready( function( $ ) {
	WPViews.shortcodes_gui = new WPViews.ShortcodesGUI( $ );
});

var wpcfFieldsEditorCallback_redirect = null;

function wpcfFieldsEditorCallback_set_redirect(function_name, params) {
	wpcfFieldsEditorCallback_redirect = {'function' : function_name, 'params' : params};
}

/*
 * wpv-conditional shortcode QTags callback
 */
function wpv_add_conditional_quicktag_function(e, c, ed) {
	var  t = this;
	/*
	 !Important fix. If shortcode added from quicktags and not closed and we chage mode from text to visual, JS will generate error that closeTag = undefined.
	 */
	t.closeTag = function(el, event) {
		var ret = false, i = 0;
		while ( i < event.openTags.length ) {
			ret = event.openTags[i] == this.id ? i : false;
			el.value = this.display;
			i ++;
		}
		ed.openTags.splice(ret, 1);
	};
	window.wpcfActiveEditor = ed.id;
	var current_editor_object = {};
	if ( ed.canvas.selectionStart !== ed.canvas.selectionEnd ) {
		//When texty selected
		current_editor_object = {'e' : e, 'c' : c, 'ed' : ed, 't' : t, 'post_id' : '', 'close_tag' : true, 'codemirror' : ''};
		WPViews.shortcodes_gui.wpv_insert_popup_conditional('wpv-conditional', icl_editor_localization_texts.wpv_insert_conditional_shortcode, {}, icl_editor_localization_texts.wpv_editor_callback_nonce, current_editor_object );
	} else if ( ed.openTags ) {
		// if we have an open tag, see if it's ours
		var ret = false, i = 0, t = this;
		while ( i < ed.openTags.length ) {
			ret = ed.openTags[i] == t.id ? i : false;
			i ++;
		}
		if ( ret === false ) {
			t.tagStart = '';
			t.tagEnd = false;
			if ( ! ed.openTags ) {
				ed.openTags = [];
			}
			ed.openTags.push(t.id);
			e.value = '/' + e.value;
			current_editor_object = {'e' : e, 'c' : c, 'ed' : ed, 't' : t, 'post_id' : '', 'close_tag' : false, 'codemirror' : ''};
			WPViews.shortcodes_gui.wpv_insert_popup_conditional('wpv-conditional', icl_editor_localization_texts.wpv_insert_conditional_shortcode, {}, icl_editor_localization_texts.wpv_editor_callback_nonce, current_editor_object );
		} else {
			// close tag
			ed.openTags.splice(ret, 1);
			WPViews.shortcodes_gui.views_conditional_qtags_opened = false;
			t.tagStart = '[/wpv-conditional]';
			e.value = t.display;
			QTags.TagButton.prototype.callback.call(t, e, c, ed);
		}
	} else {
		// last resort, no selection and no open tags
		// so prompt for input and just open the tag
		t.tagStart = '';
		t.tagEnd = false;
		if ( ! ed.openTags ) {
			ed.openTags = [];
		}
		ed.openTags.push(t.id);
		e.value = '/' + e.value;
		current_editor_object = {'e' : e, 'c' : c, 'ed' : ed, 't' : t, 'post_id' : '', 'close_tag' : false, 'codemirror' : ''};
		WPViews.shortcodes_gui.wpv_insert_popup_conditional('wpv-conditional', icl_editor_localization_texts.wpv_insert_conditional_shortcode, {}, icl_editor_localization_texts.wpv_editor_callback_nonce, current_editor_object );
	}
>>>>>>> 88f8b5285171c25644c97fdea4dd80ba70b2875c
}