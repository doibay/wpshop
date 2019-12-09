<?php
/**
 * Les fonctions principales du tunnel de vente.
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
 * Checkout Class.
 */
class Checkout extends \eoxia\Singleton_Util {

	/**
	 * Constructeur pour la classe Checkout. Charge les options et les actions.
	 *
	 * @since 2.0.0
	 */
	protected function construct() {}

	/**
	 * Récupères les données postées
	 *
	 * @since 2.0.0
	 *
	 * @return array Les données postées filtrés et sécurisés.
	 */
	public function get_posted_data() {
		$data = array(
			'contact'     => ! empty( $_POST['contact'] ) ? (array) $_POST['contact'] : array(),
			'third_party' => ! empty( $_POST['third_party'] ) ? (array) $_POST['third_party'] : array(),
		);

		$data['contact']['firstname']      = ! empty( $_POST['contact']['firstname'] ) ? sanitize_text_field( $_POST['contact']['firstname'] ) : '';
		$data['contact']['lastname']       = ! empty( $_POST['contact']['lastname'] ) ? sanitize_text_field( $_POST['contact']['lastname'] ) : '';
		$data['contact']['phone']          = ! empty( $_POST['contact']['phone'] ) ? sanitize_text_field( $_POST['contact']['phone'] ) : '';
		$data['contact']['email']          = ! empty( $_POST['contact']['email'] ) ? sanitize_email( $_POST['contact']['email'] ) : '';
		$data['contact']['password']       = ! empty( $_POST['contact']['password'] ) ? (string) ( $_POST['contact']['password'] ) : '';
		$data['third_party']['country_id'] = ! empty( $_POST['third_party']['country_id'] ) ? (int) ( $_POST['third_party']['country_id'] ) : '';
		$data['third_party']['address']    = ! empty( $_POST['third_party']['address'] ) ? sanitize_text_field( $_POST['third_party']['address'] ) : '';
		$data['third_party']['zip']        = ! empty( $_POST['third_party']['zip'] ) ? sanitize_text_field( $_POST['third_party']['zip'] ) : '';
		$data['third_party']['town']       = ! empty( $_POST['third_party']['town'] ) ? sanitize_text_field( $_POST['third_party']['town'] ) : '';
		$data['terms']                     = ( ! empty( $_POST['terms'] ) && 'true' === $_POST['terms'] ) ? true : false;

		return apply_filters( 'wps_checkout_posted_data', $data );
	}

	/**
	 * Définition du formulaire du tunnel de vente
	 *
	 * @since 2.0.0
	 *
	 * @return array Tableau contenant la définition des champs.
	 */
	private function get_checkout_fields() {
		return array(
			'terms'       => array(
				'label'    => __( 'Terms', 'wpshop' ),
				'required' => true,
			),
			'contact'     => array(
				'firstname' => array(
					'label'    => __( 'First name', 'wpshop' ),
					'required' => false,
				),
				'lastname'  => array(
					'label'    => __( 'Last name', 'wpshop' ),
					'required' => false,
				),
				'phone'     => array(
					'label'    => __( 'Phone', 'wpshop' ),
					'required' => false,
				),
				'email'     => array(
					'label'    => __( 'Email address', 'wpshop' ),
					'required' => true,
				),
				'password'  => array(
					'label'    => __( 'Password', 'wpshop' ),
					'required' => false,
				),
			),
			'third_party' => array(
				'country_id' => array(
					'label'    => __( 'Country', 'wpshop' ),
					'required' => false,
				),
				'address'    => array(
					'label'    => __( 'Street Address', 'wpshop' ),
					'required' => false,
				),
				'zip'        => array(
					'label'    => __( 'Postcode / Zip', 'wpshop' ),
					'required' => false,
				),
				'town'       => array(
					'label'    => __( 'Town / City', 'wpshop' ),
					'required' => false,
				),
			),
		);
	}

	/**
	 * Vérifie les données reçu par le formulaire du tunnel de vente.
	 *
	 * @since 2.0.0
	 *
	 * @param  array    $data   Les données reçu du formulaire.
	 * @param  WP_Error $errors Gestion des erreurs du formulaire.
	 */
	protected function validate_posted_data( &$data, &$errors ) {
		foreach ( $this->get_checkout_fields() as $fieldset_key => $fieldset ) {
			if ( 'terms' === $fieldset_key ) {
				if ( ! $data[ $fieldset_key ] ) {
					$errors->add( 'terms-field', apply_filters( 'wps_checkout_terms', __( 'You need to accept <strong>the general conditions of sale</strong> and <strong>the privacy policy</strong>', 'wpshop' ) ) );

					$error_field = array(
						'required'    => true,
						'input_class' => 'terms',
					);

					$errors->add_data( $error_field, 'input_terms' );
				}
			} else {
				foreach ( $fieldset as $field_key => $field ) {
					if ( $field['required'] && ( '' == $data[ $fieldset_key ][ $field_key ] || '0' == $data[ $fieldset_key ][ $field_key ] ) ) {
						/* translators: Lastname is a required field. */
						$errors->add( 'required-field', apply_filters( 'wps_checkout_required_field_notice', sprintf( __( '%s is a required field.', 'wpshop' ), '<strong>' . esc_html( $field['label'] ) . '</strong>' ), $field['label'] ) );

						$error_field = array(
							'required'    => true,
							'input_class' => $fieldset_key . '-' . $field_key,
						);

						$errors->add_data( $error_field, 'input_' . $fieldset_key . '_' . $field_key );
					}

					if ( ! is_user_logged_in() && 'email' === $field_key && false !== email_exists( $data['contact']['email'] ) ) {
						/* translators: mail@domain.ext is already used. */
						$errors->add( 'email-exists', apply_filters( 'wps_checkout_email_exists_notice', sprintf( __( '%s is already used.', 'wpshop' ), '<strong>' . esc_html( $field['label'] ) . '</strong>' ), $field['label'] ) );
						$error_field = array(
							'email_exists' => true,
							'input_class'  => $fieldset_key . '-' . $field_key,
						);

						$errors->add_data( $error_field, 'input_' . $fieldset_key . '_' . $field_key );
					}
				}
			}
		}
	}

	/**
	 * Appel la méthode pour valider le formulaire.
	 *
	 * @since 2.0.0
	 *
	 * @param  array    $data   Les données reçu du formulaire.
	 * @param  WP_Error $errors Gestion des erreurs du formulaire.
	 */
	public function validate_checkout( &$data, &$errors ) {
		$this->validate_posted_data( $data, $errors );
	}

	/**
	 * Procèdes au paiement
	 *
	 * @since 2.0.0
	 *
	 * @param Order_Model $order Les données de la commande.
	 */
	public function process_order_payment( $order ) {
		$type = ! empty( $_POST['type_payment'] ) ? $_POST['type_payment'] : '';

		switch ( $type ) {
			case 'cheque':
				update_post_meta( $order->data['id'], 'payment_method', 'cheque' );
				// translators: Order: Add cheque payment to the order 0000001.
				\eoxia\LOG_Util::log( sprintf( 'Order: Add cheque payment to the order %s', $order->data['id'] ), 'wpshop2' );

				Cart_Session::g()->destroy();
				wp_send_json_success( array(
					'namespace'        => 'wpshopFrontend',
					'module'           => 'checkout',
					'callback_success' => 'redirect',
					'url'              => Pages::g()->get_checkout_link() . '/received/order/' . $order->data['id'] . '/',
				) );
				break;
			case 'payment_in_shop':
				update_post_meta( $order->data['id'], 'payment_method', 'payment_in_shop' );

				// translators: Order: Add payment in shop to the order 000001.
				\eoxia\LOG_Util::log( sprintf( 'Order: Add payment in shop to the order %s', $order->data['id'] ), 'wpshop2' );

				Cart_Session::g()->destroy();
				wp_send_json_success( array(
					'namespace'        => 'wpshopFrontend',
					'module'           => 'checkout',
					'callback_success' => 'redirect',
					'url'              => Pages::g()->get_checkout_link() . '/received/order/' . $order->data['id'] . '/',
				) );
				break;
			case 'paypal':
				$order->data['payment_method']        = 'paypal';
				$order->data['traitment_in_progress'] = true;

				$order = Doli_Order::g()->update( $order->data );

				// translators: Order: Add Stripe payment to the order 000001.
				\eoxia\LOG_Util::log( sprintf( 'Order: Add Stripe payment to the order %s', $order->data['id'] ), 'wpshop2' );

				$result = Paypal::g()->process_payment( $order );
				Cart_Session::g()->destroy();
				if ( ! empty( $result['url'] ) ) {
					wp_send_json_success( array(
						'namespace'        => 'wpshopFrontend',
						'module'           => 'checkout',
						'callback_success' => 'redirectToPayment',
						'url'              => $result['url'],
					) );
				}
				break;
			case 'stripe':
				$order->data['payment_method']        = 'stripe';
				$order->data['traitment_in_progress'] = true;

				// translators: Order: Add Stripe payment to the order 000001.
				\eoxia\LOG_Util::log( sprintf( 'Order: Add Stripe payment to the order %s', $order->data['id'] ), 'wpshop2' );

				$result = Stripe::g()->process_payment( $order );
				Cart_Session::g()->destroy();

				if ( ! empty( $result['id'] ) ) {
					wp_send_json_success( array(
						'namespace'        => 'wpshopFrontend',
						'module'           => 'stripe',
						'callback_success' => 'redirectToPayment',
						'id'               => $result['id'],
					) );
				}
				break;
		}

	}

	public function reorder( $id ) {
		Cart_Session::g()->destroy();

		$shipping_cost_option     = get_option( 'wps_shipping_cost', Settings::g()->shipping_cost_default_settings );
		$shippint_cost_product_id = ! empty( $shipping_cost_option['shipping_product_id'] ) ? $shipping_cost_option['shipping_product_id'] : 0;

		$order = Doli_Order::g()->get( array( 'id' => $id ), true );

		if ( ! empty( $order->data['lines'] ) ) {
			foreach ( $order->data['lines'] as $element ) {
				$wp_product = Product::g()->get( array(
					'meta_key'   => '_external_id',
					'meta_value' => (int) $element['fk_product'],
				), true );

				if ( ! empty( $wp_product ) && $wp_product->data['id'] !== $shippint_cost_product_id ) {
					for ( $i = 0; $i < $element['qty']; ++$i ) {
						Cart::g()->add_to_cart( $wp_product );
					}
				}
			}
		}
	}

	public function do_pay( $id ) {
		Cart_Session::g()->destroy();

		$shipping_cost_option     = get_option( 'wps_shipping_cost', Settings::g()->shipping_cost_default_settings );
		$shippint_cost_product_id = ! empty( $shipping_cost_option['shipping_product_id'] ) ? $shipping_cost_option['shipping_product_id'] : 0;

		$order = Doli_Order::g()->get( array( 'id' => $id ), true );

		if ( ! empty( $order->data['lines'] ) ) {
			foreach ( $order->data['lines'] as $element ) {
				$wp_product = Product::g()->get( array(
					'meta_key'   => '_external_id',
					'meta_value' => (int) $element['fk_product'],
				), true );

				if ( ! empty( $wp_product ) && $wp_product->data['id'] !== $shippint_cost_product_id ) {
					for ( $i = 0; $i < $element['qty']; ++$i ) {
						Cart::g()->add_to_cart( $wp_product );
					}
				}
			}
		}

		Cart_Session::g()->add_external_data( 'order_id', $order->data['id'] );
		Cart_Session::g()->update_session();
	}
}

Checkout::g();
