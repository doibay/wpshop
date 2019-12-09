<?php
/**
 * Les fonctions principales des contacts avec dolibarr.
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
 * Doli Contact Class.
 */
class Doli_Contact extends \eoxia\Singleton_Util {

	/**
	 * Constructeur
	 *
	 * @since 2.0.0
	 */
	protected function construct() {}

	/**
	 * Synchronise les contacts de dolibarr vers WP.
	 *
	 * @since 2.0.0
	 *
	 * @param  stdClass      $doli_contact Les données provenant de Dolibarr.
	 * @param  Contact_Model $wp_contact   Les données de WP.
	 * @param  boolean       $save         True pour enregister le contact en
	 * base de donnée. Sinon false pour seulement récupérer un objet remplis.
	 *
	 * @return boolean|Contact_Model       Les données du contact.
	 */
	public function doli_to_wp( $doli_contact, $wp_contact, $save = true ) {
		$wp_third_party = null;

		if ( ! empty( $doli_contact->socid ) ) {
			$wp_third_party = Third_Party::g()->get( array(
				'meta_key'   => '_external_id',
				'meta_value' => (int) $doli_contact->socid,
			), true );
		}


		$wp_contact->data['external_id']  = (int) $doli_contact->id;
		$wp_contact->data['login']        = sanitize_title( $doli_contact->email );
		$wp_contact->data['firstname']    = $doli_contact->firstname;
		$wp_contact->data['lastname']     = $doli_contact->lastname;
		$wp_contact->data['displayname']  = $doli_contact->lastname;
		$wp_contact->data['phone']        = $doli_contact->phone_pro;
		$wp_contact->data['phone_mobile'] = $doli_contact->phone_mobile;
		$wp_contact->data['email']        = $doli_contact->email;

		if ( ! empty( $wp_third_party ) ) {
			$wp_contact->data['third_party_id'] = $wp_third_party->data['id'];
		}

		if ( $save ) {
			if ( 0 === $wp_contact->data['id'] && false !== email_exists( $wp_contact->data['email'] ) ) {
				\eoxia\LOG_Util::log( sprintf( 'Contact: doli_to_wp can\'t create %s email already exist', json_encode( $wp_contact->data ) ), 'wpshop2' );
				return false;
			}


			if ( empty( $wp_contact->data['id'] ) ) {
				$wp_contact->data['password'] = wp_generate_password();
			}

			$contact_saved = Contact::g()->update( $wp_contact->data );

			if ( is_wp_error( $contact_saved ) ) {
				// translators: Contact: doli_to_wp error when update or create contact {json_data}.
				\eoxia\LOG_Util::log( sprintf( 'Contact: doli_to_wp error when update or create contact: %s', json_encode( $contact_saved ) ), 'wpshop2' );
				return false;
			}

			if ( null !== $wp_third_party ) {
				$wp_third_party->data['contact_ids'][] = $contact_saved->data['id'];
				Third_Party::g()->update( $wp_third_party->data );
			}
		}

		return $wp_contact;
	}

	/**
	 * Synchronise WP vers Dolibarr
	 *
	 * @since 2.0.0
	 *
	 * @param  Contact_Model $wp_contact   Les données du contact provenant de WP.
	 * @param  stdClass      $doli_contact Les données du contact dolibarr.
	 *
	 * @return integer                     L'ID de dolibarr.
	 */
	public function wp_to_doli( $wp_contact, $doli_contact ) {
		$third_party = Third_Party::g()->get( array(
			'id' => $wp_contact->data['third_party_id'],
		), true );

		$data = array(
			'lastname'  => $wp_contact->data['lastname'],
			'firstname' => $wp_contact->data['firstname'],
			'email'     => $wp_contact->data['email'],
			'phone_pro' => $wp_contact->data['phone'],
			'socid'     => $third_party->data['external_id'],
		);

		if ( ! empty( $wp_contact->data['external_id'] ) ) {
			Request_Util::put( 'contacts/' . $wp_contact->data['external_id'], $data );
		} else {
			$contact_id                      = Request_Util::post( 'contacts', $data );
			$wp_contact->data['external_id'] = $contact_id;

			Contact::g()->update( $wp_contact->data );
		}

		return $contact_id;
	}
}

Doli_Contact::g();
