/*	Define the jQuery noConflict var for the plugin	*/
var wpshop = jQuery.noConflict();

/*	Center an element into the current page	*/
wpshop.fn.center = function() {
	this.css( 'top', ( jQuery( window ).height() - this.height() ) / 2 + 'px' );
	this.css( 'left', ( jQuery( window ).width() - this.width() ) / 2 + 'px' );
	return this;
};


//START RICH TEXT EDIT
wpshop( document ).ajaxComplete(function( e, xhr, settings ) {
	if ( ( settings != null ) && ( settings.data != null ) && ( typeof( settings.data ) != 'undefined' ) && settings.data.match( /action=(add|update)-tag/ ) ) {
		tinyMCE.get( 0 ).setContent( '' );
		kwsTriggerSave();
	}
});
// END RICH TEXT EDIT


/*	Action launched directly after the page is load	*/
wpshop( document ).ready(function() {
	jQuery( '.wpshop_input_radio_wpshop_cart_option_action_after_product_added_to_cart input[type=radio]' ).click(
			function() {
			if ( jQuery( this ).val() == 'dialog_msg' ) {
				jQuery( '#wpshop_cart_option_animation_cart_type' ).fadeIn( 'slow' );
			} else {
				jQuery( '#wpshop_cart_option_animation_cart_type' ).fadeOut( 'slow' );
			}
	});
/*	Chosen select librairy call	*/
	// Normal chosen selects
	jQuery( 'select.chosen_select' ).chosen( WPSHOP_CHOSEN_ATTRS );

	// Ajax chosen selects
	jQuery( 'select.ajax_chosen_select_related_products' ).ajaxChosen({
		method: 'POST',
		url: ajaxurl + '?action=wpshop_element_search&wpshop_ajax_nonce=' + jQuery( '#wpshop_nonce_ajax_search_related_products' ).val() + '&wpshop_element_type=' + jQuery( '#wpshop_ajax_search_element_type_related_products' ).val() + '&wpshop_format_result=0',
		dataType: 'json',
		jsonTermKey: 'wpshop_element_searched',
		textTyping: WPSHOP_AJAX_CHOSEN_KEEP_TYPING,
		textSearching: WPSHOP_AJAX_CHOSEN_SEARCHING,
		minTermLength: 2
	}, function( data ) {
		var terms = {};

		jQuery.each( data, function( i, val ) {
			terms[i] = val;
		});

		return terms;
	});
	// TOO MOCHE !!!
	jQuery( 'select.ajax_chosen_select_provider_products' ).ajaxChosen({
		method: 'POST',
		url: ajaxurl + '?action=wpshop_element_search&wpshop_ajax_nonce=' + jQuery( '#wpshop_nonce_ajax_search_provider_products' ).val() + '&wpshop_element_type=' + jQuery( '#wpshop_ajax_search_element_type_provider_products' ).val() + '&wpshop_format_result=0',
		dataType: 'json',
		jsonTermKey: 'wpshop_element_searched',
		textTyping: WPSHOP_AJAX_CHOSEN_KEEP_TYPING,
		textSearching: WPSHOP_AJAX_CHOSEN_SEARCHING,
		minTermLength: 2
	}, function( data ) {
		var terms = {};

		jQuery.each( data, function( i, val ) {
			terms[i] = val;
		});

		return terms;
	});

	/** Allow user to select all values when a multiselect or a checkbox input are displayed */
	jQuery( '.wpshop_list_chosen_select_all' ).live( 'click', function() {
		jQuery( '#' + jQuery( this ).attr( 'id' ).replace( 'wpshop_list_chosen_select_all_', '' ) + ' option' ).prop( 'selected', true );
		jQuery( '#' + jQuery( this ).attr( 'id' ).replace( 'wpshop_list_chosen_select_all_', '' ) ).trigger( 'liszt:updated' );
		return false;
	});
	/** Allow user to deselect all values when a multiselect or a checkbox input are displayed */
	jQuery( '.wpshop_list_chosen_deselect_all' ).live( 'click', function() {
		jQuery( '#' + jQuery( this ).attr( 'id' ).replace( 'wpshop_list_chosen_deselect_all_', '' ) + ' option' ).prop( 'selected', false );
		jQuery( '#' + jQuery( this ).attr( 'id' ).replace( 'wpshop_list_chosen_deselect_all_', '' ) ).trigger( 'liszt:updated' );
		return false;
	});

	/**	Add a box for adding element to select list on the fly	*/
	jQuery( '#wpshop_new_attribute_option_value_add' ).dialog({
		modal: true,
		dialogClass: 'wpshop_uidialog_box',
		autoOpen:false,
		show: 'blind',
		resizable: false,
		buttons:{
			'new-value-for-attribute-list': {
				text: WPSHOP_ADD_TEXT,
				click: function() {
					var already_selected_items = [];
					jQuery( 'select.wpshop_product_attribute_' + jQuery( '#wpshop_attribute_type_select_code' ).val() + ' option' ).each(function() {
						if ( jQuery( this ).is( ':selected' ) ) {
							already_selected_items.push( jQuery( this ).val() );
						}
					});
					var data = {
						action: 'new_option_for_select_from_product_edition',
						wpshop_ajax_nonce: WPSHOP_NEWOPTION_CREATION_NONCE,
						attribute_code: jQuery( '#wpshop_attribute_type_select_code' ).val(),
						attribute_new_label: jQuery( '#wpshop_new_attribute_option_value' ).val(),
						attribute_selected_values: already_selected_items,
						item_in_edition: jQuery( '#post_ID' ).val()
					};
					jQuery.post( ajaxurl, data, function( response ) {
						if ( response[0] ) {
							var container = 'wpshop_product_' + response[2] + '_input';
							jQuery( '.' + container ).html( response[1] );
							jQuery( 'select.chosen_select' ).chosen({ disable_search_threshold: 5, no_results_text: WPSHOP_CHOSEN_NO_RESULT });
							jQuery( '#wpshop_new_attribute_option_value_add' ).dialog( 'close' );
							jQuery( '#wpshop_new_attribute_option_value_add' ).children( 'img' ).hide();
							jQuery( '#wpshop_attribute_type_select_code' ).val( '' );
						} else {
							alert( response[1] );
						}
					}, 'json' );

					jQuery( this ).children( 'img' ).show();
				},
				class: 'button-primary'
			}
		},
		close:function() {
			jQuery( '#wpshop_new_attribute_option_value' ).val( '' );
		}
	});
	/**	Open the box to create a new value for a list	*/
	jQuery( '.wpshop_icons_add_new_value_to_option_list' ).live( 'click', function() {
		jQuery( '#wpshop_attribute_type_select_code' ).val( jQuery( this ).attr( 'id' ).replace( 'new_value_pict_', '' ) );
		jQuery( '#wpshop_new_attribute_option_value_add' ).dialog( 'open' );
		return false;
	});
/*	Chosen select librairy call	*/


	// Remove the non-rich fields
	/*JQuery('.form-field').has('#tag-description').remove();
	jQuery('.form-field').has('#category-description').remove();
	jQuery('.form-field').has('#description').remove();*/
	// Remove the non-rich fields new version
	if ( jQuery( '#wpshop_transform_taxonomy_description_field_into_wysiwyg_for_js_duplicate' ).length ) {
		var editor_wysiwyg_to_put = jQuery( '#wpshop_transform_taxonomy_description_field_into_wysiwyg_for_js_duplicate' ).html();
		jQuery( '.form-field #tag-description' ).replaceWith( editor_wysiwyg_to_put );
		jQuery( '.form-field #category-description' ).replaceWith( editor_wysiwyg_to_put );
		jQuery( '.form-field #description' ).replaceWith( editor_wysiwyg_to_put );
		jQuery( '#wpshop_transform_taxonomy_description_field_into_wysiwyg_for_js_duplicate' ).remove();
	}

	var profileTable = jQuery( '.user-edit-php .form-table, .profile-php .form-table' ).not( '.rich-text-tags' ).has( 'textarea#description' );
	profileTable.prev( 'h3' ).insertBefore( 'table.rich-text-tags' );
	profileTable.remove();

	// Make sure you're saving the latest content
	jQuery( 'input#submit' ).click(function( e ) {
		kwsTriggerSave();
	});

	jQuery( '.wpshop_coupon_type' ).click(function() {
		if ( jQuery( this ).is( ':checked' ) ) {
			jQuery( '.wpshop_coupon_type_unit' ).hide();
			jQuery( '.wpshop_coupon_type_unit_' + jQuery( this ).val() ).show();
		}
	});

	// UPLOAD
	if ( wpshop( '#wpshop_uploadForm' ).size() > 0 ) {
		wpshop( '#wpshop_uploadForm' ).ajaxForm({
			beforeSend: function() {
				wpshop( '.wpshop_loading' ).show();
			},
			complete: function( xhr ) {
				wpshop( '.wpshop_loading' ).hide();
				wpshop( '.statut' ).html( basename( xhr.responseText ) );
				wpshop( 'input[name="attribute_option[is_downloadable_][file_url]"]' ).val( xhr.responseText );
				wpshop( 'input[name=wpshop_file]' ).val( '' );
			}
		});
	}
	// UPLOAD END

	/*
	 * Manage form in frontend for adding new product
	 */
	if ( jQuery( '#new_entity_quick_form' ).length > 0 ) {
		jQuery( '#new_entity_quick_form' ).ajaxForm({
			dataType:  'json',
	        beforeSubmit: function( a, f, o ) {
	        	animate_container( '#new_entity_quick_form', jQuery( '#new_entity_quick_form_container' ) );
	        },
	        success: function( data ) {
	        	desanimate_container( jQuery( '#new_entity_quick_form_container' ) );
	        	jQuery( '#wpshop_quick_add_entity_result' ).html( data );
	            jQuery( '#wpshop_quick_add_entity_result' ).addClass( 'success' );
	        	jQuery( '#wpshop_quick_add_entity_result' ).show();
	            jQuery( '#new_entity_quick_form' )[0].reset();
	            setTimeout(function() {
	            	jQuery( '#wpshop_quick_add_entity_result' ).html( '' );
		            jQuery( '#wpshop_quick_add_entity_result' ).slideUp();
		            jQuery( '#wpshop_quick_add_entity_result' ).removeClass( 'success' );
	            }, 3500 );
	        }
		});
	}

	wpshop( '.edit-tags-php form' ).attr( 'enctype', 'multipart/form-data' ).attr( 'encoding', 'multipart/form-data' );

	/*	Hide the message container if not empty	*/
	if ( wpshop( '#wpshopMessage' ).html != '' ) {
		hideShowMessage( 5000 );
	}

	jQuery( '#shortcode-tabs' ).tabs();
	jQuery( '#fixed-tabs' ).tabs();

	jQuery( '.wpshop_icons_add_new_value_to_available_date_list' ).live( 'click', function() {
		jQuery( this ).before( jQuery( '#wpshop_attribute_date_empy_field' ).html() );
	});

	/*	Start attribute unit management part	*/
	wpshop( '#wpshop_attribute_unit_manager' ).dialog({
		autoOpen: false,
		width: 800,
		height: 600,
		modal: true,
		dialogClass: 'wpshop_uidialog_box',
		resizable: false,
		close:function() {
			wpshop( '#wpshop_attribute_unit_manager' ).html( '' );

			/* Test if combo-box exist in the DOM */
			if ( jQuery( '#wpshop_attributes_edition_table_field_id__unit_group_id' ).is( ':input' )  && jQuery( '#wpshop_attributes_edition_table_field_id__default_unit' ).is( ':input' ) ) {
				/* UPDATE UNIT GROUP COMBO-BOX */
				/* Hide the combo-box to refresh it */
				jQuery( '#wpshop_attributes_edition_table_field_id__unit_group_id' ).hide();
				jQuery( '#wpshop_attributes_edition_table_field_id__default_unit' ).hide();
				/* Display a loader */
				jQuery( '#wpshop_loader_input_group_unit' ).css( 'display', 'block' );
				jQuery( '#wpshop_loader_input_group_unit' ).html( '<div class=\'wpshopCenterContainer\' >' + wpshop( '#wpshopLoadingPicture' ).html() + '</div>' );
				jQuery( '#wpshop_loader_input_unit' ).css( 'display', 'block' );
				jQuery( '#wpshop_loader_input_unit' ).html( '<div class=\'wpshopCenterContainer\' >' + wpshop( '#wpshopLoadingPicture' ).html() + '</div>' );
				var data = {
						action: 'load_attribute_unit_list',
						wpshop_ajax_nonce: jQuery( '#input_wpshop_load_attribute_unit_list' ).val(),
						current_group: jQuery( '#wpshop_attributes_edition_table_field_id__unit_group_id' ).val(),
						selected_list:'group unit'
					};
				//Response, update the combo box
					jQuery.post( ajaxurl, data, function( response ) {
						if ( response[0] ) {
							jQuery( '#wpshop_attributes_edition_table_field_id__unit_group_id' ).html( response[1] );
							/* Hide the loader and display the combo box refreshed */
							jQuery( '#wpshop_loader_input_group_unit' ).css( 'display', 'none' );
							jQuery( '#wpshop_attributes_edition_table_field_id__unit_group_id' ).show();
						} else {
							alert( response[1] );
						}
					}, 'json' );

				/* UPDATE UNIT COMBO-BOX */
				var data = {
						action: 'load_attribute_unit_list',
						wpshop_ajax_nonce: jQuery( '#input_wpshop_load_attribute_unit_list' ).val(),
						current_group: jQuery( '#wpshop_attributes_edition_table_field_id__unit_group_id' ).val(),
						selected_list:'unit'
					};
				//Response, update the combo box
					jQuery.post( ajaxurl, data, function( response ) {
						if ( response[0] ) {
							jQuery( '#wpshop_attributes_edition_table_field_id__default_unit' ).html( response[1] );
							/* Hide the loader and display the combo box refreshed */
							jQuery( '#wpshop_loader_input_unit' ).css( 'display', 'none' );
							jQuery( '#wpshop_attributes_edition_table_field_id__default_unit' ).show();
						} else {
							alert( response[1] );
						}
					}, 'json' );

			}
		}
	});
	wpshop( '#wpshop_attribute_unit_manager_opener' ).click(function() {
		wpshop( '#wpshop_attribute_unit_manager' ).html( '<div class=\'wpshopCenterContainer\' >' + wpshop( '#wpshopLoadingPicture' ).html() + '</div>' );
		wpshop( '#wpshop_attribute_unit_manager' ).load( ajaxurl, {
			'action': 'wps_attribute_unit_interface',
			'_wpnonce': jQuery( '#wpshop_attribute_unit_manager_opener' ).data( 'nonce' )
		});
		wpshop( '#wpshop_attribute_unit_manager' ).dialog( 'open' );
	});

	wpshop( '#wpshop_attribute_group_unit_manager_opener' ).click(function() {
		wpshop( '#wpshop_attribute_unit_manager' ).html( '<div class=\'wpshopCenterContainer\' >' + wpshop( '#wpshopLoadingPicture' ).html() + '</div>' );
		wpshop( '#wpshop_attribute_unit_manager' ).load( ajaxurl, {
			'action': 'wps_attribute_unit_interface',
			'_wpnonce': jQuery( '#wpshop_attribute_unit_manager_opener' ).data( 'nonce' )
		});
		wpshop( '#wpshop_attribute_unit_manager' ).dialog( 'open' );
	});

	/*	Add support for option excluded domain deletion	*/
	jQuery( '.wpshop_attr_combo_option_delete' ).live( 'click', function() {
		if ( confirm( wpshopConvertAccentTojs( WPSHOP_SURE_TO_DELETE_ATTR_OPTION_FROM_LIST))){
			if(jQuery(this).closest("li").attr("id")) {
				jQuery(".wpshop_attr_combo_option_delete_" + jQuery(this).closest("li").attr("id").replace("att_option_div_container_", "")).html(jQuery("#wpshopLoadingPicture").html());
				var data = {
					action: "delete_option_for_select",
					current_type: jQuery("#wpshop_attributes_edition_table_field_id_frontend_input").val(),
					attribute_value_id: jQuery(this).closest("li").attr("id").replace("att_option_div_container_", ""),
					wpshop_ajax_nonce: jQuery("#wpshop_new_option_for_attribute_deletion_nonce").val()
				};
				jQuery.post(ajaxurl, data, function(response) {
					if (response[0]) {
						jQuery(response[1]).remove();
					}
					else {
						alert(response[1]);
					}
				}, 'json');
			}
			else {
				jQuery(this).closest("li").remove();
			}
		}
	});

	/** Recalculate price infos **/
	jQuery( '#update_products_loader' ).hide();
	jQuery( document ).on( 'click', '#wps_update_price_infos', function() {
		jQuery( '#update_products_loader' ).show();
		var data = {
				action: "update_products_prices",
				_wpnonce: jQuery( this ).data( 'nonce' ),
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery( '#update_products_loader' ).hide();
					alert( response['response'] );
				}
				else {
					jQuery( '#update_products_loader' ).hide();
					alert( response['response'] );
				}
		}, 'json');
	});




	/*	Make the attribute list into set section sortable	*/
	wpshop(".wpshop_attribute_combo_values_list_container").sortable({
		cancel: ".ui-state-disabled",
		placeholder: "ui-state-highlight",
		revert:true,
		forcePlaceholderSize : true,
		tolerance:'intersect',
		//connectWith: "ul.wpshop_attr_set_section_details",
		update: function(){
			var newOrder = wpshop(".wpshop_attribute_combo_values_list_container").sortable("toArray");
			wpshop("#wpshop_attribute_combo_values_list_order_def").val(newOrder);
		}
	}).disableSelection();


	// Send message
	jQuery('#sendMessage').click(function(){
		jQuery('#message_sender_loader').removeClass('wpshopHide');

		var data = {
				action: "send_message_by_type",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				customer_user_id : jQuery('#selected_recipient option:selected').val(),
				message_type_id : jQuery('#wpshop_postid').val(),
				message_model_name : jQuery('#wpshop_message_model').val()
				//order_id : jQuery('#wpshop_messages_histo_order_id').val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery('#message_histo_container').html(response['response']);
				}

				jQuery('#message_sender_loader').addClass('wpshopHide');
		}, 'json');
	});


	// ReSend message
	jQuery('input[name=resendMessage]').click(function(){

		var _this = jQuery(this);
		_this.attr('disabled', true);

		var messageid = jQuery('input[name=messageid]', _this.parent()).val();

		//alert(title+message+recipient);

		if (messageid!='undefined') {
			jQuery.getJSON(ajaxurl, { action: "wps_ajax_resend_message", messageid: messageid, _wpnonce: jQuery( this ).data( 'nonce' ) },
				function(data){
					if (!data.result) {
						alert(data.message);
						_this.attr('disabled', false);
					}
					else {
						window.location.href = window.location.href;
					}
				}
			);
		}

	});

	/*	Add support for option excluded domain addition	*/
	jQuery("#wpshop_attribute_add_new_option_value").live("click",function(){
		jQuery("#ajax-response").load(ajaxurl,{
			"action": "load_new_option_field"
		});
	});

	/*	Start product price amount calcul	*/
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_HT).live("keyup", function(){
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'HT'){
			calcul_price_from_ET();
		}
	});
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_HT).live("blur", function(){
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'HT'){
			calcul_price_from_ET();
		}
	});

	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_SPECIAL_PRICE).live("keyup", function(){
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'HT'){
			calcul_price_from_ET();
		}
		else if(WPSHOP_PRODUCT_PRICE_PILOT == 'TTC'){
			calcul_price_from_ATI();
		}
	});
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_SPECIAL_PRICE).live("blur", function(){
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'HT'){
			calcul_price_from_ET();
		}
		else if(WPSHOP_PRODUCT_PRICE_PILOT == 'TTC'){
			calcul_price_from_ATI();
		}
	});

	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX).change(function(){
		var value_tx = jQuery("#wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX + "_value_" + jQuery(this).val()).val();
		jQuery("#wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX + "_current_value").val(value_tx);

		if(WPSHOP_PRODUCT_PRICE_PILOT == 'HT'){
			calcul_price_from_ET();
		}
		else if(WPSHOP_PRODUCT_PRICE_PILOT == 'TTC'){
			calcul_price_from_ATI();
		}
	});

	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TTC).live("keyup", function(){
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'TTC'){
			calcul_price_from_ATI();
		}
	});
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TTC).live("blur", function(){
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'TTC'){
			calcul_price_from_ATI();
		}
	});


	/* Payment received */
	jQuery(".markAsCompleted").live('click',function(){
		var _this = jQuery(this);
		var this_class = _this.attr('class').split(' ');
		var oid = this_class[2].substr(6);

		if((jQuery("#used_method_payment_" + oid).val() == 'no_method') || (jQuery("#used_method_payment_transaction_id_" + oid).val() == '') || (jQuery("#used_method_payment_transaction_id_" + oid).val() == 0)){
			jQuery("#order_payment_method_" + oid).show();
			_this.hide();
		}
		else{
			mark_order_as_completed(_this, oid);
		}
	});

	/* Validate the payment transaction number */
	jQuery(".payment_method_validate").live('click',function(){
		var _this = jQuery(this);
		var this_class = _this.attr('class').split(' ');
		var oid = this_class[2].substr(6);
		var payment_method;
		// Display loading...
		_this.addClass('loading');

		if(jQuery("#used_method_payment_" + oid).val() == 'no_method'){
			payment_method = jQuery('.payment_method:checked').val();
		}
		else{
			payment_method = jQuery("#used_method_payment_" + oid).val();
		}

		var data = {
				action: "validate_payment_method",
				wpshop_ajax_nonce: jQuery("#input_wpshop_validate_payment_method").val(),
				order_id: oid,
				payment_method: payment_method,
				transaction_id: jQuery('#payment_method_transaction_number_'+oid).val()
				};
		jQuery.post(ajaxurl, data, function(response) {
			if ( response[0] ) {
				mark_order_as_completed(_this, oid);
			}
			else {
				alert( response[1] );
			}
		}, 'json');

		_this.removeClass('loading');
	});

	// Cancel of order
	jQuery(document).on('click','.markAsCanceled', function(){
		if ( confirm(WPSHOP_CANCEL_ORDER_CONFIRM_MESSAGE) ) {
			jQuery("#markascanceled_order_hidden_indicator").val('canceled');
		}
	});

	jQuery(document).on('click', '.markAsRefunded', function(){
		if ( confirm(WPSHOP_REFUND_ORDER_CONFIRM_MESSAGE) ) {
			jQuery("#markasrefunded_order_hidden_indicator").val('refunded');
		}
	});

	jQuery(document).on('click', '.wps_resend_order_to_customer', function(){
		if ( confirm(WPSHOP_RESEND_ORDER_CONFIRM_MESSAGE) ) {
			jQuery("#resendordertocustomer_order_hidden_indicator").val('resended');
		}
	});

	jQuery(".markAsShipped").live('click',function(){
		jQuery( this ).addClass( "wps-bton-loading" );
		var _this = jQuery(this);
		var this_class = _this.attr('class').split(' ');
		var oid = _this.data('id');
		// Display loading...
		_this.addClass('loading');
		//Open a dialog box to inform a shipping tracking number
		var data = {
			action: "dialog_inform_shipping_number",
			wpshop_ajax_nonce: jQuery("#input_wpshop_dialog_inform_shipping_number").val(),
			order_id: oid,
		};
		//Response, display the dialog box
			jQuery.post(ajaxurl, data, function(response) {
				if ( response[0] ) {
					jQuery('body').append('<div class="superBackground"></div><div class="popupAlert">'+response[1]+'</div>');
					jQuery('.popupAlert').center();
				}
				else {
					alert( response[1] );
				}

				jQuery( ".markAsShipped" ).removeClass( "wps-bton-loading" );
			}, 'json');

		_this.removeClass('loading');
	});

	// Validate the tracking number
	jQuery("input.sendTrackingNumber").live('click',function(){
		var oid = jQuery('input[name=oid]').val();
		var trackingNumber = jQuery('input[name=trackingNumber]').val();
		var trackingLink = jQuery('input[name=trackingLink]').val();
		var _this = jQuery('a.order_'+oid);
		jQuery('.superBackground').remove();
		jQuery('.popupAlert').remove();

		// Display loading...
		_this.addClass('loading');

		// Start ajax request : update the order state
		var data = {
				action: "change_order_state",
				wpshop_ajax_nonce: jQuery("#input_wpshop_change_order_state").val(),
				order_id: oid,
				order_state: 'shipped',
				order_shipped_number : trackingNumber,
				order_shipped_link : trackingLink
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response[0] ) {

					//jQuery('mark#order_status_'+oid).hide().html(response[2]).fadeIn(500);
					//jQuery('mark#order_status_'+oid).attr('class', response[1]);
					jQuery( '#wps-order-shipping-informations' ).html( response[2] );
					jQuery('a.order_'+oid).remove();

					jQuery('#wpshop_order_actions .wpshop_order_status_container').html( response[4] );
					jQuery('#wpshop_order_actions .wpshop_order_status_container').addClass( response[3] );

					jQuery('#selected_shiping_method').after( response[2] );

					if(jQuery("#order_status_" + oid) != undefined) {
						jQuery("#order_status_" + oid).html(response[4]);
						jQuery("#order_status_" + oid).addClass('shipped').removeClass('completed');
					}
				}
				else {
					alert( response[1] );
				}
			}, 'json');
	});





	jQuery("#wpshop_order_customer_changer").live('click', function(){
		if(jQuery("#wpshop_order_customer_selector").is(':visible')){
			jQuery(this).children("span").removeClass("wpshop_container_closer");
			jQuery(this).children("span").addClass("wpshop_container_opener");
			jQuery("#wpshop_order_customer_selector").hide();
		}
		else{
			jQuery(this).children("span").removeClass("wpshop_container_opener");
			jQuery(this).children("span").addClass("wpshop_container_closer");
			jQuery("#wpshop_order_customer_selector").show();
		}
	});


	/* ------------------- */
	/* ---- INFOBULLE ---- */
	/* ------------------- */
	jQuery("a.wpshop_infobulle_marker").live('mouseover',function(){
        if(jQuery(this).attr("title")=="") return false;
        var bulle = jQuery(".infobulle");
        bulle.html(jQuery(this).attr("title"));
        jQuery(this).attr("title","");
        var posTop = jQuery(this).offset().top-34;
        var posLeft = jQuery(this).offset().left-130;
        bulle.css({left:posLeft,top:posTop}).show();
    });
    jQuery("a.wpshop_infobulle_marker").live('mouseout',function(){
        var bulle = jQuery(".infobulle");
        jQuery(this).attr("title",bulle.text());
        bulle.hide();
    });
    jQuery("a.wpshop_infobulle_marker").click(function(e){
        e.preventDefault();
    });

	// Ferme la boite de dialogue
	jQuery("input.closeAlert").live('click', function(){
		jQuery('.superBackground').remove();
		jQuery('.popupAlert').remove();
	});



	/* Alerte lors de la d�coche de l'utilisation de permaliens personnalis� */
	jQuery('input[type=checkbox][name=useSpecialPermalink]').click(function(){
		if(jQuery(this).prop('checked') == false) {
			return confirm(jQuery('input[type=hidden][name=useSpecialPermalink_confirmMessage]').val());
		}
		return true;
	});

	jQuery('a.show-hide-shortcodes').click(function() {
		var element = jQuery('.wpshop_product_shortcode_display_container', jQuery(this).parent());
		if(element.css('display')=='block'){
			jQuery(this).html('Afficher');
			element.hide(250);
		}
		else {
			element.show(250);
			jQuery(this).html('Cacher');
		}
		return false;
	});

	/*	Ajoute la possibilit� d'augmenter la quantit� de produit � ajouter � une commande depuis l'administration	*/
	jQuery(".productQtyChange, .order_product_action_button.qty_change").on('click', function(){
		var quantity_input = jQuery(this).parent().children("input");
		if( jQuery(this).hasClass("wpshop_more_product_qty_in_cart") ){
			quantity_input.val(parseInt(quantity_input.val())+1);
			jQuery("#wpshop_admin_order_recalculate").show();
		}
		else{
			var new_pdct_qty = parseInt(quantity_input.val()) - 1;
			if(new_pdct_qty <= 0) jQuery(this).closest("tr").children("td:last").children("a").click();
			else{
				quantity_input.val(new_pdct_qty);
				jQuery("#wpshop_admin_order_recalculate").show();
			}
		}
		return false;
	});
	jQuery(".wpshop_order_shipping_cost_custom_admin").live("keypress",function(){
		jQuery("#wpshop_admin_order_recalculate").show();
	});

	/*	Hide the permalink area on orders and coupon post type	*/
	if((jQuery("#post_type").val() == WPSHOP_NEWTYPE_IDENTIFIER_ORDER) || (jQuery("#post_type").val() == WPSHOP_NEWTYPE_IDENTIFIER_COUPON)){
		jQuery("#edit-slug-box").remove();
		jQuery("#message a").remove();
	}
	/*	Allows to fill the shipping address form with billing information into admin order panel	*/
	if((jQuery("#post_type").val() == WPSHOP_NEWTYPE_IDENTIFIER_ORDER)){
		jQuery("#billing_as_shipping").click(function(){
			if(jQuery(this).is(":checked")){
				jQuery(".order_customer_adresses_edition_input_Billing").each(function(){
					jQuery("#" + jQuery(this).attr("id").replace("Billing", "Shipping")).val(jQuery(this).val());
				});
			}
		});
	}

	jQuery("#create_new_customer").click( function(){
		var _wpnonce = jQuery( this ).data( 'nonce' );
		jQuery("#create_new_customer_dialog").dialog("open");
		var data = {
				action: "load_create_new_customer_interface",
				_wpnonce: _wpnonce,
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response[0] ) {
					jQuery("#create_new_customer_picture").hide();
					jQuery("#create_new_customer_container").html(response[1]);
				}
			}, 'json');

	});
	jQuery("#create_new_customer_dialog").dialog({
			width:800,
			height:600,
			modal:true,
			autoOpen:false,
			resizable: false,
			dialogClass: "wpshop_uidialog_box",
			close:function(){
				jQuery("#create_new_customer_picture").show();
				jQuery("#create_new_customer_container").html('');
				jQuery("#create_new_customer_in_admin_reponseBox").html('');
			}
		});


	jQuery('#wpshop_customer_id').val( jQuery('#wpshop_order_user_customer_id').val() );
	if ( (undefined != jQuery('#wpshop_customer_id').val()) && (jQuery('#wpshop_customer_id').val() != 0) ) {
		display_customer_address_form ( jQuery('#wpshop_customer_id').val() );
	}

	jQuery('input[type=checkbox][name=shiptobilling]').click(function(){
		if (jQuery(this).attr('checked')=='checked') {
			jQuery('#shipping_infos_bloc').slideUp(250);
		}
		else jQuery('#shipping_infos_bloc').slideDown(250);
	});

	// Copie automatique de formulaire
	jQuery('input[name="wpshop_company_info[company_name]"]').keyup(function(){jQuery('input[name="wpshop_paymentAddress[company_name]"]').val(jQuery(this).val());});
	jQuery('input[name="wpshop_company_info[company_street]"]').keyup(function(){jQuery('input[name="wpshop_paymentAddress[company_street]"]').val(jQuery(this).val());});
	jQuery('input[name="wpshop_company_info[company_postcode]"]').keyup(function(){jQuery('input[name="wpshop_paymentAddress[company_postcode]"]').val(jQuery(this).val());});
	jQuery('input[name="wpshop_company_info[company_city]"]').keyup(function(){jQuery('input[name="wpshop_paymentAddress[company_city]"]').val(jQuery(this).val());});
	jQuery('input[name="wpshop_company_info[company_country]"]').keyup(function(){jQuery('input[name="wpshop_paymentAddress[company_country]"]').val(jQuery(this).val());});

	jQuery("#wpshop_admin_variation_options_toogler").live("click", function(){
		if ( jQuery(".wpshop_product_variation_options").is(":visible") ) {
			jQuery(".wpshop_product_variation_options").hide();
		}
		else {
			jQuery(".wpshop_product_variation_options").show();
		}
		jQuery("#wpshop_admin_variation_options_toogler span").toggleClass("toogler_opened toogler_closed");
	});


	jQuery("#wpshop_order_user_customer_id").change(function(){
		display_customer_address_form (jQuery(this).val());
	});

	jQuery("#wpshop_shipping_rules_min_max_activate").live('click', function() {
		if ( jQuery(this).is(':checked') ) {
			jQuery("#min_max_shipping_rules_configuration").fadeIn('slow');
		}
		else {
			jQuery("#min_max_shipping_rules_configuration").fadeOut('slow');
		}
	});

	jQuery('#wpshop_generate_invoice_button').live('click', function() {
		if ( confirm(WPSHOP_CONFIRM_BEFORE_GENERATE_INVOICE) ) {
			jQuery('#action_triggered_from').val('generate_invoice');
		}
	});

	jQuery('#wpshop_limit_shipping_destination_active').live('click', function() {
		if ( jQuery(this).is(':checked') ) {
			jQuery('#wpshop_limit_shipping_destination_interface').fadeIn('slow');
		}
		else {
			jQuery('#wpshop_limit_shipping_destination_interface').fadeOut('slow');
		}
	});


	jQuery(document).on('click', '#send_downloadable_file_button', function() {
		jQuery( '#send_downloadable_file_button' ).addClass( 'wps-bton-loading' );
		jQuery('#upload_downloadable_file').submit();
	});



	jQuery('.send_downloadable_file_dialog').dialog({
		modal: true,
		dialogClass: "wpshop_uidialog_box",
		autoOpen:false,
		show: "blind",
		resizable: false,
		width: 600
	});

	jQuery(document).on('click','.send_downlodable_file', function() {
		var send_downlodable_file = jQuery( this );
		send_downlodable_file.addClass( 'wps-bton-loading' );
		var data = {
			action: "fill_the_downloadable_dialog",
			_wpnonce: send_downlodable_file.data( 'nonce' ),
			product_identifier : send_downlodable_file.parent().find(' .product_identifer_field').val()
		};
		jQuery.post(ajaxurl, data, function(response) {
			if ( response['status'] ) {
				jQuery('.send_downloadable_file_dialog[data-post="'+send_downlodable_file.parent().find(' .product_identifer_field').val()+'"]').html( response['response'] );
				jQuery('.send_downloadable_file_dialog[data-post="'+send_downlodable_file.parent().find(' .product_identifer_field').val()+'"]').dialog('open');
			}
			send_downlodable_file.removeClass( 'wps-bton-loading' );
		}, 'json');
	});


	jQuery('.wpshop_product_attribute_is_downloadable_').each(function() {
	//	new show_downloadable_interface_in_admin( this );
	});

	jQuery( document ).on('change', '.wpshop_product_attribute_is_downloadable_', function() {
		new show_downloadable_interface_in_admin( this );
	});

	function show_downloadable_interface_in_admin( selected_value ) {
		var fields;
		if( jQuery(selected_value).parent().hasClass('wpshop_form_input_element') ) {
			fields = jQuery(selected_value).parent().parent();
		} else if( jQuery(selected_value).parent().hasClass('wpshop_variation_special_value_container') ) {
			fields = jQuery(selected_value).parent();
		}
		var data = {
			action: "show_downloadable_interface_in_admin",
			_wpnonce: fields.find('.attribute_option_is_downloadable_ input[name="_show_downloadable_interface_in_admin_wpnonce"]').val(),
			post_id: fields.find('.attribute_option_is_downloadable_ .wpshop_form_input_element .product_identifer_field').val(),
			selected_value: jQuery(selected_value).find("option:selected").val()
		};
		var request = function( data, callback ) {
			if( typeof show_downloadable_interface_in_admin.cache_request == "undefined" ) {
				show_downloadable_interface_in_admin.cache_request = [];
			}
			if( typeof show_downloadable_interface_in_admin.cache_request[data.selected_value] == "undefined" ) {
				jQuery.post(ajaxurl, data, function(response) {
					show_downloadable_interface_in_admin.cache_request[data.selected_value] = response['status'];
					callback( show_downloadable_interface_in_admin.cache_request[data.selected_value] );
				}, 'json');
			} else {
				callback( show_downloadable_interface_in_admin.cache_request[data.selected_value] );
			}
		}
		request( data, function( request ) {
			if( request ) {
				fields.find('.attribute_option_is_downloadable_').fadeIn( 'slow' );
			}
			else {
				fields.find('.attribute_option_is_downloadable_').fadeOut( 'slow' );
			}
		} );
	}

	/** Search on order **/
	if ( (jQuery('.post_type_page').length > 0) && (jQuery('.post_type_page').val() == 'wpshop_shop_order') && (jQuery('.search-box').length > 0) ) {

		var content = '<p>' + WPSHOP_SEARCH_IN_ORDER_EXPLAIN_MESSAGE + ' : ';
		content += '<input type="radio" value="customer" name="entity_to_search" id="customer_search_choice"';
		if ( WPSHOP_SEARCH_IN_ORDER_USER_CHOICE == 'customer' ) {
			content += 'checked="checked"';
		}
		content += '/><label for="customer_search_choice">' + WPSHOP_SEARCH_IN_ORDER_CHOICE_CUSTOMER + '</label> ';

		content += '<input type="radio" value="product" name="entity_to_search" id="product_search_choice" ';
		if ( WPSHOP_SEARCH_IN_ORDER_USER_CHOICE == 'product' ) {
			content += 'checked="checked"';
		}
		content += '/><label for="product_search_choice">' + WPSHOP_SEARCH_IN_ORDER_CHOICE_PRODUCT + '</label> </p>';


		jQuery('.search-box').prepend( content );
	}

	/** Customer can pay **/
	jQuery( document ).on( 'click', '.quotation_is_payable_by_customer', function( e ) {
		e.preventDefault();
		var order_id = jQuery( '#post_ID' ).val();
		var btn = jQuery( this );
		var btnBlock = btn.parent().parent();
		btn.addClass( 'wps-bton-loading' );
		var data = {
				action: "wps_quotation_is_payable_by_customer",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				order_id : order_id
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					btnBlock.html( response['response'] );
				} else {
					alert( response['response'] );
					btn.addClass( 'wps-bton-loading' );
				}
		}, 'json');

	});

	/** Direct payment Link **/
	jQuery( '#wps_send_direct_payment_link_loader').hide();
	jQuery( document ).on( 'click', '.send_direct_payment_link', function( e ) {
		e.preventDefault();
		jQuery( '#wps_send_direct_payment_link_loader').show();
		var order_id = jQuery( '#post_ID' ).val();
		var data = {
				action: "wps_send_direct_payment_link",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				order_id : order_id
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					alert( response['response'] );
					jQuery( '#wps_send_direct_payment_link_loader').hide();
				}
				else {
					alert( response['response'] );
					jQuery( '#wps_send_direct_payment_link_loader').hide();
				}
		}, 'json');
	});
	jQuery( document ).on( 'click', '#wps_direct_link_url', function( e ) {
		jQuery(this).select();
	} );
	jQuery( document ).on( 'click', '*[data-copy-target]', function( e ) {
		var trigger = jQuery( this );
		var element = jQuery( trigger.data('copy-target') );
		trigger.addClass( 'wps-bton-loading' );
		element.select();
		try{
			document.execCommand("copy");
			console.log( 'Copy success' );
		} catch(e){
			console.log( 'Error copy' );
		}
		trigger.removeClass( 'wps-bton-loading' );
	});

	jQuery( '#wps_delete_pîcture_cat_loader').hide();
	/** Delete category picture **/
	jQuery( document ).on( 'click', '#wps-delete-category-picture', function(e) {
		e.preventDefault();
		jQuery( '#wps-delete-category-picture' ).addClass( 'wps-bton-loading' );
		var cat_id = jQuery( 'input[name="tag_ID"]').val();
		var order_id = jQuery( '#post_ID' ).val();
		var data = {
				action: "wps_delete_picture_category",
				_wpnonce: jQuery( this ).data( 'nonce' ),
				cat_id : cat_id
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery( '.wps_category_thumbnail_preview_container').html( response['response'] );
					jQuery( '#wps_category_picture_id' ).removeAttr( 'value' );
					jQuery( '#wps-delete-category-picture' ).removeClass( 'wps-bton-loading' );
					jQuery( '#wps-delete-category-picture' ).hide();
				}
				else {
					alert( response['response'] );
					jQuery( '#wps-delete-category-picture' ).removeClass( 'wps-bton-loading' );
				}
		}, 'json');
	});


	/** Add WP media gallery in category edit interface **/

	jQuery( document ).on( 'click', '#add_picture_to_category', function(e) {
		e.preventDefault();
		jQuery( this ).addClass( 'wps-bton-loading' );
		// Open media gallery
		var uploader_category = wp.media({
					title : WPSHOP_JS_VAR_ADD_PICTURE,
					multiple : false
				}).on('select', function() {
					var selected_picture = uploader_category.state().get( 'selection' );
					var attachment = selected_picture.first().toJSON();
					jQuery( '#wps_category_picture_id' ).val( attachment.id );
					jQuery( '.wps_category_thumbnail_preview_container img' ).attr( 'src', attachment.url);
					jQuery( '#add_picture_to_category' ).removeClass( 'wps-bton-loading' );
				}).open();

		jQuery( '#add_picture_to_category' ).removeClass( 'wps-bton-loading' );
	});


	jQuery( document ).on( 'click', '#wps-add-logo-picture', function(e) {
		e.preventDefault();
		jQuery( this ).addClass( 'wps-bton-loading' );
		// Open media gallery
		var uploader_category = wp.media({
					title : WPSHOP_JS_VAR_ADD_LOGO,
					multiple : false
				}).on('select', function() {
					var selected_picture = uploader_category.state().get( 'selection' );
					var attachment = selected_picture.first().toJSON();
					jQuery( '#wps-add-logo-picture' ).removeClass( 'wps-bton-loading' );
					jQuery( '#wpshop_logo_field' ).val( attachment.url );
					jQuery( '#wpshop_logo_thumbnail' ).attr( 'src', attachment.url );
					jQuery( '#wps-delete-shop-logo' ).show();
				}).open();

		jQuery( '#wps-add-logo-picture' ).removeClass( 'wps-bton-loading' );
	});

	if ( jQuery("#wpshop_low_stock_options_active").is(':checked') ) {
		jQuery("#low_stock_alert_configuration").show();
	}
	else {
		jQuery("#low_stock_alert_configuration").hide();
	}

	if ( jQuery("#wpshop_low_stock_alert_options_based_on_stock").is(':checked') ) {
		jQuery("#low_stock_alert_limit").show();
	}
	else {
		jQuery("#low_stock_alert_limit").hide();
	}

	jQuery("#wpshop_low_stock_options_active").live('click', function(){
		if ( jQuery("#wpshop_low_stock_options_active").is(':checked') ) {
			jQuery("#low_stock_alert_configuration").fadeIn('slow');
		}
		else {
			jQuery("#low_stock_alert_configuration").fadeOut('slow');;
		}
	});

	jQuery("#wpshop_low_stock_alert_options_based_on_stock").live('click', function(){
		if ( jQuery("#wpshop_low_stock_alert_options_based_on_stock").is(':checked') ) {
			jQuery("#low_stock_alert_limit").fadeIn('slow');
		}
	});

	jQuery("#wpshop_low_stock_alert_options_not_based_on_stock").live('click', function() {
		if ( jQuery("#wpshop_low_stock_alert_options_not_based_on_stock").is(':checked') ) {
			jQuery("#low_stock_alert_limit").fadeOut('slow');
		}
	});

	/**
	jQuery( document ).on( 'click', '#wps_submit_address_form', function() {

		jQuery('#wps_address_form_save').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery( '#wps_submit_address_form' ).addClass( 'wps-bton-loading' );
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		reload_administration_dashboard_address( response[2], jQuery( '#wps_customer_id').val() );
	        		jQuery( '#TB_closeWindowButton').click();
	        		jQuery( '#wps_submit_address_form' ).removeClass( 'wps-bton-loading' );
	        	}
	        	else {
	        		jQuery('#wps_address_error_container').html( response[1] );
	        		jQuery( '#wps_submit_address_form' ).removeClass( 'wps-bton-loading' );
	        	}
	        },
		}).submit();
	});


	function reload_administration_dashboard_address( customer_id ) {
		if( customer_id != null ) {
			var data = {
					action: "wps_order_choose_customer",
					customer_id : customer_id
				};
			jQuery.post(ajaxurl, data, function( response ){
				if ( response['status'] ) {
					jQuery( '#wps_customer_id' ).val( jQuery( '#user_customer_id').val() );
					jQuery( '.wps_billing_data_container' ).html( response['billing_data'] );
					jQuery( '.wps_shipping_data_container' ).html( response['shipping_data'] );
					jQuery ( '#wps_order_choose_customer' ).removeClass( 'wps-bton-loading' );
				}
				else {
					alert( 'An error was occured...');
				}
			}, 'json');
		}
	}
	**/
});

/* Javascript plotting library for jQuery, v. 0.7.
*
* Released under the MIT license by IOLA, December 2007.
*/
(function(b){b.color={};b.color.make=function(d,e,g,f){var c={};c.r=d||0;c.g=e||0;c.b=g||0;c.a=f!=null?f:1;c.add=function(h,j){for(var k=0;k<h.length;++k){c[h.charAt(k)]+=j}return c.normalize()};c.scale=function(h,j){for(var k=0;k<h.length;++k){c[h.charAt(k)]*=j}return c.normalize()};c.toString=function(){if(c.a>=1){return"rgb("+[c.r,c.g,c.b].join(",")+")"}else{return"rgba("+[c.r,c.g,c.b,c.a].join(",")+")"}};c.normalize=function(){function h(k,j,l){return j<k?k:(j>l?l:j)}c.r=h(0,parseInt(c.r),255);c.g=h(0,parseInt(c.g),255);c.b=h(0,parseInt(c.b),255);c.a=h(0,c.a,1);return c};c.clone=function(){return b.color.make(c.r,c.b,c.g,c.a)};return c.normalize()};b.color.extract=function(d,e){var c;do{c=d.css(e).toLowerCase();if((c!="")&&(c!="transparent")){break}d=d.parent()}while(!b.nodeName(d.get(0),"body"));if(c=="rgba(0, 0, 0, 0)"){c="transparent"}return b.color.parse(c)};b.color.parse=function(c){var d,f=b.color.make;if(d=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(c)){return f(parseInt(d[1],10),parseInt(d[2],10),parseInt(d[3],10))}if(d=/rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]+(?:\.[0-9]+)?)\s*\)/.exec(c)){return f(parseInt(d[1],10),parseInt(d[2],10),parseInt(d[3],10),parseFloat(d[4]))}if(d=/rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(c)){return f(parseFloat(d[1])*2.55,parseFloat(d[2])*2.55,parseFloat(d[3])*2.55)}if(d=/rgba\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\s*\)/.exec(c)){return f(parseFloat(d[1])*2.55,parseFloat(d[2])*2.55,parseFloat(d[3])*2.55,parseFloat(d[4]))}if(d=/#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(c)){return f(parseInt(d[1],16),parseInt(d[2],16),parseInt(d[3],16))}if(d=/#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(c)){return f(parseInt(d[1]+d[1],16),parseInt(d[2]+d[2],16),parseInt(d[3]+d[3],16))}var e=b.trim(c).toLowerCase();if(e=="transparent"){return f(255,255,255,0)}else{d=a[e]||[0,0,0];return f(d[0],d[1],d[2])}};var a={aqua:[0,255,255],azure:[240,255,255],beige:[245,245,220],black:[0,0,0],blue:[0,0,255],brown:[165,42,42],cyan:[0,255,255],darkblue:[0,0,139],darkcyan:[0,139,139],darkgrey:[169,169,169],darkgreen:[0,100,0],darkkhaki:[189,183,107],darkmagenta:[139,0,139],darkolivegreen:[85,107,47],darkorange:[255,140,0],darkorchid:[153,50,204],darkred:[139,0,0],darksalmon:[233,150,122],darkviolet:[148,0,211],fuchsia:[255,0,255],gold:[255,215,0],green:[0,128,0],indigo:[75,0,130],khaki:[240,230,140],lightblue:[173,216,230],lightcyan:[224,255,255],lightgreen:[144,238,144],lightgrey:[211,211,211],lightpink:[255,182,193],lightyellow:[255,255,224],lime:[0,255,0],magenta:[255,0,255],maroon:[128,0,0],navy:[0,0,128],olive:[128,128,0],orange:[255,165,0],pink:[255,192,203],purple:[128,0,128],violet:[128,0,128],red:[255,0,0],silver:[192,192,192],white:[255,255,255],yellow:[255,255,0]}})(jQuery);(function(c){function b(av,ai,J,af){var Q=[],O={colors:["#edc240","#afd8f8","#cb4b4b","#4da74d","#9440ed"],legend:{show:true,noColumns:1,labelFormatter:null,labelBoxBorderColor:"#ccc",container:null,position:"ne",margin:5,backgroundColor:null,backgroundOpacity:0.85},xaxis:{show:null,position:"bottom",mode:null,color:null,tickColor:null,transform:null,inverseTransform:null,min:null,max:null,autoscaleMargin:null,ticks:null,tickFormatter:null,labelWidth:null,labelHeight:null,reserveSpace:null,tickLength:null,alignTicksWithAxis:null,tickDecimals:null,tickSize:null,minTickSize:null,monthNames:null,timeformat:null,twelveHourClock:false},yaxis:{autoscaleMargin:0.02,position:"left"},xaxes:[],yaxes:[],series:{points:{show:false,radius:3,lineWidth:2,fill:true,fillColor:"#ffffff",symbol:"circle"},lines:{lineWidth:2,fill:false,fillColor:null,steps:false},bars:{show:false,lineWidth:2,barWidth:1,fill:true,fillColor:null,align:"left",horizontal:false},shadowSize:3},grid:{show:true,aboveData:false,color:"#545454",backgroundColor:null,borderColor:null,tickColor:null,labelMargin:5,axisMargin:8,borderWidth:2,minBorderMargin:null,markings:null,markingsColor:"#f4f4f4",markingsLineWidth:2,clickable:false,hoverable:false,autoHighlight:true,mouseActiveRadius:10},hooks:{}},az=null,ad=null,y=null,H=null,A=null,p=[],aw=[],q={left:0,right:0,top:0,bottom:0},G=0,I=0,h=0,w=0,ak={processOptions:[],processRawData:[],processDatapoints:[],drawSeries:[],draw:[],bindEvents:[],drawOverlay:[],shutdown:[]},aq=this;aq.setData=aj;aq.setupGrid=t;aq.draw=W;aq.getPlaceholder=function(){return av};aq.getCanvas=function(){return az};aq.getPlotOffset=function(){return q};aq.width=function(){return h};aq.height=function(){return w};aq.offset=function(){var aB=y.offset();aB.left+=q.left;aB.top+=q.top;return aB};aq.getData=function(){return Q};aq.getAxes=function(){var aC={},aB;c.each(p.concat(aw),function(aD,aE){if(aE){aC[aE.direction+(aE.n!=1?aE.n:"")+"axis"]=aE}});return aC};aq.getXAxes=function(){return p};aq.getYAxes=function(){return aw};aq.c2p=C;aq.p2c=ar;aq.getOptions=function(){return O};aq.highlight=x;aq.unhighlight=T;aq.triggerRedrawOverlay=f;aq.pointOffset=function(aB){return{left:parseInt(p[aA(aB,"x")-1].p2c(+aB.x)+q.left),top:parseInt(aw[aA(aB,"y")-1].p2c(+aB.y)+q.top)}};aq.shutdown=ag;aq.resize=function(){B();g(az);g(ad)};aq.hooks=ak;F(aq);Z(J);X();aj(ai);t();W();ah();function an(aD,aB){aB=[aq].concat(aB);for(var aC=0;aC<aD.length;++aC){aD[aC].apply(this,aB)}}function F(){for(var aB=0;aB<af.length;++aB){var aC=af[aB];aC.init(aq);if(aC.options){c.extend(true,O,aC.options)}}}function Z(aC){var aB;c.extend(true,O,aC);if(O.xaxis.color==null){O.xaxis.color=O.grid.color}if(O.yaxis.color==null){O.yaxis.color=O.grid.color}if(O.xaxis.tickColor==null){O.xaxis.tickColor=O.grid.tickColor}if(O.yaxis.tickColor==null){O.yaxis.tickColor=O.grid.tickColor}if(O.grid.borderColor==null){O.grid.borderColor=O.grid.color}if(O.grid.tickColor==null){O.grid.tickColor=c.color.parse(O.grid.color).scale("a",0.22).toString()}for(aB=0;aB<Math.max(1,O.xaxes.length);++aB){O.xaxes[aB]=c.extend(true,{},O.xaxis,O.xaxes[aB])}for(aB=0;aB<Math.max(1,O.yaxes.length);++aB){O.yaxes[aB]=c.extend(true,{},O.yaxis,O.yaxes[aB])}if(O.xaxis.noTicks&&(O.xaxis.ticks==null)){O.xaxis.ticks=O.xaxis.noTicks}if(O.yaxis.noTicks&&(O.yaxis.ticks==null)){O.yaxis.ticks=O.yaxis.noTicks}if(O.x2axis){O.xaxes[1]=c.extend(true,{},O.xaxis,O.x2axis);O.xaxes[1].position="top"}if(O.y2axis){O.yaxes[1]=c.extend(true,{},O.yaxis,O.y2axis);O.yaxes[1].position="right"}if(O.grid.coloredAreas){O.grid.markings=O.grid.coloredAreas}if(O.grid.coloredAreasColor){O.grid.markingsColor=O.grid.coloredAreasColor}if(O.lines){c.extend(true,O.series.lines,O.lines)}if(O.points){c.extend(true,O.series.points,O.points)}if(O.bars){c.extend(true,O.series.bars,O.bars)}if(O.shadowSize!=null){O.series.shadowSize=O.shadowSize}for(aB=0;aB<O.xaxes.length;++aB){V(p,aB+1).options=O.xaxes[aB]}for(aB=0;aB<O.yaxes.length;++aB){V(aw,aB+1).options=O.yaxes[aB]}for(var aD in ak){if(O.hooks[aD]&&O.hooks[aD].length){ak[aD]=ak[aD].concat(O.hooks[aD])}}an(ak.processOptions,[O])}function aj(aB){Q=Y(aB);ax();z()}function Y(aE){var aC=[];for(var aB=0;aB<aE.length;++aB){var aD=c.extend(true,{},O.series);if(aE[aB].data!=null){aD.data=aE[aB].data;delete aE[aB].data;c.extend(true,aD,aE[aB]);aE[aB].data=aD.data}else{aD.data=aE[aB]}aC.push(aD)}return aC}function aA(aC,aD){var aB=aC[aD+"axis"];if(typeof aB=="object"){aB=aB.n}if(typeof aB!="number"){aB=1}return aB}function m(){return c.grep(p.concat(aw),function(aB){return aB})}function C(aE){var aC={},aB,aD;for(aB=0;aB<p.length;++aB){aD=p[aB];if(aD&&aD.used){aC["x"+aD.n]=aD.c2p(aE.left)}}for(aB=0;aB<aw.length;++aB){aD=aw[aB];if(aD&&aD.used){aC["y"+aD.n]=aD.c2p(aE.top)}}if(aC.x1!==undefined){aC.x=aC.x1}if(aC.y1!==undefined){aC.y=aC.y1}return aC}function ar(aF){var aD={},aC,aE,aB;for(aC=0;aC<p.length;++aC){aE=p[aC];if(aE&&aE.used){aB="x"+aE.n;if((aF[aB]==null)&&(aE.n==1)){aB="x"}if(aF[aB]!=null){aD.left=aE.p2c(aF[aB]);break}}}for(aC=0;aC<aw.length;++aC){aE=aw[aC];if(aE&&aE.used){aB="y"+aE.n;if((aF[aB]==null)&&(aE.n==1)){aB="y"}if(aF[aB]!=null){aD.top=aE.p2c(aF[aB]);break}}}return aD}function V(aC,aB){if(!aC[aB-1]){aC[aB-1]={n:aB,direction:aC==p?"x":"y",options:c.extend(true,{},aC==p?O.xaxis:O.yaxis)}}return aC[aB-1]}function ax(){var aG;var aM=Q.length,aB=[],aE=[];for(aG=0;aG<Q.length;++aG){var aJ=Q[aG].color;if(aJ!=null){--aM;if(typeof aJ=="number"){aE.push(aJ)}else{aB.push(c.color.parse(Q[aG].color))}}}for(aG=0;aG<aE.length;++aG){aM=Math.max(aM,aE[aG]+1)}var aC=[],aF=0;aG=0;while(aC.length<aM){var aI;if(O.colors.length==aG){aI=c.color.make(100,100,100)}else{aI=c.color.parse(O.colors[aG])}var aD=aF%2==1?-1:1;aI.scale("rgb",1+aD*Math.ceil(aF/2)*0.2);aC.push(aI);++aG;if(aG>=O.colors.length){aG=0;++aF}}var aH=0,aN;for(aG=0;aG<Q.length;++aG){aN=Q[aG];if(aN.color==null){aN.color=aC[aH].toString();++aH}else{if(typeof aN.color=="number"){aN.color=aC[aN.color].toString()}}if(aN.lines.show==null){var aL,aK=true;for(aL in aN){if(aN[aL]&&aN[aL].show){aK=false;break}}if(aK){aN.lines.show=true}}aN.xaxis=V(p,aA(aN,"x"));aN.yaxis=V(aw,aA(aN,"y"))}}function z(){var aO=Number.POSITIVE_INFINITY,aI=Number.NEGATIVE_INFINITY,aB=Number.MAX_VALUE,aU,aS,aR,aN,aD,aJ,aT,aP,aH,aG,aC,a0,aX,aL;function aF(a3,a2,a1){if((a2<a3.datamin)&&(a2!=-aB)){a3.datamin=a2}if((a1>a3.datamax)&&(a1!=aB)){a3.datamax=a1}}c.each(m(),function(a1,a2){a2.datamin=aO;a2.datamax=aI;a2.used=false});for(aU=0;aU<Q.length;++aU){aJ=Q[aU];aJ.datapoints={points:[]};an(ak.processRawData,[aJ,aJ.data,aJ.datapoints])}for(aU=0;aU<Q.length;++aU){aJ=Q[aU];var aZ=aJ.data,aW=aJ.datapoints.format;if(!aW){aW=[];aW.push({x:true,number:true,required:true});aW.push({y:true,number:true,required:true});if(aJ.bars.show||(aJ.lines.show&&aJ.lines.fill)){aW.push({y:true,number:true,required:false,defaultValue:0});if(aJ.bars.horizontal){delete aW[aW.length-1].y;aW[aW.length-1].x=true}}aJ.datapoints.format=aW}if(aJ.datapoints.pointsize!=null){continue}aJ.datapoints.pointsize=aW.length;aP=aJ.datapoints.pointsize;aT=aJ.datapoints.points;insertSteps=aJ.lines.show&&aJ.lines.steps;aJ.xaxis.used=aJ.yaxis.used=true;for(aS=aR=0;aS<aZ.length;++aS,aR+=aP){aL=aZ[aS];var aE=aL==null;if(!aE){for(aN=0;aN<aP;++aN){a0=aL[aN];aX=aW[aN];if(aX){if(aX.number&&(a0!=null)){a0=+a0;if(isNaN(a0)){a0=null}else{if(a0==Infinity){a0=aB}else{if(a0==-Infinity){a0=-aB}}}}if(a0==null){if(aX.required){aE=true}if(aX.defaultValue!=null){a0=aX.defaultValue}}}aT[aR+aN]=a0}}if(aE){for(aN=0;aN<aP;++aN){a0=aT[aR+aN];if(a0!=null){aX=aW[aN];if(aX.x){aF(aJ.xaxis,a0,a0)}if(aX.y){aF(aJ.yaxis,a0,a0)}}aT[aR+aN]=null}}else{if(insertSteps&&(aR>0)&&(aT[aR-aP]!=null)&&(aT[aR-aP]!=aT[aR])&&(aT[aR-aP+1]!=aT[aR+1])){for(aN=0;aN<aP;++aN){aT[aR+aP+aN]=aT[aR+aN]}aT[aR+1]=aT[aR-aP+1];aR+=aP}}}}for(aU=0;aU<Q.length;++aU){aJ=Q[aU];an(ak.processDatapoints,[aJ,aJ.datapoints])}for(aU=0;aU<Q.length;++aU){aJ=Q[aU];aT=aJ.datapoints.points,aP=aJ.datapoints.pointsize;var aK=aO,aQ=aO,aM=aI,aV=aI;for(aS=0;aS<aT.length;aS+=aP){if(aT[aS]==null){continue}for(aN=0;aN<aP;++aN){a0=aT[aS+aN];aX=aW[aN];if(!aX||(a0==aB)||(a0==-aB)){continue}if(aX.x){if(a0<aK){aK=a0}if(a0>aM){aM=a0}}if(aX.y){if(a0<aQ){aQ=a0}if(a0>aV){aV=a0}}}}if(aJ.bars.show){var aY=aJ.bars.align=="left"?0:-aJ.bars.barWidth/2;if(aJ.bars.horizontal){aQ+=aY;aV+=aY+aJ.bars.barWidth}else{aK+=aY;aM+=aY+aJ.bars.barWidth}}aF(aJ.xaxis,aK,aM);aF(aJ.yaxis,aQ,aV)}c.each(m(),function(a1,a2){if(a2.datamin==aO){a2.datamin=null}if(a2.datamax==aI){a2.datamax=null}})}function j(aB,aC){var aD=document.createElement("canvas");aD.className=aC;aD.width=G;aD.height=I;if(!aB){c(aD).css({position:"absolute",left:0,top:0})}c(aD).appendTo(av);if(!aD.getContext){aD=window.G_vmlCanvasManager.initElement(aD)}aD.getContext("2d").save();return aD}function B(){G=av.width();I=av.height();if((G<=0)||(I<=0)){throw"Invalid dimensions for plot, width = "+G+", height = "+I}}function g(aC){if(aC.width!=G){aC.width=G}if(aC.height!=I){aC.height=I}var aB=aC.getContext("2d");aB.restore();aB.save()}function X(){var aC,aB=av.children("canvas.base"),aD=av.children("canvas.overlay");if((aB.length==0)||(aD==0)){av.html("");av.css({padding:0});if(av.css("position")=="static"){av.css("position","relative")}B();az=j(true,"base");ad=j(false,"overlay");aC=false}else{az=aB.get(0);ad=aD.get(0);aC=true}H=az.getContext("2d");A=ad.getContext("2d");y=c([ad,az]);if(aC){av.data("plot").shutdown();aq.resize();A.clearRect(0,0,G,I);y.unbind();av.children().not([az,ad]).remove()}av.data("plot",aq)}function ah(){if(O.grid.hoverable){y.mousemove(aa);y.mouseleave(l)}if(O.grid.clickable){y.click(R)}an(ak.bindEvents,[y])}function ag(){if(M){clearTimeout(M)}y.unbind("mousemove",aa);y.unbind("mouseleave",l);y.unbind("click",R);an(ak.shutdown,[y])}function r(aG){function aC(aH){return aH}var aF,aB,aD=aG.options.transform||aC,aE=aG.options.inverseTransform;if(aG.direction=="x"){aF=aG.scale=h/Math.abs(aD(aG.max)-aD(aG.min));aB=Math.min(aD(aG.max),aD(aG.min))}else{aF=aG.scale=w/Math.abs(aD(aG.max)-aD(aG.min));aF=-aF;aB=Math.max(aD(aG.max),aD(aG.min))}if(aD==aC){aG.p2c=function(aH){return(aH-aB)*aF}}else{aG.p2c=function(aH){return(aD(aH)-aB)*aF}}if(!aE){aG.c2p=function(aH){return aB+aH/aF}}else{aG.c2p=function(aH){return aE(aB+aH/aF)}}}function L(aD){var aB=aD.options,aF,aJ=aD.ticks||[],aI=[],aE,aK=aB.labelWidth,aG=aB.labelHeight,aC;function aH(aM,aL){return c('<div style="position:absolute;top:-10000px;'+aL+'font-size:smaller"><div class="'+aD.direction+"Axis "+aD.direction+aD.n+'Axis">'+aM.join("")+"</div></div>").appendTo(av)}if(aD.direction=="x"){if(aK==null){aK=Math.floor(G/(aJ.length>0?aJ.length:1))}if(aG==null){aI=[];for(aF=0;aF<aJ.length;++aF){aE=aJ[aF].label;if(aE){aI.push('<div class="tickLabel" style="float:left;width:'+aK+'px">'+aE+"</div>")}}if(aI.length>0){aI.push('<div style="clear:left"></div>');aC=aH(aI,"width:10000px;");aG=aC.height();aC.remove()}}}else{if((aK==null)||(aG==null)){for(aF=0;aF<aJ.length;++aF){aE=aJ[aF].label;if(aE){aI.push('<div class="tickLabel">'+aE+"</div>")}}if(aI.length>0){aC=aH(aI,"");if(aK==null){aK=aC.children().width()}if(aG==null){aG=aC.find("div.tickLabel").height()}aC.remove()}}}if(aK==null){aK=0}if(aG==null){aG=0}aD.labelWidth=aK;aD.labelHeight=aG}function au(aD){var aC=aD.labelWidth,aL=aD.labelHeight,aH=aD.options.position,aF=aD.options.tickLength,aG=O.grid.axisMargin,aJ=O.grid.labelMargin,aK=aD.direction=="x"?p:aw,aE;var aB=c.grep(aK,function(aN){return aN&&(aN.options.position==aH)&&aN.reserveSpace});if(c.inArray(aD,aB)==aB.length-1){aG=0}if(aF==null){aF="full"}var aI=c.grep(aK,function(aN){return aN&&aN.reserveSpace});var aM=c.inArray(aD,aI)==0;if(!aM&&(aF=="full")){aF=5}if(!isNaN(+aF)){aJ+=+aF}if(aD.direction=="x"){aL+=aJ;if(aH=="bottom"){q.bottom+=aL+aG;aD.box={top:I-q.bottom,height:aL}}else{aD.box={top:q.top+aG,height:aL};q.top+=aL+aG}}else{aC+=aJ;if(aH=="left"){aD.box={left:q.left+aG,width:aC};q.left+=aC+aG}else{q.right+=aC+aG;aD.box={left:G-q.right,width:aC}}}aD.position=aH;aD.tickLength=aF;aD.box.padding=aJ;aD.innermost=aM}function U(aB){if(aB.direction=="x"){aB.box.left=q.left;aB.box.width=h}else{aB.box.top=q.top;aB.box.height=w}}function t(){var aC,aE=m();c.each(aE,function(aF,aG){aG.show=aG.options.show;if(aG.show==null){aG.show=aG.used}aG.reserveSpace=aG.show||aG.options.reserveSpace;n(aG)});allocatedAxes=c.grep(aE,function(aF){return aF.reserveSpace});q.left=q.right=q.top=q.bottom=0;if(O.grid.show){c.each(allocatedAxes,function(aF,aG){S(aG);P(aG);ap(aG,aG.ticks);L(aG)});for(aC=allocatedAxes.length-1;aC>=0;--aC){au(allocatedAxes[aC])}var aD=O.grid.minBorderMargin;if(aD==null){aD=0;for(aC=0;aC<Q.length;++aC){aD=Math.max(aD,Q[aC].points.radius+Q[aC].points.lineWidth/2)}}for(var aB in q){q[aB]+=O.grid.borderWidth;q[aB]=Math.max(aD,q[aB])}}h=G-q.left-q.right;w=I-q.bottom-q.top;c.each(aE,function(aF,aG){r(aG)});if(O.grid.show){c.each(allocatedAxes,function(aF,aG){U(aG)});k()}o()}function n(aE){var aF=aE.options,aD=+(aF.min!=null?aF.min:aE.datamin),aB=+(aF.max!=null?aF.max:aE.datamax),aH=aB-aD;if(aH==0){var aC=aB==0?1:0.01;if(aF.min==null){aD-=aC}if((aF.max==null)||(aF.min!=null)){aB+=aC}}else{var aG=aF.autoscaleMargin;if(aG!=null){if(aF.min==null){aD-=aH*aG;if((aD<0)&&(aE.datamin!=null)&&(aE.datamin>=0)){aD=0}}if(aF.max==null){aB+=aH*aG;if((aB>0)&&(aE.datamax!=null)&&(aE.datamax<=0)){aB=0}}}}aE.min=aD;aE.max=aB}function S(aG){var aM=aG.options;var aH;if((typeof aM.ticks=="number")&&(aM.ticks>0)){aH=aM.ticks}else{aH=0.3*Math.sqrt(aG.direction=="x"?G:I)}var aT=(aG.max-aG.min)/aH,aO,aB,aN,aR,aS,aQ,aI;if(aM.mode=="time"){var aJ={second:1000,minute:60*1000,hour:60*60*1000,day:24*60*60*1000,month:30*24*60*60*1000,year:365.2425*24*60*60*1000};var aK=[[1,"second"],[2,"second"],[5,"second"],[10,"second"],[30,"second"],[1,"minute"],[2,"minute"],[5,"minute"],[10,"minute"],[30,"minute"],[1,"hour"],[2,"hour"],[4,"hour"],[8,"hour"],[12,"hour"],[1,"day"],[2,"day"],[3,"day"],[0.25,"month"],[0.5,"month"],[1,"month"],[2,"month"],[3,"month"],[6,"month"],[1,"year"]];var aC=0;if(aM.minTickSize!=null){if(typeof aM.tickSize=="number"){aC=aM.tickSize}else{aC=aM.minTickSize[0]*aJ[aM.minTickSize[1]]}}for(var aS=0;aS<aK.length-1;++aS){if((aT<(aK[aS][0]*aJ[aK[aS][1]]+aK[aS+1][0]*aJ[aK[aS+1][1]])/2)&&(aK[aS][0]*aJ[aK[aS][1]]>=aC)){break}}aO=aK[aS][0];aN=aK[aS][1];if(aN=="year"){aQ=Math.pow(10,Math.floor(Math.log(aT/aJ.year)/Math.LN10));aI=(aT/aJ.year)/aQ;if(aI<1.5){aO=1}else{if(aI<3){aO=2}else{if(aI<7.5){aO=5}else{aO=10}}}aO*=aQ}aG.tickSize=aM.tickSize||[aO,aN];aB=function(aX){var a2=[],a0=aX.tickSize[0],a3=aX.tickSize[1],a1=new Date(aX.min);var aW=a0*aJ[a3];if(a3=="second"){a1.setUTCSeconds(a(a1.getUTCSeconds(),a0))}if(a3=="minute"){a1.setUTCMinutes(a(a1.getUTCMinutes(),a0))}if(a3=="hour"){a1.setUTCHours(a(a1.getUTCHours(),a0))}if(a3=="month"){a1.setUTCMonth(a(a1.getUTCMonth(),a0))}if(a3=="year"){a1.setUTCFullYear(a(a1.getUTCFullYear(),a0))}a1.setUTCMilliseconds(0);if(aW>=aJ.minute){a1.setUTCSeconds(0)}if(aW>=aJ.hour){a1.setUTCMinutes(0)}if(aW>=aJ.day){a1.setUTCHours(0)}if(aW>=aJ.day*4){a1.setUTCDate(1)}if(aW>=aJ.year){a1.setUTCMonth(0)}var a5=0,a4=Number.NaN,aY;do{aY=a4;a4=a1.getTime();a2.push(a4);if(a3=="month"){if(a0<1){a1.setUTCDate(1);var aV=a1.getTime();a1.setUTCMonth(a1.getUTCMonth()+1);var aZ=a1.getTime();a1.setTime(a4+a5*aJ.hour+(aZ-aV)*a0);a5=a1.getUTCHours();a1.setUTCHours(0)}else{a1.setUTCMonth(a1.getUTCMonth()+a0)}}else{if(a3=="year"){a1.setUTCFullYear(a1.getUTCFullYear()+a0)}else{a1.setTime(a4+aW)}}}while((a4<aX.max)&&(a4!=aY));return a2};aR=function(aV,aY){var a0=new Date(aV);if(aM.timeformat!=null){return c.plot.formatDate(a0,aM.timeformat,aM.monthNames)}var aW=aY.tickSize[0]*aJ[aY.tickSize[1]];var aX=aY.max-aY.min;var aZ=(aM.twelveHourClock)?" %p":"";if(aW<aJ.minute){fmt="%h:%M:%S"+aZ}else{if(aW<aJ.day){if(aX<2*aJ.day){fmt="%h:%M"+aZ}else{fmt="%b %d %h:%M"+aZ}}else{if(aW<aJ.month){fmt="%b %d"}else{if(aW<aJ.year){if(aX<aJ.year){fmt="%b"}else{fmt="%b %y"}}else{fmt="%y"}}}}return c.plot.formatDate(a0,fmt,aM.monthNames)}}else{var aU=aM.tickDecimals;var aP=-Math.floor(Math.log(aT)/Math.LN10);if((aU!=null)&&(aP>aU)){aP=aU}aQ=Math.pow(10,-aP);aI=aT/aQ;if(aI<1.5){aO=1}else{if(aI<3){aO=2;if((aI>2.25)&&((aU==null)||(aP+1<=aU))){aO=2.5;++aP}}else{if(aI<7.5){aO=5}else{aO=10}}}aO*=aQ;if((aM.minTickSize!=null)&&(aO<aM.minTickSize)){aO=aM.minTickSize}aG.tickDecimals=Math.max(0,aU!=null?aU:aP);aG.tickSize=aM.tickSize||aO;aB=function(aX){var aZ=[];var a0=a(aX.min,aX.tickSize),aW=0,aV=Number.NaN,aY;do{aY=aV;aV=a0+aW*aX.tickSize;aZ.push(aV);++aW}while((aV<aX.max)&&(aV!=aY));return aZ};aR=function(aV,aW){return aV.toFixed(aW.tickDecimals)}}if(aM.alignTicksWithAxis!=null){var aF=(aG.direction=="x"?p:aw)[aM.alignTicksWithAxis-1];if(aF&&aF.used&&(aF!=aG)){var aL=aB(aG);if(aL.length>0){if(aM.min==null){aG.min=Math.min(aG.min,aL[0])}if((aM.max==null)&&(aL.length>1)){aG.max=Math.max(aG.max,aL[aL.length-1])}}aB=function(aX){var aY=[],aV,aW;for(aW=0;aW<aF.ticks.length;++aW){aV=(aF.ticks[aW].v-aF.min)/(aF.max-aF.min);aV=aX.min+aV*(aX.max-aX.min);aY.push(aV)}return aY};if((aG.mode!="time")&&(aM.tickDecimals==null)){var aE=Math.max(0,-Math.floor(Math.log(aT)/Math.LN10)+1),aD=aB(aG);if(!((aD.length>1)&&/\..*0$/.test((aD[1]-aD[0]).toFixed(aE)))){aG.tickDecimals=aE}}}}aG.tickGenerator=aB;if(c.isFunction(aM.tickFormatter)){aG.tickFormatter=function(aV,aW){return""+aM.tickFormatter(aV,aW)}}else{aG.tickFormatter=aR}}function P(aF){var aH=aF.options.ticks,aG=[];if((aH==null)||((typeof aH=="number")&&(aH>0))){aG=aF.tickGenerator(aF)}else{if(aH){if(c.isFunction(aH)){aG=aH({min:aF.min,max:aF.max})}else{aG=aH}}}var aE,aB;aF.ticks=[];for(aE=0;aE<aG.length;++aE){var aC=null;var aD=aG[aE];if(typeof aD=="object"){aB=+aD[0];if(aD.length>1){aC=aD[1]}}else{aB=+aD}if(aC==null){aC=aF.tickFormatter(aB,aF)}if(!isNaN(aB)){aF.ticks.push({v:aB,label:aC})}}}function ap(aB,aC){if(aB.options.autoscaleMargin&&(aC.length>0)){if(aB.options.min==null){aB.min=Math.min(aB.min,aC[0].v)}if((aB.options.max==null)&&(aC.length>1)){aB.max=Math.max(aB.max,aC[aC.length-1].v)}}}function W(){H.clearRect(0,0,G,I);var aC=O.grid;if(aC.show&&aC.backgroundColor){N()}if(aC.show&&!aC.aboveData){ac()}for(var aB=0;aB<Q.length;++aB){an(ak.drawSeries,[H,Q[aB]]);d(Q[aB])}an(ak.draw,[H]);if(aC.show&&aC.aboveData){ac()}}function D(aB,aI){var aE,aH,aG,aD,aF=m();for(i=0;i<aF.length;++i){aE=aF[i];if(aE.direction==aI){aD=aI+aE.n+"axis";if(!aB[aD]&&(aE.n==1)){aD=aI+"axis"}if(aB[aD]){aH=aB[aD].from;aG=aB[aD].to;break}}}if(!aB[aD]){aE=aI=="x"?p[0]:aw[0];aH=aB[aI+"1"];aG=aB[aI+"2"]}if((aH!=null)&&(aG!=null)&&(aH>aG)){var aC=aH;aH=aG;aG=aC}return{from:aH,to:aG,axis:aE}}function N(){H.save();H.translate(q.left,q.top);H.fillStyle=am(O.grid.backgroundColor,w,0,"rgba(255, 255, 255, 0)");H.fillRect(0,0,h,w);H.restore()}function ac(){var aF;H.save();H.translate(q.left,q.top);var aH=O.grid.markings;if(aH){if(c.isFunction(aH)){var aK=aq.getAxes();aK.xmin=aK.xaxis.min;aK.xmax=aK.xaxis.max;aK.ymin=aK.yaxis.min;aK.ymax=aK.yaxis.max;aH=aH(aK)}for(aF=0;aF<aH.length;++aF){var aD=aH[aF],aC=D(aD,"x"),aI=D(aD,"y");if(aC.from==null){aC.from=aC.axis.min}if(aC.to==null){aC.to=aC.axis.max}if(aI.from==null){aI.from=aI.axis.min}if(aI.to==null){aI.to=aI.axis.max}if((aC.to<aC.axis.min)||(aC.from>aC.axis.max)||(aI.to<aI.axis.min)||(aI.from>aI.axis.max)){continue}aC.from=Math.max(aC.from,aC.axis.min);aC.to=Math.min(aC.to,aC.axis.max);aI.from=Math.max(aI.from,aI.axis.min);aI.to=Math.min(aI.to,aI.axis.max);if((aC.from==aC.to)&&(aI.from==aI.to)){continue}aC.from=aC.axis.p2c(aC.from);aC.to=aC.axis.p2c(aC.to);aI.from=aI.axis.p2c(aI.from);aI.to=aI.axis.p2c(aI.to);if((aC.from==aC.to)||(aI.from==aI.to)){H.beginPath();H.strokeStyle=aD.color||O.grid.markingsColor;H.lineWidth=aD.lineWidth||O.grid.markingsLineWidth;H.moveTo(aC.from,aI.from);H.lineTo(aC.to,aI.to);H.stroke()}else{H.fillStyle=aD.color||O.grid.markingsColor;H.fillRect(aC.from,aI.to,aC.to-aC.from,aI.from-aI.to)}}}var aK=m(),aM=O.grid.borderWidth;for(var aE=0;aE<aK.length;++aE){var aB=aK[aE],aG=aB.box,aQ=aB.tickLength,aN,aL,aP,aJ;if(!aB.show||(aB.ticks.length==0)){continue}H.strokeStyle=aB.options.tickColor||c.color.parse(aB.options.color).scale("a",0.22).toString();H.lineWidth=1;if(aB.direction=="x"){aN=0;if(aQ=="full"){aL=(aB.position=="top"?0:w)}else{aL=aG.top-q.top+(aB.position=="top"?aG.height:0)}}else{aL=0;if(aQ=="full"){aN=(aB.position=="left"?0:h)}else{aN=aG.left-q.left+(aB.position=="left"?aG.width:0)}}if(!aB.innermost){H.beginPath();aP=aJ=0;if(aB.direction=="x"){aP=h}else{aJ=w}if(H.lineWidth==1){aN=Math.floor(aN)+0.5;aL=Math.floor(aL)+0.5}H.moveTo(aN,aL);H.lineTo(aN+aP,aL+aJ);H.stroke()}H.beginPath();for(aF=0;aF<aB.ticks.length;++aF){var aO=aB.ticks[aF].v;aP=aJ=0;if((aO<aB.min)||(aO>aB.max)||((aQ=="full")&&(aM>0)&&((aO==aB.min)||(aO==aB.max)))){continue}if(aB.direction=="x"){aN=aB.p2c(aO);aJ=aQ=="full"?-w:aQ;if(aB.position=="top"){aJ=-aJ}}else{aL=aB.p2c(aO);aP=aQ=="full"?-h:aQ;if(aB.position=="left"){aP=-aP}}if(H.lineWidth==1){if(aB.direction=="x"){aN=Math.floor(aN)+0.5}else{aL=Math.floor(aL)+0.5}}H.moveTo(aN,aL);H.lineTo(aN+aP,aL+aJ)}H.stroke()}if(aM){H.lineWidth=aM;H.strokeStyle=O.grid.borderColor;H.strokeRect(-aM/2,-aM/2,h+aM,w+aM)}H.restore()}function k(){av.find(".tickLabels").remove();var aG=['<div class="tickLabels" style="font-size:smaller">'];var aJ=m();for(var aD=0;aD<aJ.length;++aD){var aC=aJ[aD],aF=aC.box;if(!aC.show){continue}aG.push('<div class="'+aC.direction+"Axis "+aC.direction+aC.n+'Axis" style="color:'+aC.options.color+'">');for(var aE=0;aE<aC.ticks.length;++aE){var aH=aC.ticks[aE];if(!aH.label||(aH.v<aC.min)||(aH.v>aC.max)){continue}var aK={},aI;if(aC.direction=="x"){aI="center";aK.left=Math.round(q.left+aC.p2c(aH.v)-aC.labelWidth/2);if(aC.position=="bottom"){aK.top=aF.top+aF.padding}else{aK.bottom=I-(aF.top+aF.height-aF.padding)}}else{aK.top=Math.round(q.top+aC.p2c(aH.v)-aC.labelHeight/2);if(aC.position=="left"){aK.right=G-(aF.left+aF.width-aF.padding);aI="right"}else{aK.left=aF.left+aF.padding;aI="left"}}aK.width=aC.labelWidth;var aB=["position:absolute","text-align:"+aI];for(var aL in aK){aB.push(aL+":"+aK[aL]+"px")}aG.push('<div class="tickLabel" style="'+aB.join(";")+'">'+aH.label+"</div>")}aG.push("</div>")}aG.push("</div>");av.append(aG.join(""))}function d(aB){if(aB.lines.show){at(aB)}if(aB.bars.show){e(aB)}if(aB.points.show){ao(aB)}}function at(aE){function aD(aP,aQ,aI,aU,aT){var aV=aP.points,aJ=aP.pointsize,aN=null,aM=null;H.beginPath();for(var aO=aJ;aO<aV.length;aO+=aJ){var aL=aV[aO-aJ],aS=aV[aO-aJ+1],aK=aV[aO],aR=aV[aO+1];if((aL==null)||(aK==null)){continue}if((aS<=aR)&&(aS<aT.min)){if(aR<aT.min){continue}aL=(aT.min-aS)/(aR-aS)*(aK-aL)+aL;aS=aT.min}else{if((aR<=aS)&&(aR<aT.min)){if(aS<aT.min){continue}aK=(aT.min-aS)/(aR-aS)*(aK-aL)+aL;aR=aT.min}}if((aS>=aR)&&(aS>aT.max)){if(aR>aT.max){continue}aL=(aT.max-aS)/(aR-aS)*(aK-aL)+aL;aS=aT.max}else{if((aR>=aS)&&(aR>aT.max)){if(aS>aT.max){continue}aK=(aT.max-aS)/(aR-aS)*(aK-aL)+aL;aR=aT.max}}if((aL<=aK)&&(aL<aU.min)){if(aK<aU.min){continue}aS=(aU.min-aL)/(aK-aL)*(aR-aS)+aS;aL=aU.min}else{if((aK<=aL)&&(aK<aU.min)){if(aL<aU.min){continue}aR=(aU.min-aL)/(aK-aL)*(aR-aS)+aS;aK=aU.min}}if((aL>=aK)&&(aL>aU.max)){if(aK>aU.max){continue}aS=(aU.max-aL)/(aK-aL)*(aR-aS)+aS;aL=aU.max}else{if((aK>=aL)&&(aK>aU.max)){if(aL>aU.max){continue}aR=(aU.max-aL)/(aK-aL)*(aR-aS)+aS;aK=aU.max}}if((aL!=aN)||(aS!=aM)){H.moveTo(aU.p2c(aL)+aQ,aT.p2c(aS)+aI)}aN=aK;aM=aR;H.lineTo(aU.p2c(aK)+aQ,aT.p2c(aR)+aI)}H.stroke()}function aF(aI,aQ,aP){var aW=aI.points,aV=aI.pointsize,aN=Math.min(Math.max(0,aP.min),aP.max),aX=0,aU,aT=false,aM=1,aL=0,aR=0;while(true){if((aV>0)&&(aX>aW.length+aV)){break}aX+=aV;var aZ=aW[aX-aV],aK=aW[aX-aV+aM],aY=aW[aX],aJ=aW[aX+aM];if(aT){if((aV>0)&&(aZ!=null)&&(aY==null)){aR=aX;aV=-aV;aM=2;continue}if((aV<0)&&(aX==aL+aV)){H.fill();aT=false;aV=-aV;aM=1;aX=aL=aR+aV;continue}}if((aZ==null)||(aY==null)){continue}if((aZ<=aY)&&(aZ<aQ.min)){if(aY<aQ.min){continue}aK=(aQ.min-aZ)/(aY-aZ)*(aJ-aK)+aK;aZ=aQ.min}else{if((aY<=aZ)&&(aY<aQ.min)){if(aZ<aQ.min){continue}aJ=(aQ.min-aZ)/(aY-aZ)*(aJ-aK)+aK;aY=aQ.min}}if((aZ>=aY)&&(aZ>aQ.max)){if(aY>aQ.max){continue}aK=(aQ.max-aZ)/(aY-aZ)*(aJ-aK)+aK;aZ=aQ.max}else{if((aY>=aZ)&&(aY>aQ.max)){if(aZ>aQ.max){continue}aJ=(aQ.max-aZ)/(aY-aZ)*(aJ-aK)+aK;aY=aQ.max}}if(!aT){H.beginPath();H.moveTo(aQ.p2c(aZ),aP.p2c(aN));aT=true}if((aK>=aP.max)&&(aJ>=aP.max)){H.lineTo(aQ.p2c(aZ),aP.p2c(aP.max));H.lineTo(aQ.p2c(aY),aP.p2c(aP.max));continue}else{if((aK<=aP.min)&&(aJ<=aP.min)){H.lineTo(aQ.p2c(aZ),aP.p2c(aP.min));H.lineTo(aQ.p2c(aY),aP.p2c(aP.min));continue}}var aO=aZ,aS=aY;if((aK<=aJ)&&(aK<aP.min)&&(aJ>=aP.min)){aZ=(aP.min-aK)/(aJ-aK)*(aY-aZ)+aZ;aK=aP.min}else{if((aJ<=aK)&&(aJ<aP.min)&&(aK>=aP.min)){aY=(aP.min-aK)/(aJ-aK)*(aY-aZ)+aZ;aJ=aP.min}}if((aK>=aJ)&&(aK>aP.max)&&(aJ<=aP.max)){aZ=(aP.max-aK)/(aJ-aK)*(aY-aZ)+aZ;aK=aP.max}else{if((aJ>=aK)&&(aJ>aP.max)&&(aK<=aP.max)){aY=(aP.max-aK)/(aJ-aK)*(aY-aZ)+aZ;aJ=aP.max}}if(aZ!=aO){H.lineTo(aQ.p2c(aO),aP.p2c(aK))}H.lineTo(aQ.p2c(aZ),aP.p2c(aK));H.lineTo(aQ.p2c(aY),aP.p2c(aJ));if(aY!=aS){H.lineTo(aQ.p2c(aY),aP.p2c(aJ));H.lineTo(aQ.p2c(aS),aP.p2c(aJ))}}}H.save();H.translate(q.left,q.top);H.lineJoin="round";var aG=aE.lines.lineWidth,aB=aE.shadowSize;if((aG>0)&&(aB>0)){H.lineWidth=aB;H.strokeStyle="rgba(0,0,0,0.1)";var aH=Math.PI/18;aD(aE.datapoints,Math.sin(aH)*(aG/2+aB/2),Math.cos(aH)*(aG/2+aB/2),aE.xaxis,aE.yaxis);H.lineWidth=aB/2;aD(aE.datapoints,Math.sin(aH)*(aG/2+aB/4),Math.cos(aH)*(aG/2+aB/4),aE.xaxis,aE.yaxis)}H.lineWidth=aG;H.strokeStyle=aE.color;var aC=ae(aE.lines,aE.color,0,w);if(aC){H.fillStyle=aC;aF(aE.datapoints,aE.xaxis,aE.yaxis)}if(aG>0){aD(aE.datapoints,0,0,aE.xaxis,aE.yaxis)}H.restore()}function ao(aE){function aH(aN,aM,aU,aK,aS,aT,aQ,aJ){var aR=aN.points,aI=aN.pointsize;for(var aL=0;aL<aR.length;aL+=aI){var aP=aR[aL],aO=aR[aL+1];if((aP==null)||(aP<aT.min)||(aP>aT.max)||(aO<aQ.min)||(aO>aQ.max)){continue}H.beginPath();aP=aT.p2c(aP);aO=aQ.p2c(aO)+aK;if(aJ=="circle"){H.arc(aP,aO,aM,0,aS?Math.PI:Math.PI*2,false)}else{aJ(H,aP,aO,aM,aS)}H.closePath();if(aU){H.fillStyle=aU;H.fill()}H.stroke()}}H.save();H.translate(q.left,q.top);var aG=aE.points.lineWidth,aC=aE.shadowSize,aB=aE.points.radius,aF=aE.points.symbol;if((aG>0)&&(aC>0)){var aD=aC/2;H.lineWidth=aD;H.strokeStyle="rgba(0,0,0,0.1)";aH(aE.datapoints,aB,null,aD+aD/2,true,aE.xaxis,aE.yaxis,aF);H.strokeStyle="rgba(0,0,0,0.2)";aH(aE.datapoints,aB,null,aD/2,true,aE.xaxis,aE.yaxis,aF)}H.lineWidth=aG;H.strokeStyle=aE.color;aH(aE.datapoints,aB,ae(aE.points,aE.color),0,false,aE.xaxis,aE.yaxis,aF);H.restore()}function E(aN,aM,aV,aI,aQ,aF,aD,aL,aK,aU,aR,aC){var aE,aT,aJ,aP,aG,aB,aO,aH,aS;if(aR){aH=aB=aO=true;aG=false;aE=aV;aT=aN;aP=aM+aI;aJ=aM+aQ;if(aT<aE){aS=aT;aT=aE;aE=aS;aG=true;aB=false}}else{aG=aB=aO=true;aH=false;aE=aN+aI;aT=aN+aQ;aJ=aV;aP=aM;if(aP<aJ){aS=aP;aP=aJ;aJ=aS;aH=true;aO=false}}if((aT<aL.min)||(aE>aL.max)||(aP<aK.min)||(aJ>aK.max)){return}if(aE<aL.min){aE=aL.min;aG=false}if(aT>aL.max){aT=aL.max;aB=false}if(aJ<aK.min){aJ=aK.min;aH=false}if(aP>aK.max){aP=aK.max;aO=false}aE=aL.p2c(aE);aJ=aK.p2c(aJ);aT=aL.p2c(aT);aP=aK.p2c(aP);if(aD){aU.beginPath();aU.moveTo(aE,aJ);aU.lineTo(aE,aP);aU.lineTo(aT,aP);aU.lineTo(aT,aJ);aU.fillStyle=aD(aJ,aP);aU.fill()}if((aC>0)&&(aG||aB||aO||aH)){aU.beginPath();aU.moveTo(aE,aJ+aF);if(aG){aU.lineTo(aE,aP+aF)}else{aU.moveTo(aE,aP+aF)}if(aO){aU.lineTo(aT,aP+aF)}else{aU.moveTo(aT,aP+aF)}if(aB){aU.lineTo(aT,aJ+aF)}else{aU.moveTo(aT,aJ+aF)}if(aH){aU.lineTo(aE,aJ+aF)}else{aU.moveTo(aE,aJ+aF)}aU.stroke()}}function e(aD){function aC(aJ,aI,aL,aG,aK,aN,aM){var aO=aJ.points,aF=aJ.pointsize;for(var aH=0;aH<aO.length;aH+=aF){if(aO[aH]==null){continue}E(aO[aH],aO[aH+1],aO[aH+2],aI,aL,aG,aK,aN,aM,H,aD.bars.horizontal,aD.bars.lineWidth)}}H.save();H.translate(q.left,q.top);H.lineWidth=aD.bars.lineWidth;H.strokeStyle=aD.color;var aB=aD.bars.align=="left"?0:-aD.bars.barWidth/2;var aE=aD.bars.fill?function(aF,aG){return ae(aD.bars,aD.color,aF,aG)}:null;aC(aD.datapoints,aB,aB+aD.bars.barWidth,0,aE,aD.xaxis,aD.yaxis);H.restore()}function ae(aD,aB,aC,aF){var aE=aD.fill;if(!aE){return null}if(aD.fillColor){return am(aD.fillColor,aC,aF,aB)}var aG=c.color.parse(aB);aG.a=typeof aE=="number"?aE:0.4;aG.normalize();return aG.toString()}function o(){av.find(".legend").remove();if(!O.legend.show){return}var aH=[],aF=false,aN=O.legend.labelFormatter,aM,aJ;for(var aE=0;aE<Q.length;++aE){aM=Q[aE];aJ=aM.label;if(!aJ){continue}if(aE%O.legend.noColumns==0){if(aF){aH.push("</tr>")}aH.push("<tr>");aF=true}if(aN){aJ=aN(aJ,aM)}aH.push('<td class="legendColorBox"><div style="border:1px solid '+O.legend.labelBoxBorderColor+';padding:1px"><div style="width:4px;height:0;border:5px solid '+aM.color+';overflow:hidden"></div></div></td><td class="legendLabel">'+aJ+"</td>")}if(aF){aH.push("</tr>")}if(aH.length==0){return}var aL='<table style="font-size:smaller;color:'+O.grid.color+'">'+aH.join("")+"</table>";if(O.legend.container!=null){c(O.legend.container).html(aL)}else{var aI="",aC=O.legend.position,aD=O.legend.margin;if(aD[0]==null){aD=[aD,aD]}if(aC.charAt(0)=="n"){aI+="top:"+(aD[1]+q.top)+"px;"}else{if(aC.charAt(0)=="s"){aI+="bottom:"+(aD[1]+q.bottom)+"px;"}}if(aC.charAt(1)=="e"){aI+="right:"+(aD[0]+q.right)+"px;"}else{if(aC.charAt(1)=="w"){aI+="left:"+(aD[0]+q.left)+"px;"}}var aK=c('<div class="legend">'+aL.replace('style="','style="position:absolute;'+aI+";")+"</div>").appendTo(av);if(O.legend.backgroundOpacity!=0){var aG=O.legend.backgroundColor;if(aG==null){aG=O.grid.backgroundColor;if(aG&&(typeof aG=="string")){aG=c.color.parse(aG)}else{aG=c.color.extract(aK,"background-color")}aG.a=1;aG=aG.toString()}var aB=aK.children();c('<div style="position:absolute;width:'+aB.width()+"px;height:"+aB.height()+"px;"+aI+"background-color:"+aG+';"> </div>').prependTo(aK).css("opacity",O.legend.backgroundOpacity)}}}var ab=[],M=null;function K(aI,aG,aD){var aO=O.grid.mouseActiveRadius,a0=aO*aO+1,aY=null,aR=false,aW,aU;for(aW=Q.length-1;aW>=0;--aW){if(!aD(Q[aW])){continue}var aP=Q[aW],aH=aP.xaxis,aF=aP.yaxis,aV=aP.datapoints.points,aT=aP.datapoints.pointsize,aQ=aH.c2p(aI),aN=aF.c2p(aG),aC=aO/aH.scale,aB=aO/aF.scale;if(aH.options.inverseTransform){aC=Number.MAX_VALUE}if(aF.options.inverseTransform){aB=Number.MAX_VALUE}if(aP.lines.show||aP.points.show){for(aU=0;aU<aV.length;aU+=aT){var aK=aV[aU],aJ=aV[aU+1];if(aK==null){continue}if((aK-aQ>aC)||(aK-aQ<-aC)||(aJ-aN>aB)||(aJ-aN<-aB)){continue}var aM=Math.abs(aH.p2c(aK)-aI),aL=Math.abs(aF.p2c(aJ)-aG),aS=aM*aM+aL*aL;if(aS<a0){a0=aS;aY=[aW,aU/aT]}}}if(aP.bars.show&&!aY){var aE=aP.bars.align=="left"?0:-aP.bars.barWidth/2,aX=aE+aP.bars.barWidth;for(aU=0;aU<aV.length;aU+=aT){var aK=aV[aU],aJ=aV[aU+1],aZ=aV[aU+2];if(aK==null){continue}if(Q[aW].bars.horizontal?((aQ<=Math.max(aZ,aK))&&(aQ>=Math.min(aZ,aK))&&(aN>=aJ+aE)&&(aN<=aJ+aX)):((aQ>=aK+aE)&&(aQ<=aK+aX)&&(aN>=Math.min(aZ,aJ))&&(aN<=Math.max(aZ,aJ)))){aY=[aW,aU/aT]}}}}if(aY){aW=aY[0];aU=aY[1];aT=Q[aW].datapoints.pointsize;return{datapoint:Q[aW].datapoints.points.slice(aU*aT,(aU+1)*aT),dataIndex:aU,series:Q[aW],seriesIndex:aW}}return null}function aa(aB){if(O.grid.hoverable){u("plothover",aB,function(aC){return aC.hoverable!=false})}}function l(aB){if(O.grid.hoverable){u("plothover",aB,function(aC){return false})}}function R(aB){u("plotclick",aB,function(aC){return aC.clickable!=false})}function u(aC,aB,aD){var aE=y.offset(),aH=aB.pageX-aE.left-q.left,aF=aB.pageY-aE.top-q.top,aJ=C({left:aH,top:aF});aJ.pageX=aB.pageX;aJ.pageY=aB.pageY;var aK=K(aH,aF,aD);if(aK){aK.pageX=parseInt(aK.series.xaxis.p2c(aK.datapoint[0])+aE.left+q.left);aK.pageY=parseInt(aK.series.yaxis.p2c(aK.datapoint[1])+aE.top+q.top)}if(O.grid.autoHighlight){for(var aG=0;aG<ab.length;++aG){var aI=ab[aG];if((aI.auto==aC)&&!(aK&&(aI.series==aK.series)&&(aI.point[0]==aK.datapoint[0])&&(aI.point[1]==aK.datapoint[1]))){T(aI.series,aI.point)}}if(aK){x(aK.series,aK.datapoint,aC)}}av.trigger(aC,[aJ,aK])}function f(){if(!M){M=setTimeout(s,30)}}function s(){M=null;A.save();A.clearRect(0,0,G,I);A.translate(q.left,q.top);var aC,aB;for(aC=0;aC<ab.length;++aC){aB=ab[aC];if(aB.series.bars.show){v(aB.series,aB.point)}else{ay(aB.series,aB.point)}}A.restore();an(ak.drawOverlay,[A])}function x(aD,aB,aF){if(typeof aD=="number"){aD=Q[aD]}if(typeof aB=="number"){var aE=aD.datapoints.pointsize;aB=aD.datapoints.points.slice(aE*aB,aE*(aB+1))}var aC=al(aD,aB);if(aC==-1){ab.push({series:aD,point:aB,auto:aF});f()}else{if(!aF){ab[aC].auto=false}}}function T(aD,aB){if((aD==null)&&(aB==null)){ab=[];f()}if(typeof aD=="number"){aD=Q[aD]}if(typeof aB=="number"){aB=aD.data[aB]}var aC=al(aD,aB);if(aC!=-1){ab.splice(aC,1);f()}}function al(aD,aE){for(var aB=0;aB<ab.length;++aB){var aC=ab[aB];if((aC.series==aD)&&(aC.point[0]==aE[0])&&(aC.point[1]==aE[1])){return aB}}return -1}function ay(aE,aD){var aC=aD[0],aI=aD[1],aH=aE.xaxis,aG=aE.yaxis;if((aC<aH.min)||(aC>aH.max)||(aI<aG.min)||(aI>aG.max)){return}var aF=aE.points.radius+aE.points.lineWidth/2;A.lineWidth=aF;A.strokeStyle=c.color.parse(aE.color).scale("a",0.5).toString();var aB=1.5*aF,aC=aH.p2c(aC),aI=aG.p2c(aI);A.beginPath();if(aE.points.symbol=="circle"){A.arc(aC,aI,aB,0,2*Math.PI,false)}else{aE.points.symbol(A,aC,aI,aB,false)}A.closePath();A.stroke()}function v(aE,aB){A.lineWidth=aE.bars.lineWidth;A.strokeStyle=c.color.parse(aE.color).scale("a",0.5).toString();var aD=c.color.parse(aE.color).scale("a",0.5).toString();var aC=aE.bars.align=="left"?0:-aE.bars.barWidth/2;E(aB[0],aB[1],aB[2]||0,aC,aC+aE.bars.barWidth,0,function(){return aD},aE.xaxis,aE.yaxis,A,aE.bars.horizontal,aE.bars.lineWidth)}function am(aJ,aB,aH,aC){if(typeof aJ=="string"){return aJ}else{var aI=H.createLinearGradient(0,aH,0,aB);for(var aE=0,aD=aJ.colors.length;aE<aD;++aE){var aF=aJ.colors[aE];if(typeof aF!="string"){var aG=c.color.parse(aC);if(aF.brightness!=null){aG=aG.scale("rgb",aF.brightness)}if(aF.opacity!=null){aG.a*=aF.opacity}aF=aG.toString()}aI.addColorStop(aE/(aD-1),aF)}return aI}}}c.plot=function(g,e,d){var f=new b(c(g),e,d,c.plot.plugins);return f};c.plot.version="0.7";c.plot.plugins=[];c.plot.formatDate=function(l,f,h){var o=function(d){d=""+d;return d.length==1?"0"+d:d};var e=[];var p=false,j=false;var n=l.getUTCHours();var k=n<12;if(h==null){h=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]}if(f.search(/%p|%P/)!=-1){if(n>12){n=n-12}else{if(n==0){n=12}}}for(var g=0;g<f.length;++g){var m=f.charAt(g);if(p){switch(m){case"h":m=""+n;break;case"H":m=o(n);break;case"M":m=o(l.getUTCMinutes());break;case"S":m=o(l.getUTCSeconds());break;case"d":m=""+l.getUTCDate();break;case"m":m=""+(l.getUTCMonth()+1);break;case"y":m=""+l.getUTCFullYear();break;case"b":m=""+h[l.getUTCMonth()];break;case"p":m=(k)?("am"):("pm");break;case"P":m=(k)?("AM"):("PM");break;case"0":m="";j=true;break}if(m&&j){m=o(m);j=false}e.push(m);if(!j){p=false}}else{if(m=="%"){p=true}else{e.push(m)}}}return e.join("")};function a(e,d){return d*Math.floor(e/d)}})(jQuery);
