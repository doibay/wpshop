<?php
/**
 * Les fonctions principales des contacts.
 *
 * Le controlleur du modèle Contact_Model.
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
 * Contact class.
 */
class Contact_Class extends \eoxia\User_Class {

	/**
	 * Model name @see ../model/*.model.php.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $model_name = '\wpshop\Contact_Model';

	/**
	 * Post type
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $type = 'wps-contact';

	/**
	 * La clé principale du modèle
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $meta_key = 'contact';

	/**
	 * La route pour accéder à l'objet dans la rest API
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $base = 'contact';

	/**
	 * La taxonomy lié à ce post type.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $attached_taxonomy_type = '';

	public function display( $third_party ) {
		$contacts = array();

		if ( ! empty( $third_party->data['contact_ids'] ) ) {
			$contacts = $this->get( array(
				'include' => $third_party->data['contact_ids'],
			) );
		}

		\eoxia\View_Util::exec( 'wpshop', 'contacts', 'list', array(
			'contacts' => $contacts,
		) );
	}

	public function doli_to_wp( $wp_contact, $doli_contact ) {
		$contact_ids = array();

		$data = Request_Util::get( 'contacts?thirdparty_ids=' . $third_party->data['external_id'] );

		if ( ! empty( $data ) ) {
			foreach ( $data as $doli_contact ) {
				// Vérifie l'existence du contact en base de donnée.
				$contact = Contact_Class::g()->get( array(
					'meta_key'   => '_external_id',
					'meta_value' => $doli_contact->id,
				), true ); // WPCS: slow query ok.

				if ( empty( $contact ) ) {
					$contact = Contact_Class::g()->get( array( 'schema' => true ), true );
				}

				$contact->data['external_id']    = (int) $doli_contact->id;
				$contact->data['third_party_id'] = (int) $third_party->data['id'];
				$contact->data['login']          = $doli_contact->socname;
				$contact->data['firstname']      = $doli_contact->firstname;
				$contact->data['lastname']       = $doli_contact->lastname;
				$contact->data['phone']          = $doli_contact->phone_pro;
				$contact->data['email']          = $doli_contact->email;

				if ( empty( $contact->data['id'] ) ) {
					$contact->data['password'] = wp_generate_password();
				}

				$contact       = Contact_Class::g()->update( $contact->data );
				if ( ! is_wp_error( $contact ) ) {
					$contact_ids[] = $contact->data['id'];
				}
			}
		}

		// Supprimes les contacts qui ne sont plus présent dans dolibarr
		// if ( ! empty( $third_party->data['contact_ids'] ) ) {
		// 	foreach ( $third_party->data['contact_ids'] as $index => $contact_id ) {
		// 		if ( ! in_array( $contact_id, $contact_ids ) && ! empty( $contact_id ) ) {
		// 			array_splice( $third_party->data['contact_ids'], $index, 1 );
		//
		// 			$contact                = Contact_Class::g()->get( array( 'id' => $contact_id ), true );
		// 			$contact->data['socid'] = -1;
		// 			Contact_Class::g()->update( $contact->data );
		//
		// 		}
		// 	}
		// }
		//
		// Third_Party_Class::g()->update( $third_party->data );

		return $contact_ids;
	}

	public function save( $data ) {
		$contact = Contact_Class::g()->get( array( 'schema' => true ), true );

		$contact->data['login']     = sanitize_user( current( explode( '@', $data['contact']['email'] ) ), true );
		$contact->data['email']     = $data['contact']['email'];
		$contact->data['firstname'] = $data['contact']['firstname'];
		$contact->data['lastname']  = $data['contact']['lastname'];
		$contact->data['phone']     = $data['contact']['phone'];
		$contact->data['password']  = wp_generate_password();

		$contact = Contact_Class::g()->update( $contact->data );
		return $contact;
	}
}

Contact_Class::g();