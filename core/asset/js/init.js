/**
 * Initialise l'objet principale de WPshop.
 *
 * @since 2.0.0
 */
window.eoxiaJS.wpshop = {};
window.eoxiaJS.wpshopFrontend = {};

window.eoxiaJS.wpshop.init = function() {
	if ( jQuery( '.wps-sync[data-associate=true]' ).length ) {
		jQuery( '.wps-sync[data-associate=true]' ).each( function() {
			var data = {
				action: 'check_sync_statut',
				id: jQuery( this ).data( 'id' )
			};

			window.eoxiaJS.loader.display(jQuery( this ));
			var _this = jQuery( this );

			jQuery.post( ajaxurl, data, function( response ) {
				window.eoxiaJS.loader.remove(_this);
				if ( ! response.data.sync ) {
					_this.find( '.statut' ).addClass( 'statut-orange' );
				} else {
					_this.find( '.statut' ).addClass( 'statut-green' );
				}
			} );
		} );
	}

	var data = {
		action: 'check_erp_statut',
		_wpnonce: scriptParams.check_erp_statut_nonce
	};

	jQuery.post( ajaxurl, data, function( response ) {
		if ( ! response.data.statut && response.data.view ) {
			jQuery( 'body' ).append( response.data.view );
		}
	} );
};
