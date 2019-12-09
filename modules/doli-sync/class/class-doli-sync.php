<?php
/**
 * Les fonctions principales des synchronisations.
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
 *  Doli Synchro Class.
 */
class Doli_Sync extends \eoxia\Singleton_Util {

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
				'endpoint' => 'thirdparties',
			),
			'contacts'      => array(
				'title'    => __( 'Contacts', 'wpshop' ),
				'action'   => 'sync_contacts',
				'nonce'    => 'sync_contacts',
				'endpoint' => 'contacts',
			),
			'products'      => array(
				'title'    => __( 'Products', 'wpshop' ),
				'action'   => 'sync_products',
				'nonce'    => 'sync_products',
				'endpoint' => 'wpshop/object/get/web',
			),
			'proposals'     => array(
				'title'    => __( 'Proposals', 'wpshop' ),
				'action'   => 'sync_proposals',
				'nonce'    => 'sync_proposals',
				'endpoint' => 'proposals',
			),
			'orders'        => array(
				'title'    => __( 'Orders', 'wpshop' ),
				'action'   => 'sync_orders',
				'nonce'    => 'sync_orders',
				'endpoint' => 'orders',
			),
			'invoices'      => array(
				'title'    => __( 'Invoices', 'wpshop' ),
				'action'   => 'sync_invoices',
				'nonce'    => 'sync_invoices',
				'endpoint' => 'invoices',
			),
			'payments'      => array(
				'title'    => __( 'Payments', 'wpshop' ),
				'action'   => 'sync_payments',
				'nonce'    => 'sync_payments',
				'endpoint' => 'invoices', // Total is invoice too.
			),
		);
	}

	/**
	 * Compte le nombre d'entrée.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $sync_info Les informations de synchro.
	 *
	 * @return array            Les informations de synchro avec le nombre
	 * total d'élement en plus.
	 */
	public function count_entries( $sync_info ) {
		if ( ! empty( $sync_info['endpoint'] ) ) {
			$args = array(
				'limit' => $this->limit_entries_by_request,
				'page'  => $sync_info['page'],
			);

			$args = implode( '&', array_map( function( $v, $k ) {
				return $k . '=' . $v;
			}, $args, array_keys( $args ) ) );

			$tmp = Request_Util::get( $sync_info['endpoint'] . '?' . $args );

			if ( $tmp ) {
				$count                      = count( $tmp );
				$sync_info['total_number'] += count( $tmp );

				if ( $count >= $this->limit_entries_by_request ) {
					$sync_info['page']++;
					$sync_info = $this->count_entries( $sync_info );
				}
			}
		}

		return $sync_info;
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
	 * @return array             Les informations de la société.
	 */
	public function associate_and_synchronize( $from, $wp_id, $entry_id ) {
		$post_type = get_post_type( $wp_id );

		$wp_error = new \WP_Error();
		$messages = array();

		switch ( $post_type ) {
			case 'wps-third-party':
				if ( 'dolibarr' === $from ) {
					$doli_third_party = Request_Util::get( 'thirdparties/' . $entry_id );
					$wp_third_party   = Third_Party::g()->get( array( 'id' => $wp_id ), true );

					Doli_Third_Parties::g()->doli_to_wp( $doli_third_party, $wp_third_party );

					// translators: Erase date for the third party <strong>Eoxia</strong> with the <strong>dolibarr</strong> data.
					$messages[] = sprintf( __( 'Erase data for the third party <strong>%s</strong> with the <strong>dolibarr</strong> data', 'wpshop' ), $wp_third_party->data['title'] );

					$messages = array_merge( $messages, Third_Party::g()->dessociate_contact( $wp_third_party ) );
				}

				if ( 'wp' === $from ) {
					$wp_third_party   = Third_Party::g()->get( array( 'id' => $wp_id ), true );
					$doli_third_party = Request_Util::get( 'thirdparties/' . $entry_id );

					Doli_Third_Parties::g()->wp_to_doli( $wp_third_party, $doli_third_party );
				}

				$wp_object = $wp_third_party;
				break;
			case 'wps-product':
				if ( 'dolibarr' === $from ) {
					$doli_product = Request_Util::get( 'products/' . $entry_id );
					$wp_product   = Product::g()->get( array( 'id' => $wp_id ), true );

					$wp_product = Doli_Products::g()->doli_to_wp( $doli_product, $wp_product );

					Request_Util::post( 'wpshop/object', array(
						'wp_id'   => $wp_id,
						'doli_id' => $entry_id,
					) );
				}

				if ( 'wp' === $from ) {
					$wp_product   = Product::g()->get( array( 'id' => $wp_id ), true );
					$doli_product = Request_Util::get( 'products/' . $entry_id );

					update_post_meta( $wp_id, '_external_id', $entry_id );
					$wp_product->data['external_id'] = $entry_id;
					Doli_Products::g()->wp_to_doli( $wp_product, $doli_product );
				}

				$wp_object = $wp_product;
				break;
			case 'wps-proposal':
				if ( 'dolibarr' === $from ) {
					$doli_proposal = Request_Util::get( 'proposals/' . $entry_id );
					$wp_proposal   = Proposals::g()->get( array( 'id' => $wp_id ), true );

					Doli_Proposals::g()->doli_to_wp( $doli_proposal, $wp_proposal );
				}

				$wp_object = $wp_proposal;
				break;
			case 'wps-order':
				if ( 'dolibarr' === $from ) {
					$doli_order = Request_Util::get( 'orders/' . $entry_id );
					$wp_order   = Doli_Order::g()->get( array( 'id' => $wp_id ), true );

					Doli_Order::g()->doli_to_wp( $doli_order, $wp_order );

					Request_Util::post( 'wpshop/object', array(
						'wp_id'   => $wp_id,
						'doli_id' => $entry_id,
					) );

					// Facture.
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
				}

				$wp_object = $wp_order;
				break;

			case 'wps-doli-invoice':
				if ( 'dolibarr' === $from ) {
					$doli_order = Request_Util::get( 'invoices/' . $entry_id );
					$wp_order   = Doli_Invoice::g()->get( array( 'id' => $wp_id ), true );

					Doli_Invoice::g()->doli_to_wp( $doli_order, $wp_order );

					Request_Util::post( 'wpshop/object', array(
						'wp_id'   => $wp_id,
						'doli_id' => $entry_id,
					) );
				}

				$wp_object = $wp_order;
				break;
			default:
				break;
		}

		$last_sync = current_time( 'mysql' );
		update_post_meta( $wp_id, '_last_sync', $last_sync );

		return array(
			'messages'  => $messages,
			'wp_error'  => $wp_error,
			'last_sync' => $last_sync,
			'wp_object' => $wp_object,
		);
	}
}

Doli_Sync::g();
