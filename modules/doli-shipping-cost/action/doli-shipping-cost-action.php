<?php
/**
 * Gestion des actions des frais de port.
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
 * Doli Shipping Cost Action.
 */
class Doli_Shipping_Cost_Action {

	/**
	 * Constructeur.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'wps_after_calculate_totals', array( $this, 'add_shipping_cost' ), 10, 0 );
	}

	public function add_shipping_cost() {
		$shipping_cost_option = get_option( 'wps_shipping_cost', Settings::g()->shipping_cost_default_settings );

		if ( 0 === $shipping_cost_option['shipping_product_id'] ) {
			return;
		}

		if ( (float) Cart_Session::g()->total_price_no_shipping < (float) $shipping_cost_option['from_price_ht'] &&
			! Cart_Session::g()->has_product( $shipping_cost_option['shipping_product_id'] ) && count( Cart_Session::g()->cart_contents ) > 0 ) {
			$product = Product::g()->get( array( 'id' => $shipping_cost_option['shipping_product_id'] ), true );
			Cart::g()->add_to_cart( $product );
		}

		if ( (float) Cart_Session::g()->total_price_no_shipping >= (float) $shipping_cost_option['from_price_ht'] ||
			count( Cart_Session::g()->cart_contents ) == 1 && Cart_Session::g()->has_product( $shipping_cost_option['shipping_product_id'] ) ) {
			Cart_Session::g()->remove_product( $shipping_cost_option['shipping_product_id'] );
		}

	}
}

new Doli_Shipping_Cost_Action();
