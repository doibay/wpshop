<?php
/**
 * Les fonctions principales.
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
 * Cart Session Class.
 */
class Core extends \eoxia\Singleton_Util {

	/**
	 * Le constructeur.
	 *
	 * @since 2.0.0
	 */
	protected function construct() {}

	/**
	 * Méthode appelée à l'activation du plugin.
	 *
	 * @since 2.0.0
	 */
	public function plugin_activated() {
		$db_version = get_option( 'wpshop2_default_page', false );

		if ( ! $db_version ) {
			Pages::g()->create_default_page();

			update_option( 'wpshop2_default_page', true );
		}
	}
}

Core::g();