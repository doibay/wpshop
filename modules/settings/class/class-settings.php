<?php
/**
 * Les fonctions principales des options
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
 * Settings Class.
 */
class Settings extends \eoxia\Singleton_Util {

	/**
	 * Les options par défauts.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $default_settings;

	/**
	 * TVA
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $tva = array( 0, 2.1, 5.5, 10, 20 );

	/**
	 * Constructeur.
	 *
	 * @since 2.0.0
	 */
	protected function construct() {
		$this->default_settings = array(
			'dolibarr_url'    => '',
			'dolibarr_secret' => '',
			'shop_email'      => '',
			'error'           => '',
			'thumbnail_size'  => array(
				'width'  => 360,
				'height' => 460,
			),
			'use_quotation' => true,
			'notice' => array(
				'error_erp' => true,
				'activate_erp' => true,
			),
		);

		$this->shipping_cost_default_settings = array(
			'from_price_ht'       => null,
			'shipping_product_id' => 0,
		);
	}

	/**
	 * Affiches l'onglet "Général" de la page options.
	 *
	 * @param  string $section La section.
	 *
	 * @since 2.0.0
	 */
	public function display_general( $section = '' ) {
		$dolibarr_option = get_option( 'wps_dolibarr', $this->default_settings );

		\eoxia\View_Util::exec( 'wpshop', 'settings', 'general', array(
			'dolibarr_option' => $dolibarr_option,
		) );
	}

	/**
	 * Affiches l'onglet "Pages" de la page options.
	 *
	 * @param  string $section La section.
	 *
	 * @since 2.0.0
	 */
	public function display_pages( $section = '' ) {
		$pages = get_pages();

		array_unshift( $pages, (object) array(
			'ID'         => 0,
			'post_title' => __( 'No page', 'wpshop' ),
		) );

		$page_ids_options = get_option( 'wps_page_ids', Pages::g()->default_options );

		\eoxia\View_Util::exec( 'wpshop', 'settings', 'pages', array(
			'pages'            => $pages,
			'page_ids_options' => $page_ids_options,
		) );
	}

	/**
	 * Affiches l'onglet "Emails" de la page options.
	 *
	 * @param  string $section La section.
	 *
	 * @since 2.0.0
	 */
	public function display_emails( $section = '' ) {
		if ( ! empty( $section ) ) {
			$email            = Emails::g()->emails[ $section ];
			$path_to_template = Emails::g()->get_path( $email['filename_template'] );
			$content          = file_get_contents( $path_to_template );

			\eoxia\View_Util::exec( 'wpshop', 'settings', 'email-single', array(
				'section'     => $section,
				'email'       => $email,
				'content'     => $content,
				'is_override' => Emails::g()->is_override( $email['filename_template'] ),
			) );
		} else {
			\eoxia\View_Util::exec( 'wpshop', 'settings', 'emails', array(
				'emails' => Emails::g()->emails,
			) );
		}
	}

	/**
	 * Affiches l'onglet "Méthode de paiement" de la page options.
	 *
	 * @param  string $section La section.
	 *
	 * @since 2.0.0
	 */
	public function display_payment_method( $section = '' ) {
		$payment_methods = get_option( 'wps_payment_methods', Payment::g()->default_options );

		if ( ! empty( $section ) ) {
			$payment_data = Payment::g()->get_payment_option( $section );

			\eoxia\View_Util::exec( 'wpshop', 'settings', 'payment-method-single', array(
				'section'      => $section,
				'payment_data' => $payment_data,
			) );
		} else {
			\eoxia\View_Util::exec( 'wpshop', 'settings', 'payment-method', array(
				'payment_methods' => $payment_methods,
			) );
		}
	}

	/**
	 * Affiches l'onglet "Frais de port" de la page options.
	 *
	 * @param  string $section La section.
	 *
	 * @since 2.0.0
	 */
	public function display_shipping_cost( $section = '' ) {
		$shipping_cost_option = get_option( 'wps_shipping_cost', Settings::g()->shipping_cost_default_settings );

		$products = Product::g()->get();

		$no_product = (object) array(
			'data' => array(
				'id'    => 0,
				'title' => __( 'No product', 'wpshop' ),
			),
		);

		array_unshift( $products, $no_product );

		\eoxia\View_Util::exec( 'wpshop', 'settings', 'shipping-cost', array(
			'shipping_cost_option' => $shipping_cost_option,
			'products'             => $products,
		) );
	}

	/**
	 * Vérifie si dolibarr est actif.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean true or false.
	 */
	public function dolibarr_is_active() {
		$dolibarr_option = get_option( 'wps_dolibarr', Settings::g()->default_settings );

		if ( ! empty( $dolibarr_option['dolibarr_url'] ) && ! empty( $dolibarr_option['dolibarr_secret'] ) && empty( $dolibarr_option['error'] ) ) {
			return true;
		}

		return false;
	}

	public function use_quotation() {
		$dolibarr_option = get_option( 'wps_dolibarr', Settings::g()->default_settings );

		return $dolibarr_option['use_quotation'];
	}
}

Settings::g();
