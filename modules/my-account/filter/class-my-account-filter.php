<?php
/**
 * Classe filtre de My Account.
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
 * My Account Filter Class.
 */
class My_Account_Filter {

	/**
	 * Init filter
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_filter( 'wps_account_navigation_items', array( $this, 'add_items_menu' ) );
	}

	/**
	 * Ajoutes des éléments dans le menu si Dolibarr est actives.
	 *
	 * @since 2.0.0
	 *
	 * @param array $items Les éléments du menu.
	 *
	 * @return array       Les éléments du menu + les éléments ajouté par cette
	 * fonction.
	 */
	public function add_items_menu( $items ) {
		$new_items = array();

		if ( Settings::g()->use_quotation() ) {
			$new_items['quotations'] = array(
				'link'  => Pages::g()->get_account_link() . 'quotations/',
				'icon'  => 'fas fa-file-signature',
				'title' => __( 'Quotations', 'wpshop' ),
			);
		}

		if ( Settings::g()->dolibarr_is_active() ) {
			$new_items['orders'] = array(
				'link'  => Pages::g()->get_account_link() . 'orders/',
				'icon'  => 'fas fa-shopping-cart',
				'title' => __( 'Orders', 'wpshop' ),
			);

			$new_items['invoices'] = array(
				'link'  => Pages::g()->get_account_link() . 'invoices/',
				'icon'  => 'fas fa-file-invoice-dollar',
				'title' => __( 'Invoices', 'wpshop' ),
			);

			$new_items['download'] = array(
				'link'  => Pages::g()->get_account_link() . 'download/',
				'icon'  => 'fas fa-file-download',
				'title' => __( 'Downloads', 'wpshop' ),
			);

		}

		$items = $new_items + $items;
		return $items;
	}
}

new My_Account_Filter();
