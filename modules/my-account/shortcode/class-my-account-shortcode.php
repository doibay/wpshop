<?php
/**
 * Gestion des actions des commandes.
 *
 * Ajoutes une page "Orders" dans le menu de WordPress.
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
 * Action of Order module.
 */
class My_Account_Shortcode extends \eoxia\Singleton_Util {

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	protected function construct() {}

	public function init_shortcode() {
		add_shortcode( 'wps_account', array( $this, 'callback_account' ) );
	}

	public function callback_account() {
		if ( ! is_admin() ) {
			if ( ! is_user_logged_in() ) {
				include( Template_Util::get_template_part( 'my-account', 'form-login' ) );
			} else {
				global $wp;

				$tab = 'orders';

				if ( array_key_exists( 'orders', $wp->query_vars ) ) {
					$tab = 'orders';
				}

				if ( array_key_exists( 'proposals', $wp->query_vars ) ) {
					$tab = 'proposals';
				}

				include( Template_Util::get_template_part( 'my-account', 'my-account' ) );
			}
		}
	}
}

My_Account_Shortcode::g();
