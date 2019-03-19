<?php
/**
 * Les fonctions principales des contacts.
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
class Contact extends \eoxia\User_Class {

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

	/**
	 * Affiches les tiers
	 *
	 * @since 2.0.0
	 *
	 * @param Third_Party_Model $third_party Les données du tier.
	 */
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
}

Contact::g();