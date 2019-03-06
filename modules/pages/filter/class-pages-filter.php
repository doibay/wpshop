<?php
/**
 * Les fonctions principales des produits.
 *
 * Le controlleur du modèle Product_Model.
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
* Handle product
*/
class Pages_Filter extends \eoxia\Singleton_Util {

	protected function construct() {

		add_action( 'template_redirect', array( $this, 'check_page' ) );
		add_filter( 'display_post_states', array( $this, 'add_states_post' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'do_shortcode_page' ), 99, 1 );
	}

	public function check_page() {
		$page_ids_options = get_option( 'wps_page_ids', Pages_Class::g()->default_options );

		if ( $page_ids_options['checkout_id'] && is_page( $page_ids_options['checkout_id'] ) ) {
			$cart_contents = Class_Cart_Session::g()->cart_contents;

			if ( empty( $cart_contents ) ) {
				wp_redirect( Pages_Class::g()->get_cart_link() );
				exit;
			}
		}
	}

	public function add_states_post( $post_states, $post ) {
		$page_ids_options = get_option( 'wps_page_ids', Pages_Class::g()->default_options );

		$key = array_search($post->ID, $page_ids_options);

		if ( FALSE !== $key ) {
			$post_states[] = $key;
		}

		return $post_states;
	}

	public function do_shortcode_page( $content ) {

		if ( ! is_admin() ) {
			$page_ids_options = get_option( 'wps_page_ids', Pages_Class::g()->default_options );

			$key = array_search($GLOBALS['post']->ID, $page_ids_options);

			$shortcode = '';
			$params    = array();


			if ( FALSE !== $key ) {
				switch ($key)
				{
					case 'checkout_id':
						$params['step'] = isset( $_GET['step'] ) ? $_GET['step'] : 1;
						$shortcode      = 'checkout';
						break;
					case 'valid_checkout_id':
						$shortcode          = 'valid_checkout';
						$params['order_id'] = isset( $_GET['order_id'] ) ? $_GET['order_id'] : 0;
						break;
					case 'valid_proposal_id':
						$shortcode             = 'valid_proposal';
						$params['proposal_id'] = isset( $_GET['proposal_id'] ) ? $_GET['proposal_id'] : 0;
						break;
					case 'cart_id':
						$shortcode = 'cart';
						break;
					case 'my_account_id':
						$shortcode = 'account';
						break;
					default:
						break;
				}
			}

			$tmp_content = $content;

			if ( ! empty( $shortcode ) ) {
				$shortcode_attr = '';
				if ( ! empty( $params ) ) {
					foreach ( $params as $key => $value ) {
						$shortcode_attr = $key . '=' . $value . ' ';
					}
				}
				$content  = do_shortcode( '[wps_' . $shortcode . ' ' . $shortcode_attr . ']' );
				$content .= $tmp_content;
			}
		}

		return $content;
	}
}

Pages_Filter::g();
