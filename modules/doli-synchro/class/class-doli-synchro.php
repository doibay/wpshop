<?php
/**
 * Les fonctions principales des synchronisations.
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2011-2018 Eoxia <dev@eoxia.com>.
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
 *  Doli Synchro Class.
 */
class Doli_Synchro extends \eoxia\Singleton_Util {

	/**
	 * Tableau contenant les synchronisations à effectuer.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $sync_infos = array();

	/**
	 * Limites de synchronisation par requête.
	 *
	 * @since 2.0.0
	 *
	 * @var integer
	 */
	public $limit_entries_by_request = 50;

	/**
	 * Constructeur.
	 *
	 * @since 2.0.0
	 */
	protected function construct() {
		$this->sync_infos = array(
			'third-parties' => array(
				'title'    => __( 'Third parties', 'wpshop' ),
				'action'   => 'sync_third_parties',
				'nonce'    => 'sync_third_parties',
				'endpoint' => 'thirdparties?limit=-1',
			),
			'contacts'      => array(
				'title'    => __( 'Contacts', 'wpshop' ),
				'action'   => 'sync_contacts',
				'nonce'    => 'sync_contacts',
				'endpoint' => 'contacts?limit=-1',
			),
			'products'      => array(
				'title'    => __( 'Products', 'wpshop' ),
				'action'   => 'sync_products',
				'nonce'    => 'sync_products',
				'endpoint' => 'products?limit=-1',
			),
			'proposals'     => array(
				'title'    => __( 'Proposals', 'wpshop' ),
				'action'   => 'sync_proposals',
				'nonce'    => 'sync_proposals',
				'endpoint' => 'proposals?limit=-1',
			),
			'orders'        => array(
				'title'    => __( 'Orders', 'wpshop' ),
				'action'   => 'sync_orders',
				'nonce'    => 'sync_orders',
				'endpoint' => 'orders?limit=-1',
			),
			'invoices'      => array(
				'title'    => __( 'Invoices', 'wpshop' ),
				'action'   => 'sync_invoices',
				'nonce'    => 'sync_invoices',
				'endpoint' => 'invoices?limit=-1',
			),
			'payments'      => array(
				'title'    => __( 'Payments', 'wpshop' ),
				'action'   => 'sync_payments',
				'nonce'    => 'sync_payments',
				'endpoint' => 'invoices?limit=-1', // Total is invoice too.
			),
		);
	}

	/**
	 * Associes et synchronises les données d'une entité.
	 *
	 * @since 2.0.0
	 *
	 * @param  string  $from     De quel coté la synchro ? WordPress ou Dolibarr.
	 * @param  integer $wp_id    L'ID de l'entitée sur WordPress.
	 * @param  integer $entry_id L'ID de l'entitée sur Dolibarr.
	 *
	 * @return void
	 */
	public function associate_and_synchronize( $from, $wp_id, $entry_id ) {
		$post_type = get_post_type( $wp_id );

		switch ( $post_type ) {
			case 'wps-third-party':
				if ( 'dolibarr' === $from ) {
					$doli_third_party = Request_Util::get( 'thirdparties/' . $entry_id );
					$wp_third_party   = Third_Party::g()->get( array( 'id' => $wp_id ), true );

					Doli_Third_Parties::g()->doli_to_wp( $doli_third_party, $wp_third_party );
				}

				if ( 'wp' === $from ) {
					$wp_third_party   = Third_Party::g()->get( array( 'id' => $wp_id ), true );
					$doli_third_party = Request_Util::get( 'thirdparties/' . $entry_id );

					Doli_Third_Parties::g()->wp_to_doli( $wp_third_party, $doli_third_party );
				}
				break;
			case 'wps-product':
				if ( 'dolibarr' === $from ) {
					$doli_product = Request_Util::get( 'products/' . $entry_id );
					$wp_product   = Product::g()->get( array( 'id' => $wp_id ), true );

					Doli_Products::g()->doli_to_wp( $doli_product, $wp_product );

					Request_Util::post( 'wpshopapi/associate/product', array(
						'wp_product' => $wp_id,
						'fk_product' => $entry_id,
					) );
				}

				if ( 'wp' === $from ) {
					$wp_product   = Product::g()->get( array( 'id' => $wp_id ), true );
					$doli_product = Request_Util::get( 'products/' . $entry_id );

					update_post_meta( $wp_id, '_external_id', $entry_id );
					$wp_product->data['external_id'] = $entry_id;
					Doli_Products::g()->wp_to_doli( $wp_product, $doli_product );
				}
				break;
			case 'wps-order':
				if ( 'dolibarr' === $from ) {
					$doli_order = Request_Util::get( 'orders/' . $entry_id );
					$wp_order   = Doli_Order::g()->get( array( 'id' => $wp_id ), true );

					Doli_Order::g()->doli_to_wp( $doli_order, $wp_order );

					Request_Util::post( 'wpshopapi/associate/order', array(
						'wp_product' => $wp_id,
						'fk_product' => $entry_id,
					) );

					// Facture
					if ( ! empty( $doli_order->linkedObjectsIds->facture ) ) {
						foreach ( $doli_order->linkedObjectsIds->facture as $facture_id ) {
							$doli_invoice = Request_Util::get( 'invoices/' . $facture_id );

							$wp_invoice = Doli_Invoice::g()->get( array(
								'meta_key'   => '_external_id',
								'meta_value' => (int) $facture_id,
							), true );

							if ( empty( $wp_invoice ) ) {
								$wp_invoice = Doli_Invoice::g()->get( array( 'schema' => true ), true );
							}

							Doli_Invoice::g()->doli_to_wp( $doli_invoice, $wp_invoice );
						}
					}

					// Règlement
				}
				break;
			default:
				break;
		}

		update_post_meta( $wp_id, '_last_sync', current_time( 'mysql' ) );
	}
}

Doli_Synchro::g();
