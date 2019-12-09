<?php
/**
 * Gestion des actions des tiers.
 *
 * Ajoutes une page "Tiers" dans le menu de WordPress.
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
 * Third Party Action Class.
 */
class Third_Party_Action {

	/**
	 * Définition des metabox sur la page.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $metaboxes = null;

	/**
	 * Constructeur.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'callback_admin_menu' ), 11 );

		$this->metaboxes = apply_filters( 'wps_third_party_metaboxes', array(
			'wps-third-party-billing'  => array(
				'callback' => 'metabox_billing_address',
			),
			'wps-third-party-contacts' => array(
				'callback' => 'metabox_contacts',
			),
			'wps-third-party-propal'   => array(
				'callback' => 'metabox_proposals',
			),
		) );
	}

	/**
	 * Initialise la page "Third Parties".
	 *
	 * @since 2.0.0
	 */
	public function callback_admin_menu() {
		$hook = add_submenu_page(
			'wpshop',
			__( 'Third Parties', 'wpshop' ),
			__( 'Third Parties', 'wpshop' ),
			'manage_options',
			'wps-third-party',
			array( $this, 'callback_add_menu_page' )
		);

		if ( ! isset( $_GET['id'] ) ) {
			add_action( 'load-' . $hook, array( $this, 'callback_add_screen_option' ) );
		}
	}

	/**
	 * Appel la vue "main" du module "Third Party".
	 *
	 * @since 2.0.0
	 */
	public function callback_add_menu_page() {
		if ( isset( $_GET['id'] ) ) {
			$id = ! empty( $_GET['id'] ) ? (int) $_GET['id'] : 0;
			$third_party  = Third_Party::g()->get( array( 'id' => $id ), true );
			$args_metabox = array(
				'third_party' => $third_party,
				'id'          => $id,
			);

			if ( ! empty( $this->metaboxes ) ) {
				foreach ( $this->metaboxes as $key => $metabox ) {
					add_action( 'wps_third_party', array( $this, $metabox['callback'] ), 10, 1 );
				}
			}

			\eoxia\View_Util::exec( 'wpshop', 'third-parties', 'single', array( 'third_party' => $third_party ) );
		} else {
			$per_page = get_user_meta( get_current_user_id(), Third_Party::g()->option_per_page, true );

			if ( empty( $per_page ) || 1 > $per_page ) {
				$per_page = Third_Party::g()->limit;
			}

			$s     = ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
			$count = Third_Party::g()->search( $s, array(), true );

			$number_page  = ceil( $count / $per_page );
			$current_page = isset( $_GET['current_page'] ) ? (int) $_GET['current_page'] : 1;

			$base_url = admin_url( 'admin.php?page=wps-third-party' );

			$begin_url = $base_url . '&current_page=1';
			$end_url   = $base_url . '&current_page=' . $number_page;

			$prev_url = $base_url . '&current_page=' . ( $current_page - 1 );
			$next_url = $base_url . '&current_page=' . ( $current_page + 1 );

			if ( ! empty( $s ) ) {
				$begin_url .= '&s=' . $s;
				$end_url   .= '&s=' . $s;
				$prev_url  .= '&s=' . $s;
				$next_url  .= '&s=' . $s;
			}

			\eoxia\View_Util::exec( 'wpshop', 'third-parties', 'main', array(
				'number_page'  => $number_page,
				'current_page' => $current_page,
				'count'        => $count,
				'begin_url'    => $begin_url,
				'end_url'      => $end_url,
				'prev_url'     => $prev_url,
				'next_url'     => $next_url,
				's'            => $s,
			) );
		}
	}

	/**
	 * Ajoutes le menu "Options de l'écran" pour les tiers.
	 *
	 * @since 2.0.0.
	 */
	public function callback_add_screen_option() {
		add_screen_option(
			'per_page',
			array(
				'label'   => _x( 'Third parties', 'Third party per page', 'wpshop' ),
				'default' => Third_Party::g()->limit,
				'option'  => Third_Party::g()->option_per_page,
			)
		);
	}

	/**
	 * Appel la vue de la metabox des adresses.
	 *
	 * @param Third_Party $third_party Les données du tiers.
	 *
	 * @since 2.0.0
	 */
	public function metabox_billing_address( $third_party ) {
		\eoxia\View_Util::exec( 'wpshop', 'third-parties', 'metaboxes/metabox-billing-address', array(
			'third_party' => $third_party,
		) );
	}

	/**
	 * Appel la vue de la metabox des contacts.
	 *
	 * @param Third_Party $third_party Les données du tiers.
	 *
	 * @since 2.0.0
	 */
	public function metabox_contacts( $third_party ) {
		$contacts = array();

		if ( ! empty( $third_party->data['contact_ids'] ) ) {
			$contacts = Contact::g()->get( array( 'include' => $third_party->data['contact_ids'] ) );
		}
		\eoxia\View_Util::exec( 'wpshop', 'third-parties', 'metaboxes/metabox-contacts', array(
			'third_party' => $third_party,
			'contacts'    => $contacts,
		) );
	}

	/**
	 * Appel la vue de la metabox des devis.
	 *
	 * @param Third_Party $third_party Les données du tiers.
	 *
	 * @since 2.0.0
	 */
	public function metabox_proposals( $third_party ) {
		$dolibarr_option = get_option( 'wps_dolibarr', Settings::g()->default_settings );

		$proposals = Proposals::g()->get( array( 'post_parent' => $third_party->data['id'] ) );

		\eoxia\View_Util::exec( 'wpshop', 'third-parties', 'metaboxes/metabox-proposals', array(
			'doli_url'  => $dolibarr_option['dolibarr_url'],
			'proposals' => $proposals,
		) );
	}

	/**
	 * Appel la vue de la metabox des commandes.
	 *
	 * @param Third_Party $third_party Les données du tiers.
	 *
	 * @since 2.0.0
	 */
	public function metabox_orders( $third_party ) {
		$orders = array();

		if ( Settings::g()->dolibarr_is_active() ) {
			$orders = Doli_Order::g()->get( array( 'post_parent' => $third_party->data['id'] ) );

			if ( ! empty( $orders ) ) {
				foreach ( $orders as &$order ) {
					$order->data['invoice'] = Doli_Invoice::g()->get( array( 'post_parent' => $order->data['id'] ), true );
				}
			}
		}

		\eoxia\View_Util::exec( 'wpshop', 'third-parties', 'metaboxes/metabox-orders', array(
			'orders' => $orders,
		) );
	}

	/**
	 * Appel la vue de la metabox des factures.
	 *
	 * @param Third_Party $third_party Les données du tiers.
	 *
	 * @since 2.0.0
	 */
	public function metabox_invoices( $third_party ) {
		$dolibarr_option = get_option( 'wps_dolibarr', Settings::g()->default_settings );

		$invoices = array();

		if ( Settings::g()->dolibarr_is_active() ) {
			$invoices = Doli_Invoice::g()->get( array(
				'meta_key'   => '_third_party_id',
				'meta_value' => $third_party->data['id'],
			) );

			if ( ! empty( $invoices ) ) {
				foreach ( $invoices as &$invoice ) {
					$invoice->data['order'] = Doli_Order::g()->get( array( 'id' => $invoice->data['parent_id'] ), true );
				}
			}
		}

		\eoxia\View_Util::exec( 'wpshop', 'third-parties', 'metaboxes/metabox-invoices', array(
			'doli_url' => $dolibarr_option['dolibarr_url'],
			'invoices' => $invoices,
		) );
	}

	/**
	 * Ajoutes la metabox "Tâches".
	 *
	 * @param Third_Party $third_party Les données du tiers.
	 *
	 * @since 2.0.0
	 */
	public function metabox_tasks( $third_party ) {
		$post = get_post( $third_party->data['id'] );

		\eoxia\View_Util::exec( 'wpshop', 'third-parties', 'metaboxes/metabox-task', array(
			'post' => $post,
		) );
	}

	/**
	 * Ajoutes la metabox "Indicateur".
	 *
	 * @param Third_Party $third_party Les données du tiers.
	 *
	 * @since 2.0.0
	 */
	public function metabox_indicator( $third_party ) {
		$post = get_post( $third_party->data['id'] );

		\eoxia\View_Util::exec( 'wpshop', 'third-parties', 'metaboxes/metabox-indicator', array(
			'post' => $post,
		) );
	}

	/**
	 * Ajoutes la metabox "Activité".
	 *
	 * @param Third_Party $third_party Les données du tiers.
	 *
	 * @since 2.0.0
	 */
	public function metabox_activity( $third_party ) {
		$post = get_post( $third_party->data['id'] );

		\eoxia\View_Util::exec( 'wpshop', 'third-parties', 'metaboxes/metabox-activity', array(
			'post' => $post,
		) );
	}
}

new Third_Party_Action();
