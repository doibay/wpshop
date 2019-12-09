<?php
/**
 * Classe définisant le schéma principales des éléments de WPshop.
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
 * Object Model Class.
 */
class Object_Model extends \eoxia\Post_Model {

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $object     Les données de l'objet.
	 * @param string  $req_method La méthode de la requête.
	 */
	public function __construct( $object, $req_method = null ) {
		$this->schema['external_id'] = array(
			'type'        => 'integer',
			'meta_type'   => 'single',
			'field'       => '_external_id',
			'since'       => '2.0.0',
			'description' => 'L\'ID provenant de dolibarr',
		);

		$this->schema['ref'] = array(
			'type'        => 'string',
			'meta_type'   => 'single',
			'field'       => '_ref',
			'since'       => '2.0.0',
			'description' => 'La référence de l\'objet (varchar(128)). Ne peut être NULL. Clé unique. Aucune valeur par défaut. Relation avec dolibarr.',
		);

		$this->schema['date_last_synchro'] = array(
			'type'        => 'wpeo_date',
			'meta_type'   => 'single',
			'field'       => '_date_last_synchro',
			'since'       => '2.0.0',
			'description' => 'La date de la dernière synchronisation.',
			'context'     => array( 'GET' ),
		);

		parent::__construct( $object, $req_method );
	}
}
