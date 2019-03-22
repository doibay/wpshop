<?php
/**
 * Les actions relatives aux commandes avec Dolibarr.
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
 * Doli Order Action Class.
 */
class Doli_Order_Action {

	/**
	 * Initialise les actions liées aux proposals.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'callback_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'callback_admin_menu' ) );

		add_action( 'wps_checkout_create_order', array( $this, 'create_order' ), 10, 1 );
		add_action( 'wps_payment_complete', array( $this, 'set_to_billed' ), 30, 1 );
		add_action( 'wps_payment_failed', array( $this, 'set_to_failed' ), 30, 1 );
	}

	/**
	 * Ajoutes des status dans la commande.
	 *
	 * @since 2.0.0
	 */
	public function callback_admin_init() {
		remove_post_type_support( 'wps-order', 'title' );
		remove_post_type_support( 'wps-order', 'editor' );
		remove_post_type_support( 'wps-order', 'excerpt' );

		register_post_status( 'wps-delivered', array(
			'label'                     => _x( 'Delivered', 'Order status', 'wpshop' ),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of orders */
			'label_count'               => _n_noop( 'Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'wpshop' ),
		) );

		register_post_status( 'wps-canceled', array(
			'label'                     => _x( 'Canceled', 'Order status', 'wpshop' ),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of orders */
			'label_count'               => _n_noop( 'Canceled <span class="count">(%s)</span>', 'Canceled <span class="count">(%s)</span>', 'wpshop' ),
		) );
	}

	/**
	 * Initialise la page "Commande".
	 *
	 * @since 2.0.0
	 */
	public function callback_admin_menu() {
		add_submenu_page( 'wps-order', __( 'Orders', 'wpshop' ), __( 'Orders', 'wpshop' ), 'manage_options', 'wps-order', array( $this, 'callback_add_menu_page' ) );
	}

	/**
	 * Affichage de la vue du menu
	 *
	 * @since 2.0.0
	 */
	public function callback_add_menu_page() {
		if ( isset( $_GET['id'] ) ) {
			$order        = Doli_Order::g()->get( array( 'id' => $_GET['id'] ), true );
			$args_metabox = array(
				'order' => $order,
				'id'    => $_GET['id'],
			);

			/* translators: Order details CO00010 */
			$box_order_detail_title = sprintf( __( 'Order details %s', 'wpshop' ), $order->data['title'] );

			add_meta_box( 'wps-order-customer', $box_order_detail_title, array( $this, 'callback_meta_box' ), 'wps-order', 'normal', 'default', $args_metabox );
			add_meta_box( 'wps-order-products', __( 'Products', 'wpshop' ), array( $this, 'callback_products' ), 'wps-order', 'normal', 'default', $args_metabox );

			\eoxia\View_Util::exec( 'wpshop', 'doli-order', 'single', array( 'order' => $order ) );
		} else {
			$args = array(
				'post_type'      => 'wps-order',
				'posts_per_page' => -1,
			);

			$count = count( get_posts( $args ) );

			\eoxia\View_Util::exec( 'wpshop', 'doli-order', 'main', array(
				'count' => $count,
			) );
		}
	}

	/**
	 * La metabox des détails de la commande
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_Post $post          Les données du post.
	 * @param  array   $callback_args Tableau contenu les données de la commande.
	 */
	public function callback_meta_box( $post, $callback_args ) {
		$order        = $callback_args['args']['order'];
		$invoice      = Doli_Invoice::g()->get( array( 'post_parent' => $order->data['id'] ), true );
		$third_party  = Third_Party::g()->get( array( 'id' => $order->data['parent_id'] ), true );
		$link_invoice = '';

		if ( ! empty( $invoice ) ) {
			$invoice->data['payments'] = array();
			$invoice->data['payments'] = Doli_Payment::g()->get( array( 'post_parent' => $invoice->data['id'] ) );
			$link_invoice              = admin_url( 'admin-post.php?action=wps_download_invoice_wpnonce=' . wp_create_nonce( 'download_invoice' ) . '&order_id=' . $order->data['id'] );
		}

		\eoxia\View_Util::exec( 'wpshop', 'doli-order', 'metabox-order-details', array(
			'order'        => $order,
			'third_party'  => $third_party,
			'invoice'      => $invoice,
			'link_invoice' => $link_invoice,
		) );
	}

	/**
	 * Box affichant les produits de la commande
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_Post $post          Les données du post.
	 * @param  array   $callback_args Tableau contenu les données de la commande.
	 */
	public function callback_products( $post, $callback_args ) {
		$order = $callback_args['args']['order'];

		$tva_lines = array();

		if ( ! empty( $order->data['lines'] ) ) {
			foreach ( $order->data['lines'] as $line ) {
				if ( empty( $tva_lines[ $line['tva_tx'] ] ) ) {
					$tva_lines[ $line['tva_tx'] ] = 0;
				}

				$tva_lines[ $line['tva_tx'] ] += $line['total_tva'];
			}
		}

		\eoxia\View_Util::exec( 'wpshop', 'doli-order', 'metabox-orders', array(
			'order'     => $order,
			'tva_lines' => $tva_lines,
		) );
	}

	/**
	 * Box affichant les actions de la commande.
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_Post $post          Les données du post.
	 * @param  array   $callback_args Tableau contenu les données de la commande.
	 */
	public function callback_order_action( $post, $callback_args ) {
		$order = $callback_args['args']['order'];

		\eoxia\View_Util::exec( 'wpshop', 'doli-order', 'metabox-action', array(
			'order' => $order,
		) );
	}

	/**
	 * Création d'une commande lors du tunnel de vente.
	 *
	 * @since 2.0.0
	 *
	 * @param  stdClass $wp_proposal Les données du devis.
	 * @return Order_Model           Les données de la commande WP.
	 */
	public function create_order( $wp_proposal ) {
		$third_party      = Third_Party::g()->get( array( 'id' => $wp_proposal->data['parent_id'] ), true );
		$doli_proposal_id = get_post_meta( $wp_proposal->data['id'], '_external_id', true );

		$doli_order = Request_Util::post( 'orders/createfromproposal/' . $doli_proposal_id );
		$doli_order = Request_Util::post( 'orders/' . $doli_order->id . '/validate' );

		Emails::g()->send_mail( null, 'wps_email_new_order', array(
			'order'       => $doli_order,
			'third_party' => $third_party->data,
		) );

		$current_user = wp_get_current_user();

		Emails::g()->send_mail( $current_user->user_email, 'wps_email_customer_processing_order', array(
			'order'       => $doli_order,
			'third_party' => $third_party->data,
		) );

		$wp_order = Doli_Order::g()->get( array( 'schema' => true ), true );
		$wp_order = Doli_Order::g()->doli_to_wp( $doli_order, $wp_order );

		$wp_order->data['author_id'] = $current_user->ID;

		return Doli_Order::g()->update( $wp_order->data );
	}

	/**
	 * Passes la commande à payé.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Les données IPN de PayPal.
	 */
	public function set_to_billed( $data ) {
		$wp_order = Doli_Order::g()->get( array( 'id' => (int) $data['custom'] ), true );

		$doli_order = Request_Util::post( 'orders/' . $wp_order->data['external_id'] . '/setinvoiced' );

		Doli_Order::g()->doli_to_wp( $doli_order, $wp_order );
	}

	/**
	 * Passes la commande à payment échoué.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Les données IPN de PayPal.
	 */
	public function set_to_failed( $data ) {
		$wp_order = Doli_Order::g()->get( array( 'id' => (int) $data['custom'] ), true );

		$wp_order->data['payment_failed'] = true;
		Doli_Order::g()->update( $wp_order->data );
	}
}

new Doli_Order_Action();
