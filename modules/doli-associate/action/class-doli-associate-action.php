<?php
/**
 * Gestion des actions d'association des entités avec dolibarr.
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2011-2019 Eoxia <dev@eoxia.com>.
 *
 * @license   AGPLv3 <https://spdx.org/licenses/AGPL-3.0-or-later.html>
 *
 * @package   WPshop\Classes
 *
 * @since     2.0.0
 */

namespace wpshop;

defined( 'ABSPATH' ) || exit;

/**
 * Doli Associate Action Class.
 */
class Doli_Associate_Action {

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_load_associate_modal', array( $this, 'load_associate_modal' ) );
		add_action( 'wp_ajax_load_compare_modal', array( $this, 'load_compare_modal' ) );
	}

	/**
	 * Charges la modal d'association.
	 *
	 * @since 2.0.0
	 */
	public function load_associate_modal() {
		check_ajax_referer( 'load_associate_modal' );

		$wp_id = ! empty( $_POST['wp_id'] ) ? (int) $_POST['wp_id'] : 0;
		$route = ! empty( $_POST['route'] ) ? sanitize_text_field( $_POST['route'] ) : '';
		$type  = ! empty( $_POST['wp_type'] ) ? sanitize_text_field( $_POST['wp_type'] ) : '';
		$type  = str_replace( '_Class', '', str_replace( '\\\\', '\\', $type ) );

		$post_type        = get_post_type( $wp_id );
		$post_type_object = get_post_type_object( $post_type );

		$entries = Request_Util::get( $route . '?limit=-1' );

		if ( ! empty( $entries ) ) {
			foreach ( $entries as $key => $entry ) {
				$wp_entry = $type::g()->get( array(
					'meta_key'   => '_external_id',
					'meta_value' => (int) $entry->id,
				), true );

				if ( ! empty( $wp_entry ) ) {
					unset( $entries[ $key ] );
				}
			}
		}

		ob_start();
		\eoxia\View_Util::exec( 'wpshop', 'doli-associate', 'main', array(
			'entries' => $entries,
			'wp_id'   => $wp_id,
			'route'   => $route,
			'type'    => $type,
			'label'   => $post_type_object->labels->singular_name,
		) );
		$view = ob_get_clean();

		ob_start();
		\eoxia\View_Util::exec( 'wpshop', 'doli-associate', 'single-footer' );
		$buttons_view = ob_get_clean();

		wp_send_json_success( array(
			'view'         => $view,
			'buttons_view' => $buttons_view,
		) );
	}

	/**
	 * Charges la modal pour comparer les données de WordPress et Dolibarr.
	 *
	 * @since 2.0.0
	 */
	public function load_compare_modal() {
		check_ajax_referer( 'load_compare_modal' );

		$wp_id    = ! empty( $_POST['wp_id'] ) ? (int) $_POST['wp_id'] : 0;
		$entry_id = ! empty( $_POST['entry_id'] ) ? (int) $_POST['entry_id'] : 0;
		$route    = ! empty( $_POST['route'] ) ? sanitize_text_field( $_POST['route'] ) : '';
		$type     = ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';

		if ( empty( $entry_id ) ) {
			wp_send_json_success( array(
				'namespace'        => 'wpshop',
				'module'           => 'doliAssociate',
				'callback_success' => 'openModalCompareSuccess',
				'error'            => array(
					'status'  => true,
					'message' => __( 'Please select an entry', 'wpshop' ),
				),
			) );
		}

		$type = str_replace( '\\\\', '\\', $type );

		$wp_entry         = $type::g()->get( array( 'id' => $wp_id ), true );
		$doli_entry       = Request_Util::get( $route . '/' . $entry_id );
		$doli_to_wp_entry = $type::g()->get( array( 'schema' => true ), true );

		if ( 'Product' == $type ) {
			$route = 'products' == $route ? 'wpshopapi/product/get/web' : 'products';
		}

		switch ( $route ) {
			case 'thirdparties':
				$wp_entry->data['contacts'] = array();

				if ( ! empty( $wp_entry->data['contact_ids'] ) ) {
					$wp_entry->data['contacts'] = Contact::g()->get( array( 'include' => $wp_entry->data['contact_ids'] ) );
				}

				$doli_to_wp_entry = Doli_Third_Parties::g()->doli_to_wp( $doli_entry, $doli_to_wp_entry, false );

				$doli_to_wp_entry->data['contacts'] = Request_Util::get( 'contacts?sortfield=t.rowid&sortorder=ASC&limit=-1&thirdparty_ids=' . $doli_to_wp_entry->data['external_id'] );
				if ( ! empty( $doli_to_wp_entry->data['contacts'] ) ) {
					foreach ( $doli_to_wp_entry->data['contacts'] as &$contact ) {
						$wp_contact = Contact::g()->get( array( 'schema' => true ), true );
						$contact    = Doli_Contact::g()->doli_to_wp( $contact, $wp_contact, false );
					}
				}
				break;
			case 'wpshopapi/product/get/web':
				$route            = 'products';
				$doli_entry       = Request_Util::get( $route . '/' . $entry_id );
				$doli_to_wp_entry = Doli_Products::g()->doli_to_wp( $doli_entry, $doli_to_wp_entry, false );
				break;
			default:
				break;
		}

		$entries = array(
			'wordpress' => array( // WPCS: spelling ok.
				'title' => __( 'WordPress', 'wpshop' ),
				'data'  => $wp_entry->data,
				'id'    => $wp_entry->data['id'],
			),
			'dolibarr'  => array(
				'title' => __( 'Dolibarr', 'wpshop' ),
				'data'  => $doli_to_wp_entry->data,
				'id'    => $entry_id,
			),
		);

		ob_start();
		\eoxia\View_Util::exec( 'wpshop', 'doli-associate', 'compare-' . $route, array(
			'entries' => $entries,
			'type'    => $type,
			'route'   => $route,
		) );
		$view = ob_get_clean();

		ob_start();
		\eoxia\View_Util::exec( 'wpshop', 'doli-associate', 'compare-footer' );
		$footer_view = ob_get_clean();

		wp_send_json_success( array(
			'namespace'        => 'wpshop',
			'module'           => 'doliAssociate',
			'callback_success' => 'openModalCompareSuccess',
			'view'             => $view,
			'footer_view'      => $footer_view,
		) );
	}
}

new Doli_Associate_Action();
