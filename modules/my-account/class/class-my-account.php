<?php
/**
 * Classe principale de My Account.
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
 * My Account Class.
 */
class My_Account extends \eoxia\Singleton_Util {

	/**
	 * Éléments du menu
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $menu = array();

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	protected function construct() {}

	/**
	 * Ajoutes la route orders.
	 *
	 * @since 2.0.0
	 */
	public function init_endpoint() {
		if ( Settings::g()->dolibarr_is_active() ) {
			add_rewrite_endpoint( 'orders', EP_PAGES );
			add_rewrite_endpoint( 'invoices', EP_PAGES );
		}

		add_rewrite_endpoint( 'details', EP_PAGES );
		add_rewrite_endpoint( 'quotations', EP_PAGES );
		add_rewrite_endpoint( 'download', EP_PAGES );

		do_action( 'wps_account_navigation_endpoint' );

		if ( ! get_option( 'plugin_permalinks_flushed' ) ) {
			flush_rewrite_rules(false);
			update_option('plugin_permalinks_flushed', 1);
		}
	}

	/**
	 * Appel la vue pour afficher le formulaire de login dans la page de
	 * paiement.
	 *
	 * @since 2.0.0
	 */
	public function checkout_form_login() {
		if ( ! is_user_logged_in() ) {
			include( Template_Util::get_template_part( 'my-account', 'checkout-login' ) );
		}
	}

	public function display_form_login() {
		global $post;

		$transient = get_option( 'login_error_' . $_COOKIE['PHPSESSID'] );
		update_option( 'login_error_' . $_COOKIE['PHPSESSID'], '', false );

		include( Template_Util::get_template_part( 'my-account', 'form-login' ) );
	}

	/**
	 * Affiches le menu de navigation
	 *
	 * @since 2.0.0
	 *
	 * @param  string $tab Le slug de l'onglet actuel.
	 */
	public function display_navigation( $tab ) {
		$menu_def = array(
			'details'    => array(
				'link'  => Pages::g()->get_account_link() . 'details/',
				'icon'  => 'fas fa-user' ,
				'title' => __( 'Account details', 'wpshop' ),
			),
			'logout'     => array(
				'link'  => wp_logout_url( home_url() ),
				'icon'  => 'fas fa-sign-out-alt',
				'title' => __( 'Logout', 'wpshop' ),
			),
		);

		if ( class_exists( '\user_switching' ) ) {
			$old_user = \user_switching::get_old_user();

			if ( $old_user ) {
				$link = \user_switching::switch_back_url( $old_user );

				$menu_def['switch'] = array(
					'link'  => $link,
					'icon'  => 'fas fa-random',
					'title' => __( 'Switch back', 'wpshop' ),
				);
			}
		}

		$this->menu = apply_filters( 'wps_account_navigation_items', $menu_def );
		include( Template_Util::get_template_part( 'my-account', 'my-account-navigation' ) );
	}

	/**
	 * Affiches les détails de l'utilisateur.
	 *
	 * @since 2.0.0
	 */
	public function display_details() {
		$contact     = Contact::g()->get( array( 'id' => get_current_user_id() ), true );
		$third_party = Third_Party::g()->get( array( 'id' => $contact->data['third_party_id'] ), true );

		$transient = get_transient( 'wps_update_account_details_errors' );
		delete_transient( 'wps_update_account_details_errors' );

		include( Template_Util::get_template_part( 'my-account', 'my-account-details' ) );
	}

	/**
	 * Affiches les commandes liées au tier.
	 *
	 * @since 2.0.0
	 */
	public function display_orders() {
		$contact     = Contact::g()->get( array( 'id' => get_current_user_id() ), true );
		$third_party = Third_Party::g()->get( array( 'id' => $contact->data['third_party_id'] ), true );

		$orders = array();

		if ( ! empty( $third_party->data['id'] ) ) {
			$orders = Doli_Order::g()->get( array( 'post_parent' => $third_party->data['id'] ) );

			if ( ! empty( $orders ) ) {
				foreach ( $orders as &$order ) {
					$order->data['invoice'] = Doli_Invoice::g()->get( array( 'post_parent' => $order->data['id'] ), true );
				}
			}

			unset( $order );
		}

		include( Template_Util::get_template_part( 'my-account', 'my-account-orders' ) );
	}

	/**
	 * Affiches les factures liées au tier.
	 *
	 * @since 2.0.0
	 */
	public function display_invoices() {
		$contact     = Contact::g()->get( array( 'id' => get_current_user_id() ), true );
		$third_party = Third_Party::g()->get( array( 'id' => $contact->data['third_party_id'] ), true );

		$invoices = array();

		if ( ! empty( $third_party->data['id'] ) ) {
			$invoices = Doli_Invoice::g()->get( array(
				'meta_key'   => '_third_party_id',
				'meta_value' => $third_party->data['id'],
			) );
		}

		include( Template_Util::get_template_part( 'my-account', 'my-account-invoices' ) );
	}

	/**
	 * Affiches les téléchargements liées au tier.
	 *
	 * @since 2.0.0
	 */
	public function display_downloads() {
		$contact     = Contact::g()->get( array( 'id' => get_current_user_id() ), true );
		$third_party = Third_Party::g()->get( array( 'id' => $contact->data['third_party_id'] ), true );

		$products_downloadable = array();

		if ( ! empty( $third_party->data['id'] ) ) {
			$products_downloadable = Product_Downloadable::g()->get( array(
				'author' => $contact->data['id'],
			) );
		}

		include( Template_Util::get_template_part( 'my-account', 'my-account-downloads' ) );
	}

	/**
	 * Affiches les devis liés au tiers.
	 *
	 * @since 2.0.0
	 */
	public function display_quotations() {
		$contact     = Contact::g()->get( array( 'id' => get_current_user_id() ), true );
		$third_party = Third_Party::g()->get( array( 'id' => $contact->data['third_party_id'] ), true );

		$proposals = array();

		if ( ! empty( $third_party->data['id'] ) ) {
			$proposals = Proposals::g()->get( array( 'post_parent' => $third_party->data['id'] ) );
		}

		include( Template_Util::get_template_part( 'my-account', 'my-account-proposals' ) );
	}
}

My_Account::g();
