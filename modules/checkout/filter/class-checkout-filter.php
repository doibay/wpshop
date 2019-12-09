<?php
/**
 * Les filtres du tunnel de vente.
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
 * Checkout Filter Class.
 */
class Checkout_Filter {

	/**
	 * Init filter
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'add_order_or_quotation_id' ) );
	}

	/**
	 * Add parameters to the route /{type}/{id}
	 *
	 * @param array $vars Les paramètres de base.
	 *
	 * @return array      Les paramètres de base avec {type} et {id}.
	 */
	public function add_order_or_quotation_id( $vars ) {
		$vars[] = 'id';
		$vars[] = 'type';
		$vars[] = 'object_type';

		return $vars;
	}

}

new Checkout_Filter();
