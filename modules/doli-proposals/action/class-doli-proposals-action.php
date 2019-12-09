<?php
/**
 * Gestion des actions des devis venant de dolibarr.
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
 * Doli Porposals Action.
 */
class Doli_Proposals_Action {

	/**
	 * Constructeur.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'wps_checkout_create_proposal', array( $this, 'checkout_create_proposal' ), 1, 1 );
		add_action( 'wps_checkout_update_proposal', array( $this, 'checkout_update_proposal' ), 1, 1 );

		add_action( 'admin_post_wps_download_proposal', array( $this, 'download_proposal' ) );

		add_action( 'admin_post_convert_to_order_and_pay', array( $this, 'convert_to_order_and_pay' ) );

		add_action( 'wps_payment_complete', array( $this, 'set_to_billed' ), 40, 1 );
	}

	/**
	 * Créer le devis vers dolibarr.
	 *
	 * @since 2.0.0
	 *
	 * @param  Proposal_Model $wp_proposal Les données du devis.
	 */
	public function checkout_create_proposal( $wp_proposal ) {
		if ( Settings::g()->dolibarr_is_active() ) {
			$doli_proposal_id = Doli_Proposals::g()->wp_to_doli( $wp_proposal );

			$doli_proposal = Request_Util::post( 'proposals/' . (int) $doli_proposal_id . '/validate', array(
				'notrigger' => 1,
			) );

			$doli_proposal = Request_Util::post( 'proposals/' . (int) $doli_proposal_id . '/close', array(
				'status'    => 2,
				'notrigger' => 1,
			) );

			Request_Util::put( 'documents/builddoc', array(
				'module_part'   => 'propal',
				'original_file' => $doli_proposal->ref . '/' . $doli_proposal->ref . '.pdf',
			) );

			update_post_meta( $wp_proposal->data['id'], '_external_id', $doli_proposal_id );

			Doli_Proposals::g()->doli_to_wp( $doli_proposal, $wp_proposal );

			$wpshop_object = Request_Util::post( 'wpshop/object', array(
				'wp_id'   => $wp_proposal->data['id'],
				'doli_id' => $doli_proposal_id,
				'type'    => 'propal',
			) );

			update_post_meta( $wp_proposal->data['id'], '_date_last_synchro', $wpshop_object->last_sync_date );

			// translators: Create proposal on dolibarr: {json_data} and create PDF.
			\eoxia\LOG_Util::log( sprintf( 'Create proposal on dolibarr: %s and create PDF', json_encode( $doli_proposal ) ), 'wpshop2' );
		}
	}

	/**
	 * Met à jour le devis avec le mode de règlement.
	 *
	 * @since 2.0.0
	 *
	 * @param  Proposal_Model $wp_proposal Les données du devis.
	 */
	public function checkout_update_proposal( $wp_proposal ) {
		if ( Settings::g()->dolibarr_is_active() ) {
			$doli_proposal = Request_Util::put( 'proposals/' . $wp_proposal->data['external_id'], array(
				'mode_reglement_id' => Doli_Payment::g()->convert_to_doli_id( $wp_proposal->data['payment_method'] ),
			) );

			// translators: Update method payment for proposal {json_data}.
			\eoxia\LOG_Util::log( sprintf( 'Update method payment for proposal %s', json_encode( $doli_proposal ) ), 'wpshop2' );
		}
	}

	/**
	 * Télécharges le devis.
	 *
	 * @since 2.0.0
	 */
	public function download_proposal() {
		check_admin_referer( 'download_proposal' );

		$proposal_id = ! empty( $_GET['proposal_id'] ) ? (int) $_GET['proposal_id'] : 0;

		if ( ! $proposal_id ) {
			exit;
		}

		$contact     = Contact::g()->get( array( 'id' => get_current_user_id() ), true );
		$third_party = Third_Party::g()->get( array( 'id' => $contact->data['third_party_id'] ), true );
		$proposal    = Proposals::g()->get( array( 'id' => $proposal_id ), true );

		if ( ( isset( $third_party->data ) && $proposal->data['parent_id'] !== $third_party->data['id'] ) && ! current_user_can( 'administrator' ) ) {
			exit;
		}

		// tanslators: Contact test download proposal test@eoxia.com.
		\eoxia\LOG_Util::log( sprintf( 'Contact %s download proposal %s', $proposal->data['title'], $contact->data['email'] ), 'wpshop2' );

		$proposal_file = Request_Util::get( 'documents/download?module_part=propale&original_file=' . $proposal->data['title'] . '/' . $proposal->data['title'] . '.pdf' );
		$content       = base64_decode( $proposal_file->content );

		header( 'Cache-Control: no-cache' );
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: inline; filename="' . $proposal->data['title'] . '.pdf"' );
		header( 'Content-Length: ' . strlen( $content ) );

		echo $content;

		exit;
	}

	/**
	 * Create Order from Proposal and go to pay page.
	 *
	 * @since 2.0.0
	 *
	 * @todo: nonce
	 */
	public function convert_to_order_and_pay() {
		$id = ! empty( $_GET['proposal_id'] ) ? (int) $_GET['proposal_id'] : 0;

		if ( empty( $id ) ) {
			wp_die();
		}

		$wp_proposal = Proposals::g()->get( array( 'id' => $id ), true );

		$doli_proposal_id = get_post_meta( $wp_proposal->data['id'], '_external_id', true );

		$doli_order = Request_Util::post( 'orders/createfromproposal/' . $doli_proposal_id );
		$doli_order = Request_Util::post( 'orders/' . $doli_order->id . '/validate' );

		$wp_order = Doli_Order::g()->get( array( 'schema' => true ), true );
		$wp_order = Doli_Order::g()->doli_to_wp( $doli_order, $wp_order );

		$data = array(
			'doli_id' => $doli_order->id,
			'wp_id'   => $wp_order->data['id'],
			'type'    => 'order',
		);

		Request_Util::post( 'wpshop/object', $data );

		$current_user = wp_get_current_user();
		Checkout::g()->reorder( $wp_order->data['id'] );
		$wp_order->data['total_price_no_shipping'] = Cart_Session::g()->total_price_no_shipping;
		$wp_order->data['tva_amount']              = Cart_Session::g()->tva_amount;
		$wp_order->data['shipping_cost']           = Cart_Session::g()->shipping_cost;
		$wp_order->data['author_id']               = $current_user->ID;

		$order = Doli_Order::g()->update( $wp_order->data );

		Checkout::g()->do_pay( $order->data['id'] );

		wp_redirect( Pages::g()->get_checkout_link() . '/pay/' . $order->data['id'] );
	}

	public function set_to_billed( $data ) {
		$wp_order = Doli_Order::g()->get( array( 'id' => (int) $data['custom'] ), true );

		$proposals = Proposals::g()->get( array( 'post__in' => $wp_order->data['linked_objects_ids']['propal'] ) );

		if ( ! empty( $proposals ) ) {
			foreach ( $proposals as $proposal ) {
				$doli_proposal = Request_Util::post( 'proposals/' . $proposal->data['external_id'] . '/setinvoiced' );
				Doli_Proposals::g()->doli_to_wp( $doli_proposal, $proposal );

				$wpshop_object = Request_Util::post( 'wpshop/object', array(
					'wp_id'   => $proposal->data['id'],
					'doli_id' => $doli_proposal->id,
					'type'    => 'propal',
				) );

				update_post_meta( $proposal->data['id'], '_date_last_synchro', $wpshop_object->last_sync_date );
			}
		}
	}
}

new Doli_Proposals_Action();
