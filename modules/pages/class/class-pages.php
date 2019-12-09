<?php
/**
 * Les fonctions principales des pages.
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
 * Pages Class.
 */
class Pages extends \eoxia\Singleton_Util {
	/**
	 * Tableau contenant toutes les pages personnalisables par défaut.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $default_options;

	/**
	 * Les titres des pages lisible.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $page_state_titles;

	/**
	 * Tableau contenant toutes les pages personnalisables dans la base de
	 * donnée.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $page_ids;

	/**
	 * Constructeur.
	 *
	 * @since 2.0.0
	 */
	protected function construct() {
		$this->default_options = array(
			'shop_id'                    => 0,
			'cart_id'                    => 0,
			'checkout_id'                => 0,
			'my_account_id'              => 0,
			'general_conditions_of_sale' => 0,
		);

		$this->page_state_titles = array(
			'shop_id'                    => __( 'Shop', 'wpshop' ),
			'cart_id'                    => __( 'Cart', 'wpshop' ),
			'checkout_id'                => __( 'Checkout', 'wpshop' ),
			'my_account_id'              => __( 'My account', 'wpshop' ),
			'general_conditions_of_sale' => __( 'General conditions of sale', 'wpshop' ),
		);

		$this->page_ids = get_option( 'wps_page_ids', $this->default_options );
	}

	/**
	 * Créer et associe les pages par défaut nécessaire pour le fonctionnement
	 * de WPshop.
	 *
	 * @since 2.0.0
	 */
	public function create_default_page() {
		load_plugin_textdomain( 'wpshop', false, PLUGIN_WPSHOP_DIR . '/core/asset/language/' );

		if ( ! empty( $this->page_state_titles ) ) {
			foreach ( $this->page_state_titles as $key => $page_title ) {
				$page_id = wp_insert_post( array(
					'post_title'  => $page_title,
					'post_type'   => 'page',
					'post_status' => 'publish',
				) );

				if ( ! empty( $page_id ) ) {
					$this->page_ids[ $key ] = $page_id;

					\eoxia\LOG_Util::log( sprintf( 'Create the page %s when activate plugin success', $page_title ), 'wpshop' );
				} else {
					\eoxia\LOG_Util::log( sprintf( 'Error for create the page %s when activate plugin', $page_title ), 'wpshop' );
				}
			}

			update_option( 'wps_page_ids', $this->page_ids );
		}
	}

	/**
	 * Récupères le slug de la page shop.
	 *
	 * @since 2.0.0
	 *
	 * @return string Le slug de la page shop.
	 */
	public function get_slug_shop_page() {
		$page = get_page( $this->page_ids['shop_id'] );

		if ( ! $page ) {
			return false;
		}

		return $page->post_name;
	}

	/**
	 * Récupères le lien vers la page mon compte.
	 *
	 * @since 2.0.0
	 *
	 * @return string Le lien vers la page mon compte.
	 */
	public function get_account_link() {
		return get_permalink( $this->page_ids['my_account_id'] );
	}

	/**
	 * Récupères le lien vers la page "Mon compte".
	 *
	 * @since 2.0.0
	 *
	 * @return string Le lien vers la page "Mon compte".
	 */
	public function get_cart_link() {
		return get_permalink( $this->page_ids['cart_id'] );
	}

	/**
	 * Récupères le lien vers la page "Paiement".
	 *
	 * @since 2.0.0
	 *
	 * @return string Le lien vers la page "Paiement".
	 */
	public function get_checkout_link() {
		return get_permalink( $this->page_ids['checkout_id'] );
	}

	/**
	 * Récupères le lien vers la page "Condition générale de vente".
	 *
	 * @since 2.0.0
	 *
	 * @return string Le lien vers la page "Condition général de vente".
	 */
	public function get_general_conditions_of_sale_link() {
		return get_permalink( $this->page_ids['general_conditions_of_sale'] );
	}

	/**
	 * Est-ce la page "checkout" ?
	 *
	 * @since 2.0.0
	 *
	 * @return boolean True si oui, sinon false.
	 */
	public function is_checkout_page() {
		$this->page_ids = get_option( 'wps_page_ids', $this->default_options );

		return ( get_the_ID() === $this->page_ids['checkout_id'] ) ? true : false;
	}
}

Pages::g();
